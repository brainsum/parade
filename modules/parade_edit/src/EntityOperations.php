<?php

namespace Drupal\parade_edit;

use Drupal\workbench_moderation\ModerationInformationInterface;
use Drupal\Core\Entity\Display\EntityViewDisplayInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\parade_edit\Form\EntityModerationForm;

/**
 * Defines a class for reacting to entity events.
 */
class EntityOperations {

  protected $moderationInfo;

  protected $formBuilder;

  /**
   * Constructs a new EntityOperations object.
   *
   * @param \Drupal\workbench_moderation\ModerationInformationInterface $moderation_info
   *   Moderation information service.
   * @param \Drupal\Core\Form\FormBuilderInterface $form_builder
   *   The form builder.
   */
  public function __construct(ModerationInformationInterface $moderation_info, FormBuilderInterface $form_builder) {
    $this->moderationInfo = $moderation_info;
    $this->formBuilder = $form_builder;
  }

  /**
   * Act on entities being assembled before rendering.
   *
   * This is a hook bridge.
   * Add Parade edit moderation control.
   *
   * @see hook_entity_view()
   * @see EntityFieldManagerInterface::getExtraFields()
   */
  public function entityView(array &$build, EntityInterface $entity, EntityViewDisplayInterface $display, $view_mode) {
    if (!$this->moderationInfo->isModeratableEntity($entity)) {
      return;
    }
    if (!$this->moderationInfo->isLatestRevision($entity)) {
      return;
    }
    // Don't show on node view page.
    if (\Drupal::routeMatch()->getRouteName() === 'entity.node.canonical') {
      return;
    }

    $component = $display->getComponent('parade_edit_moderation_control');
    if ($component) {
      $build['parade_edit_moderation_control'] = $this->formBuilder->getForm(EntityModerationForm::class, $entity);
      $build['parade_edit_moderation_control']['#weight'] = $component['weight'];
    }
  }

}
