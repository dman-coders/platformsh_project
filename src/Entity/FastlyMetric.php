<?php

namespace Drupal\platformsh_project\Entity;

use Drupal\platformsh_project\Check\FastlyServiceCheck;

/**
 * A metric entity that saves the result of a fastly test.
 *
 * Each bundle definition needs to be declared in the
 * platformsh_project_entity_bundle_info() also.
 * Cannot use annotations without things getting snarled up.
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
  public function refresh() {
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
  }

}
