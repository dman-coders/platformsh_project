<?php

namespace Drupal\platformsh_project\Entity\Bundle;

use Drupal\platformsh_project\Entity\Metric;

/**
 * A metric entity that performs no special action.
 *
 * It just exists as a repository for notes entered manually.
 *
 * @MetricType(
 *   id = "note",
 *   label = @Translation("Note"),
 *   description = @Translation("An arbitrary user-added note")
 * )
 */
class NoteMetric extends Metric {

  /**
   * Refresh this metric.
   */
  public function refresh(): void {
    $this->set('data', 'Refreshed ' . date("Y-m-d H:i:s"))
      ->save();
    parent::refresh();
  }

}