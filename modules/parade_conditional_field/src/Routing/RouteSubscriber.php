<?php

namespace Drupal\parade_conditional_field\Routing;

use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Routing\RouteSubscriberBase;
use Drupal\Core\Routing\RoutingEvents;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

/**
 * Subscriber for Field UI routes.
 */
class RouteSubscriber extends RouteSubscriberBase {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityManagerInterface
   */
  protected $manager;

  /**
   * Constructs a RouteSubscriber object.
   *
   * @param \Drupal\Core\Entity\EntityManagerInterface $manager
   *   The entity type manager.
   */
  public function __construct(EntityManagerInterface $manager) {
    $this->manager = $manager;
  }

  /**
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection) {
    foreach ($this->manager->getDefinitions() as $entity_type_id => $entity_type) {
      if ($entity_type_id == 'paragraph' && $route_name = $entity_type->get('field_ui_base_route')) {
        // Try to get the route from the current collection.
        if (!$entity_route = $collection->get($route_name)) {
          continue;
        }
        $path = $entity_route->getPath();

        $options = $entity_route->getOptions();
        if ($bundle_entity_type = $entity_type->getBundleEntityType()) {
          $options['parameters'][$bundle_entity_type] = [
            'type' => 'entity:' . $bundle_entity_type,
          ];
        }
        // @todo: pass paragraphs_type as bundle.
        $route_match = \Drupal::service('current_route_match');
        $bundle = $route_match->getParameter('paragraphs_type');

        $defaults = [
          'entity_type_id' => $entity_type_id,
          'bundle' => $bundle,
        ];


        $route = new Route(
          "$path/parade-conditional-fields",
          [
            '_controller' => '\Drupal\parade_conditional_field\Controller\ParadeConditionalFieldController::listing',
            '_title' => 'Parade field conditions',
          ] + $defaults,
          ['_permission' => 'administer paragraphs types'],
          $options
        );
        $collection->add("entity.{$entity_type_id}.parade_conditional_field", $route);
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events = parent::getSubscribedEvents();
    $events[RoutingEvents::ALTER] = ['onAlterRoutes', -100];
    return $events;
  }

}
