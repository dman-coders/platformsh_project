<?php

namespace Drupal\platformsh_project\Entity;

use Drupal\platformsh_project\Check\CliCheck;

/**
 * Check project info
 *
 *  Each bundle definition needs to be declared in the
 *  platformsh_project_entity_bundle_info() also.
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
