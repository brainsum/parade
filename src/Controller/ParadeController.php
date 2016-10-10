<?php
/**
 * @file
 * Contains \Drupal\parade\Controller\ParadeController.
 */

namespace Drupal\parade\Controller;

use Drupal\Core\Controller\ControllerBase;

class ParadeController extends ControllerBase {

  /**
   * Parade settings.
   *
   * @todo
   */
  public function settings() {
    return array(
      '#type' => 'markup',
      '#markup' => 'In progress',
    );
  }

}
