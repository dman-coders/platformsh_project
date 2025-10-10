<?php

namespace Drupal\platformsh_project\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a MetricType annotation object.
 *
 * Plugin namespace: Plugin\MetricType
 *
 * @Annotation
 */
class MetricType extends Plugin {

  /**
   * The plugin ID.
   *
   * @var string
   */
  public $id;

  /**
   * The human-readable name of the metric type.
   *
   * @var \Drupal\Core\Annotation\Translation
   *
   * @ingroup plugin_translatable
   */
  public $label;

  /**
   * A brief description of the metric type.
   *
   * @var \Drupal\Core\Annotation\Translation
   *
   * @ingroup plugin_translatable
   */
  public $description;

}
