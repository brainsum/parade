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
    $bundles = \Drupal::config('parade_demo.settings')->get('bundles');
    if ($this->moderationInfo->isModeratableEntity($this->entity) && in_array($this->entity->bundle(), array_keys($bundles))) {
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
