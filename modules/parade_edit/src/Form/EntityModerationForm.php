<?php

namespace Drupal\parade_edit\Form;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\workbench_moderation\Entity\ModerationStateTransition;
use Drupal\workbench_moderation\ModerationInformationInterface;
use Drupal\workbench_moderation\StateTransitionValidation;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Url;

/**
 * Class EntityModerationForm
 * @package Drupal\parade_edit\Form
 */
class EntityModerationForm extends FormBase {

  /**
   * @var \Drupal\workbench_moderation\ModerationInformationInterface
   */
  protected $moderationInfo;

  /**
   * @var \Drupal\workbench_moderation\StateTransitionValidation
   */
  protected $validation;

  /**
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  public function __construct(ModerationInformationInterface $moderation_info, StateTransitionValidation $validation, EntityTypeManagerInterface $entity_type_manager) {
    $this->moderationInfo = $moderation_info;
    $this->validation = $validation;
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * @inheritDoc
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('workbench_moderation.moderation_information'),
      $container->get('workbench_moderation.state_transition_validation'),
      $container->get('entity_type.manager')
    );
  }

  /**
   * @inheritDoc
   */
  public function getFormId() {
    return 'parade_edit_entity_moderation_form';
  }

  /**
   * @inheritDoc
   */
  public function buildForm(array $form, FormStateInterface $form_state, ContentEntityInterface $entity = NULL) {
    /** @var ModerationState $current_state */
    $current_state = $entity->moderation_state->entity;
    $transitions = $this->validation->getValidTransitions($entity, $this->currentUser());

    // Exclude self-transitions.
    $transitions = array_filter($transitions, function (ModerationStateTransition $transition) use ($current_state) {
      return $transition->getToState() != $current_state->id();
    });

    $target_states = [];
    $states = $this->entityTypeManager->getStorage('moderation_state')->loadMultiple();
    /** @var ModerationStateTransition $transition */
    foreach ($transitions as $transition) {
      $stateTo = $transition->getToState();
      $group = $states[$stateTo]->isPublishedState() ? 'published' : 'unpublished';
      $target_states[$group][$stateTo] = [$transition->label()];
    }

    if (!count($target_states)) {
      return $form;
    }

    $form['preferences'] = [
      '#type' => 'link',
      '#title' => 'Edit menu and preferences',
      '#url' => Url::fromRoute('parade_edit.modal.preferences', [
        'entity_type' => $entity->getEntityType()->id(),
        'node' => $entity->id(),
        'js' => 'nojs',
      ]),
      '#attributes' => [
        'class' => ['button', 'geysir-button', 'use-ajax'],
      ],
      '#weight' => -50,
      '#prefix' => '<div id="parade-edit-preferences-wrapper">',
      '#suffix' => '</div>',
    ];

    if ($current_state) {
      $form['current'] = [
        '#markup' => t('All changes saved as %moderation_state.', [
          '%moderation_state' => strtolower($current_state->label()),
        ]),
      ];
    }

    // Persist the entity so we can access it in the submit handler.
    $form_state->set('entity', $entity);

    ksort($target_states);
    foreach ($target_states as $group => $states) {
      $form['actions'][$group] = [
        '#type' => 'container',
        '#attributes' => ['class' => "state_group-$group"],
      ];
      foreach ($states as $stateId => $label) {
        $form['actions'][$group][$stateId] = [
          '#type' => 'submit',
          '#name' => 'state_' . $stateId,
          '#value' => current($label),
        ];
      }
    }

    $form['#attributes']['class'][] = $current_state->isPublishedState() ? 'published' : 'unpublished';
    $form['#prefix'] = '<div id="parade-edit-moderation-wrapper">';
    $form['#suffix'] = '</div>';
    return $form;
  }

  /**
   * @inheritDoc
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    /** @var ContentEntityInterface $entity */
    $entity = $form_state->get('entity');
    $input = $form_state->getUserInput();

    foreach ($input as $key => $value) {
      if (strstr($key, 'state_')) {
        $stateTo = substr($key, 6);
        break;
      }
    }

    $stateFrom = $entity->moderation_state->target_id;
    $entity->moderation_state->target_id = $stateTo;
    $entity->revision_log = $this->t('State changed from %from to %to.', ['%from' => $stateFrom, '%to' => $stateTo]);
    $entity->setRevisionCreationTime(\Drupal::time()->getRequestTime());
    $entity->setRevisionUserId(\Drupal::currentUser()->id());
    $entity->save();

    drupal_set_message($this->t('The moderation state has been updated.'));

    $form_state->setRedirectUrl($entity->toUrl('canonical'));
  }

}
