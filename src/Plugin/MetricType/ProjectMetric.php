<?php

namespace Drupal\platformsh_project\Plugin\MetricType;

use Drupal\platformsh_project\Check\CliCheck;
use Drupal\platformsh_project\Entity\Metric;

/**
 * Check project info
 *
 * @MetricType(
 *   id = "project",
 *   label = @Translation("Project Check"),
 *   description = @Translation("Checks project information via Platform.sh CLI")
 * )
 */
class ProjectMetric extends Metric {

  /**
   * {@inheritdoc}
   */
  public function label(): string {
    return "Project check (class)";
  }

  /**
   * Refresh this metric.
   */
  public function refresh(): void {
    $result = "";
    $logger = $this->getLogger();
    $project_id = $this->getProject()->get('field_id')->value;
    $args = [
      'PLATFORM_PROJECT' => $project_id
    ];

    $status = CliCheck::execute($args, $result, $logger);
    $this
      ->set('status', $status)
      ->set('data', $result)
      ->set('note', "Checked project. Response:\n" . $result)
      ->save();
    parent::refresh();

  }

}
