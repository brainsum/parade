<?php

namespace Drupal\parade_edit\Plugin\Menu;

use Drupal\workbench_moderation\Plugin\Menu\EditTab as WorkbenchModerationEditTab;

/**
 * Defines a class for altering the View tab text.
 */
class ViewTab extends WorkbenchModerationEditTab {

  /**
   * {@inheritdoc}
   */
  public function getTitle() {
    // @todo - check parade_demo enabled bundles.
    if ($this->moderationInfo->isModeratableEntity($this->entity) && 'parade_onepage' === $this->entity->bundle()) {
      $entity_type_id = $this->entity->getEntityType()->id();
      $defaultRevisionId = $this->moderationInfo->getDefaultRevisionId($entity_type_id, $this->entity->id());
      $entity = $this->entity;
      if ($defaultRevisionId != $this->entity->getLoadedRevisionId()) {
        $entity = \Drupal::entityTypeManager()->getStorage($entity_type_id)->loadRevision($defaultRevisionId);
      }
      return $entity->isPublished() ? $this->t('View - Live') : $this->t('View - Unpublished');
    }

    return $this->t('View');
  }

}
