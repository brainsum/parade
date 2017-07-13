<?php

namespace Drupal\parade_conditional_field\Controller;

use Drupal\Core\Entity\Controller\EntityListController;
use Drupal\Core\Routing\RouteMatchInterface;

/**
 * Defines a controller to list parade conditional field instances.
 */
class ParadeConditionalFieldController extends EntityListController {

  /**
   * Shows the 'Parade conditional fields' page.
   *
   * @param string $entity_type_id
   *   The entity type.
   * @param string $bundle
   *   The entity bundle.
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The current route match.
   *
   * @return array
   *   A render array as expected by drupal_render().
   */
  public function listing($entity_type_id = NULL, $bundle = NULL, RouteMatchInterface $route_match = NULL) {
    return $this->entityManager()->getListBuilder('parade_conditional_field')->render($entity_type_id, $bundle);
  }

}
