<?php

namespace Drupal\platformsh_project\Entity;

use Drupal\Core\Entity\Annotation\ContentEntityType;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;

/**
 * A metric entity that saves the result of a ping test.
 *
 *  Each bundle definition needs to be declared in the
 *  platformsh_project_entity_bundle_info() also.
 *  Cannot use annotations without things getting snarled up.
 */
class PingMetric extends Metric {

  /**
   *
   */
  public function label() {
    return "A ping";
  }

  /**
   *
   */
  public function refresh() {
    $this->set('data', 'pinged ' . date("Y-m-d H:i:s"))
      ->save();
  }

}
