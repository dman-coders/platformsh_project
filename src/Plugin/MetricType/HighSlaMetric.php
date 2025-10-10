<?php

namespace Drupal\platformsh_project\Plugin\MetricType;

use Drupal\platformsh_project\Entity\Metric;

/**
 * A metric entity that checks if Nodeping HighSLA monitoring is enabled.
 *
 * @MetricType(
 *   id = "highsla",
 *   label = @Translation("HighSLA"),
 *   description = @Translation("Checks if HighSLA monitoring is active")
 * )
 */
class HighSlaMetric extends Metric {


  /**
   * Refresh this metric.
   */
  public function refresh(): void {
    $this->set('data', 'pinged ' . date("Y-m-d H:i:s"))
      ->save();
    parent::refresh();

  }

}
