<?php

namespace Drupal\parade_edit\Plugin\Menu;

use Drupal\workbench_moderation\Plugin\Menu\EditTab as WorkbenchModerationEditTab;

/**
 * Defines a class for altering the Last version tab to 'Try new editor'.
 */
class ViewTab extends WorkbenchModerationEditTab {

  /**
   * {@inheritdoc}
   */
  public function getTitle() {
    // @todo - check parade_demo enabled bundles.
    if ($this->moderationInfo->isModeratableEntity($this->entity) && 'parade_onepage' === $this->entity->bundle()) {
      return $this->moderationInfo->isLiveRevision($this->entity) ? $this->t('View - Live') : $this->t('View - Unpublished');
    }

    return $this->t('View');
  }

}
