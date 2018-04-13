<?php

namespace Drupal\parade_edit;

use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Service class for manipulating entity type information.
 *
 * This class contains primarily bridged hooks for compile-time or
 * cache-clear-time hooks. Runtime hooks should be placed in EntityOperations.
 */
class EntityTypeInfo {

  use StringTranslationTrait;

  /**
   * Adds devel links to appropriate entity types.
   *
   * This is an alter hook bridge.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface[] $entity_types
   *   The master entity type list to alter.
   *
   * @see hook_entity_type_alter()

  public function entityTypeAlter(array &$entity_types) {
    foreach ($entity_types as $entity_type_id => $entity_type) {
      if (($entity_type->getFormClass('default') || $entity_type->getFormClass('edit')) && $entity_type->hasLinkTemplate('edit-form')) {
        $entity_type->setLinkTemplate('devel-load', "/devel/$entity_type_id/{{$entity_type_id}}");
      }
    }
  }

  /**
   * Adds 'Try new editor' operations on entity that supports it.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity on which to define an operation.
   *
   * @return array
   *   An array of operation definitions.
   *
   * @see hook_entity_operation()

  public function entityOperation(EntityInterface $entity) {
    $operations = [];dpm('1');
//    if ($this->currentUser->hasPermission('view latest version')) {dpm('2');
//      if ('node' === $entity->getEntityType()->id() && 'parade_onepage' === $entity->bundle()) {
        $operations['parade_edit'] = [
          'title' => $this->t('Try new editor'),
          'weight' => 100,
          'url' => Url::fromRoute('entity.node.latest_version', ['node' => $entity->id()]),
        ];
//      }
//    }
    return $operations;
  }   */

  /**
   * Gets the "extra fields" for a bundle.
   *
   * This is a hook bridge.
   * Add Parade edit moderation control.
   *
   * @see hook_entity_extra_field_info()
   *
   * @return array
   *   A nested array of 'pseudo-field' elements. Each list is nested within the
   *   following keys: entity type, bundle name, context (either 'form' or
   *   'display'). The keys are the name of the elements as appearing in the
   *   renderable array (either the entity form or the displayed entity). The
   *   value is an associative array:
   *   - label: The human readable name of the element. Make sure you sanitize
   *     this appropriately.
   *   - description: A short description of the element contents.
   *   - weight: The default weight of the element.
   *   - visible: (optional) The default visibility of the element. Defaults to
   *     TRUE.
   *   - edit: (optional) String containing markup (normally a link) used as the
   *     element's 'edit' operation in the administration interface. Only for
   *     'form' context.
   *   - delete: (optional) String containing markup (normally a link) used as the
   *     element's 'delete' operation in the administration interface. Only for
   *     'form' context.
   */
  public function entityExtraFieldInfo() {
    $return = [];
    $bundles = \Drupal::config('parade_demo.settings')->get('bundles');
    foreach (array_keys($bundles) as $bundle) {
      $return['node'][$bundle]['display']['parade_edit_moderation_control'] = [
        'label' => $this->t('Parade edit moderation control'),
        'description' => $this->t("Status listing and form for the entitiy's moderation state."),
        'weight' => -20,
        'visible' => TRUE,
      ];
    }

    return $return;
  }

}
