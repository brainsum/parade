<?php
/**
 * @file
 * Contains \Drupal\parade\Controller\ParadeController.
 */

namespace Drupal\parade\Controller;

use Drupal\Core\Controller\ControllerBase;

class ParadeController extends ControllerBase {

  public function settings() {
    return array(
        '#type' => 'markup',
        '#markup' => $this->t('Hello, World!'),
    );
  }

}
