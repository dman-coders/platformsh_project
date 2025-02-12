<?php

namespace Drupal\platformsh_project\Entity;

use Drupal\Core\Entity\Annotation\ContentEntityType;

/**
 * A metric entity that checks if Nodeping HighSLA monitoring is enabled for
 * this project.
 *
 *  Each bundle definition beeds to be declared in the
 *  platformsh_project_entity_bundle_info() also.
 *  Cannot use annotations without things getting snarled up.
 */
class HighSlaMetric extends Metric {

  /**
   *
   */
  public function label() {
    return "HighSLA Check";
  }

  /**
   *
   */
  public function refresh() {
    $this->set('data', 'pinged ' . date("Y-m-d H:i:s"))
      ->save();
  }

}
