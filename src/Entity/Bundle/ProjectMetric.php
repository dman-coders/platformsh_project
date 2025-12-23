<?php

namespace Drupal\platformsh_project\Entity\Bundle;

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
   * Refresh this metric.
   */
  public function refresh(): void {
    $result = "";
    $logger = $this->getLogger();
    $project_id = $this->getProject()->get('field_id')->value;
    $args = [
      'check_name' => 'check_project_organization',
      'PLATFORM_PROJECT' => $project_id
    ];
    $this->getLogger()->debug("Project refresh start. %data ",
      [
        '%data' => $result,
        'link' => $this->toLink('View metric')->toString(),
      ]
    );


    $status = CliCheck::execute($args, $result, $logger);
    $this
      ->set('status', $status)
      ->set('data', $result)
      ->set('note', "Checked project. Response:\n" . $result)
      ->save();
    parent::refresh();

    $this->getLogger()->debug("Project refresh done. %data %note",
      [
        '%data' => $result, '%note' => $this->get('note')->value,
        'link' => $this->toLink('View metric')->toString(),
      ]
    );
  }

}