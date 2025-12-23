<?php

namespace Drupal\platformsh_project\Routing;

use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\Routing\AdminHtmlRouteProvider;

/**
 * Provides routes for Metric entities with plugin-based bundle support.
 *
 * This custom route provider ensures that auto-generated routes use our
 * custom 'metric_type_plugin' parameter converter instead of expecting
 * MetricType config entities.
 */
class MetricRouteProvider extends AdminHtmlRouteProvider {

  /**
   * {@inheritdoc}
   */
  protected function getAddFormRoute(EntityTypeInterface $entity_type) {
    $route = parent::getAddFormRoute($entity_type);

    if ($route) {
      // Override the parameter type to use our custom plugin-based converter
      // instead of the default entity:metric_type converter.
      $route->setOption('parameters', [
        'metric_type' => ['type' => 'metric_type_plugin'],
      ]);
    }

    return $route;
  }

}
