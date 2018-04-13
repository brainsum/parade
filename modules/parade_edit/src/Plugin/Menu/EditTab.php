<?php

namespace Drupal\parade_edit\Plugin\Menu;

use Drupal\workbench_moderation\Plugin\Menu\EditTab as WorkbenchModerationEditTab;

/**
 * Defines a class for altering the edit tab text.
 */
class EditTab extends WorkbenchModerationEditTab {

  /**
   * {@inheritdoc}
   */
  public function getTitle() {
    $bundles = \Drupal::config('parade_demo.settings')->get('bundles');
    if ($this->moderationInfo->isModeratableEntity($this->entity) && in_array($this->entity->bundle(), array_keys($bundles))) {
      return $this->t('Edit');
    }

    return parent::getTitle();
  }

}
