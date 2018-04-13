<?php

namespace Drupal\parade_edit\Plugin\Menu;

use Drupal\workbench_moderation\Plugin\Menu\EditTab;

/**
 * Defines a class for altering the Last version tab to 'Try new editor'.
 */
class ParadeEditorTab extends EditTab {

  /**
   * {@inheritdoc}
   */
  public function getTitle() {
    $bundles = \Drupal::config('parade_demo.settings')->get('bundles');
    if ($this->moderationInfo->isModeratableEntity($this->entity) && in_array($this->entity->bundle(), array_keys($bundles))) {
      return $this->t('Try the new editor');
    }

    return $this->t('Latest version');
  }

}
