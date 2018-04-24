<?php

namespace Drupal\parade_edit\ParamConverter;

use Drupal\workbench_moderation\ParamConverter\EntityRevisionConverter as WorkbenchModerationEntityRevisionConverter;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\TypedData\TranslatableInterface;

/**
 * Defines a class for Latest version TAB to autocreating draft.
 */
class ParadeEntityRevisionConverter extends WorkbenchModerationEntityRevisionConverter {

  /**
   * {@inheritdoc}
   */
  public function convert($value, $definition, $name, array $defaults) {
    $entity = parent::convert($value, $definition, $name, $defaults);
    // Create new draft revision if doesn't exist.
    /* @todo - refactor: create only on latest route, doesn't work:
     * \Drupal::routeMatch()->getRouteName() === 'entity.node.latest_version'
     */
    if ($entity && $this->moderationInformation->isModeratableEntity($entity) && $this->moderationInformation->isLiveRevision($entity)) {
      $entity->setNewRevision(TRUE);
      $entity->setRevisionCreationTime(\Drupal::time()->getRequestTime());
      $entity->setRevisionUserId(\Drupal::currentUser()->id());
      $bundle = \Drupal::entityTypeManager()->getStorage('node_type')->load($entity->bundle());
      $default_moderation_state = $bundle->getThirdPartySetting('workbench_moderation', 'default_moderation_state');
      $entity->moderation_state->target_id = $default_moderation_state;
      $entity->revision_log = t('New %state version created automatically.', ['%state' => $default_moderation_state]);
      $entity->save();

      // If the entity type is translatable, ensure we return the proper
      // translation object for the current context.
      if ($entity instanceof EntityInterface && $entity instanceof TranslatableInterface) {
        $entity = $this->entityManager->getTranslationFromContext($entity, NULL, array('operation' => 'entity_upcast'));
      }
    }

    return $entity;
  }

}
