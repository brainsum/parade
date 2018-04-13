<?php

namespace Drupal\parade_edit\Access;

use Drupal\workbench_moderation\Access\LatestRevisionCheck as WorkbenchModerationLatestRevisionCheck;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Routing\RouteMatchInterface;
use Symfony\Component\Routing\Route;

/**
 * Custom access check for 'Last version' TAB.
 *
 * @package Drupal\parade_edit\Access
 */
class LatestRevisionCheck extends WorkbenchModerationLatestRevisionCheck {

  /**
   * Allow 'Last version' TAB for parade_demo enabled moderatable entities.
   *
   * This checker assumes the presence of an '_entity_access' requirement key
   * in the same form as used by EntityAccessCheck.
   *
   * @param \Symfony\Component\Routing\Route $route
   *   The route to check against.
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The parametrized route.
   *
   * @throws \Exception
   *
   * @return \Drupal\Core\Access\AccessResultInterface
   *   The access result.
   *
   * @see EntityAccessCheck
   */
  public function access(Route $route, RouteMatchInterface $route_match) {
    $entity = $this->loadEntity($route, $route_match);

    // Always show this TAB for parade_demo enabled moderatable entity.
    $bundles = \Drupal::config('parade_demo.settings')->get('bundles');
    if (in_array($entity->bundle(), array_keys($bundles))) {
      return $this->moderationInfo->isModeratableEntity($entity)
        ? AccessResult::allowed()->addCacheableDependency($entity)
        : AccessResult::forbidden()->addCacheableDependency($entity);
    }
    return parent::access($route, $route_match);
  }

}
