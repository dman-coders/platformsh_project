<?php

namespace Drupal\platformsh_project\ParamConverter;

use Drupal\Core\ParamConverter\ParamConverterInterface;
use Symfony\Component\Routing\Route;

/**
 * Converts metric_type route parameters to plugin-based pseudo-entities.
 *
 * This allows routes to reference metric types dynamically discovered from
 * @MetricType plugins without requiring MetricType config entities to exist.
 *
 * @see platformsh_project.routing.yml where this is leveraged.
 */
class MetricTypeConverter implements ParamConverterInterface {

  /**
   * {@inheritdoc}
   */
  public function convert($value, $definition, $name, array $defaults) {
    $plugin_manager = \Drupal::service('plugin.manager.metric_type');
    $plugin_definition = $plugin_manager->getDefinition($value, FALSE);

    if (!$plugin_definition) {
      return NULL;
    }

    // Create a pseudo MetricType object from plugin definition.
    // This mimics enough of the MetricType interface for routing purposes.
    return new class($plugin_definition) {
      private $definition;

      public function __construct($definition) {
        $this->definition = $definition;
      }

      public function id() {
        return $this->definition['id'];
      }

      public function label() {
        return (string) $this->definition['label'];
      }

      public function getDescription() {
        return isset($this->definition['description']) ? (string) $this->definition['description'] : '';
      }
    };
  }

  /**
   * {@inheritdoc}
   */
  public function applies($definition, $name, Route $route) {
    return !empty($definition['type']) && $definition['type'] === 'metric_type_plugin';
  }

}
