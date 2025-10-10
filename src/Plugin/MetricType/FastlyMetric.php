<?php

namespace Drupal\platformsh_project\Plugin\MetricType;

use Drupal\platformsh_project\Check\FastlyServiceCheck;
use Drupal\platformsh_project\Entity\Metric;

/**
 * A metric entity that saves the result of a fastly test.
 *
 * @MetricType(
 *   id = "fastly",
 *   label = @Translation("Fastly Check"),
 *   description = @Translation("Check Fastly status")
 * )
 */
class FastlyMetric extends Metric {

  /**
   * {@inheritdoc}
   */
  public function label(): string {
    return "Fastly check";
  }

  /**
   * Refresh this metric.
   */
  public function refresh(): void {
    // Check for the existence of a Fastly service for this project.
    $result = "";
    $logger = $this->getLogger();
    $project_id = $this->getProject()->get('field_id')->value;
    $args = ['PLATFORM_PROJECT' => $project_id];
    $status = FastlyServiceCheck::execute($args, $result, $logger);
    $this
      ->set('status', $status)
      ->set('data', $result)
      ->set('note', "Checked Fastly Account. Response:\n" . $result)
      ->save();
    parent::refresh();

  }

}
