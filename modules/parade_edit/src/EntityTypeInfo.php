<?php

namespace Drupal\parade_edit;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Session\AccountInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Url;

/**
 * Service class for manipulating entity type information.
 *
 * This class contains primarily bridged hooks for compile-time or
 * cache-clear-time hooks. Runtime hooks should be placed in EntityOperations.
 */
class EntityTypeInfo implements ContainerInjectionInterface {

  use StringTranslationTrait;

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * EntityTypeInfo constructor.
   *
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   Current user.
   */
  public function __construct(AccountInterface $current_user) {
    $this->currentUser = $current_user;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('current_user')
    );
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
   */
  public function entityOperation(EntityInterface $entity) {
    $operations = [];
    if ($this->currentUser->hasPermission('view latest version')) {
      $bundles = \Drupal::config('parade_demo.settings')->get('bundles');
      if ('node' === $entity->getEntityType()->id() && in_array($entity->bundle(), array_keys($bundles))) {
        $operations['parade_edit'] = [
          'title' => $this->t('Edit with the new editor'),
          'weight' => 10,
          'url' => Url::fromRoute('entity.node.latest_version', ['node' => $entity->id()]),
        ];
      }
    }
    return $operations;
  }

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
   *   - delete: (optional) String containing markup (normally a link) used as
   *     the element's 'delete' operation in the administration interface. Only
   *     for 'form' context.
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
