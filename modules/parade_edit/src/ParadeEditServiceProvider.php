<?php

namespace Drupal\parade_edit;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderBase;

/**
 * Defines a service profiler for the parade_edit module.
 */
class ParadeEditServiceProvider extends ServiceProviderBase {

  /**
   * {@inheritdoc}
   */
  public function alter(ContainerBuilder $container) {
    // Overrides workbench_moderation paramconverter latest_revision class.
    $definition = $container->getDefinition('paramconverter.latest_revision');
    $definition->setClass('Drupal\parade_edit\ParamConverter\ParadeEntityRevisionConverter');

    // Overrides workbench_moderation latest_revision access class.
    $definition = $container->getDefinition('access_check.latest_revision');
    $definition->setClass('Drupal\parade_edit\Access\LatestRevisionCheck');
  }

}
