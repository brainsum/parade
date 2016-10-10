<?php

namespace Drupal\parade\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Controller for Parade.
 */
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
