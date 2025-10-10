<?php

namespace Drupal\platformsh_project\Entity;

/**
 * A metric entity that checks if Nodeping HighSLA monitoring is enabled.
 *
 * Each bundle definition needs to be declared in the
 * platformsh_project_entity_bundle_info() also.
 * Cannot use annotations without things getting snarled up.
 */
class HighSlaMetric extends Metric {

  /**
   * {@inheritdoc}
   */
  public function label(): string {
    return "HighSLA Check";
  }

  /**
   * Refresh this metric.
   */
  public function refresh(): void {
    $this->set('data', 'pinged ' . date("Y-m-d H:i:s"))
      ->save();
    parent::refresh();

  }

}
