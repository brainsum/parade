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
    // @todo - check parade_demo enabled bundles.
    if ($this->moderationInfo->isModeratableEntity($this->entity) && 'parade_onepage' === $this->entity->bundle()) {
      return $this->t('Try the new editor');
    }

    return $this->t('Latest version');
  }

}
