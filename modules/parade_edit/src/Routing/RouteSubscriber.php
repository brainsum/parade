<?php

namespace Drupal\parade_edit\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

/**
 * Listens to the dynamic route events.
 */
class RouteSubscriber extends RouteSubscriberBase {
  /**
   * {@inheritdoc}
   */
  public function alterRoutes(RouteCollection $collection) {
    // Override "geysir.modal.add_form" route with custom controller.
    if ($route = $collection->get('geysir.modal.add_form')) {
      $defaults = $route->getDefaults();
      $defaults['_controller'] = '\Drupal\parade_edit\Controller\ParadeGeysirController::add';
      $route->setDefaults($defaults);
    }
    // Override "geysir.modal.edit_form" route with custom controller.
    if ($route = $collection->get('geysir.modal.edit_form')) {
      $defaults = $route->getDefaults();
      $defaults['_controller'] = '\Drupal\parade_edit\Controller\ParadeGeysirController::edit';
      $route->setDefaults($defaults);
    }
  }
}