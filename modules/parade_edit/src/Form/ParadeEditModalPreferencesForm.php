<?php

namespace Drupal\parade_edit\Form;

use Drupal\node\NodeForm;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\geysir\Ajax\GeysirCloseModalDialogCommand;

/**
 * Functionality to edit a paragraph through a modal.
 */
class ParadeEditModalPreferencesForm extends NodeForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    // We don't need this title on the Modal because we stay on the same page
    // using a Modal, thus we don't loose context.
    unset($form['#title']);

    return $form;
  }

  /**
   * {@inheritdoc}
   *
   * Based on GeysirModalParagraphForm::buildForm, removed actions, added
   * custom Save, Cancel submit buttons.
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);

    $form['#prefix'] = '<div id="geysir-modal-form">';
    $form['#suffix'] = '</div>';

    // @TODO: fix problem with form is outdated.
    $form['#token'] = FALSE;

    // Define alternative submit callbacks using AJAX by copying the default
    // submit callbacks to the AJAX property.
    $parade_edit_save = [
      '#type' => 'submit',
      '#submit' => $form['actions']['submit']['#submit'],
      '#value' => $this->t('Save'),
      '#ajax' => [
        'callback' => '::ajaxSave',
        'event' => 'click',
        'progress' => [
          'type' => 'throbber',
          'message' => NULL,
        ],
      ],
    ];
    unset($form['actions']);
    $form['actions']['parade_edit_save'] = $parade_edit_save;
    $form['actions']['cancel'] = [
      '#type'   => 'button',
      '#value'  => $this->t('Cancel'),
      '#ajax' => [
        'callback' => '::ajaxCancel',
        'event'    => 'click',
      ],
    ];

    return $form;
  }

  /**
   * Ajax callback for cancel.
   */
  public function ajaxCancel(array $form, FormStateInterface $form_state) {
    $response = new AjaxResponse();
    $response->addCommand(new GeysirCloseModalDialogCommand());
    return $response;
  }

  /**
   * Ajax callback for save.
   */
  public function ajaxSave(array $form, FormStateInterface $form_state) {
    $response = new AjaxResponse();

    // When errors occur during form validation, show them to the user.
    if ($form_state->getErrors()) {
      unset($form['#prefix']);
      unset($form['#suffix']);
      $form['status_messages'] = [
        '#type' => 'status_messages',
        '#weight' => -10,
      ];
      $response->addCommand(new ReplaceCommand('#geysir-modal-form', $form));
    }
    else {
      $route_match = $this->getRouteMatch();
      $entity_type = $route_match->getParameter('entity_type');

      $node = $this->entity;
      $response->addCommand(
        new ReplaceCommand(
          '[data-history-node-id="' . $node->id() . '"]',
          \Drupal::entityTypeManager()->getViewBuilder($entity_type)->view($node, 'full')));

      $node_revision = \Drupal::service('workbench_moderation.moderation_information')->getLatestRevision($entity_type, $node->id());
      $moderationForm = \Drupal::formBuilder()
        ->getForm(EntityModerationForm::class, $node_revision);
      $response->addCommand(
        new ReplaceCommand(
          '#parade-edit-moderation-wrapper',
          $moderationForm));

      $response->addCommand(new GeysirCloseModalDialogCommand());
    }

    return $response;
  }

}
