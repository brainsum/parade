<?php

namespace Drupal\parade_edit\ParamConverter;

use Drupal\workbench_moderation\ParamConverter\EntityRevisionConverter as WorkbenchModerationEntityRevisionConverter;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\TypedData\TranslatableInterface;

/**
 * Defines a class for Latest version TAB to autocreating draft version if needed.
 */
class ParadeEntityRevisionConverter extends WorkbenchModerationEntityRevisionConverter {

  /**
   * {@inheritdoc}
   */
  public function convert($value, $definition, $name, array $defaults) {
    $entity = parent::convert($value, $definition, $name, $defaults);
    $route_match = \Drupal::routeMatch();
    // Create new draft revision if doesn't exist and we are on the latest
    // version route.
    if ('entity.node.latest_version' === $route_match->getRouteName() && $entity && $this->moderationInformation->isModeratableEntity($entity) && $this->moderationInformation->isLiveRevision($entity)) {
      $entity->setNewRevision(TRUE);
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
