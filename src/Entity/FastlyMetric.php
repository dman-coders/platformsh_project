<?php

namespace Drupal\platformsh_project\Entity;

use Drupal\Core\Entity\Annotation\ContentEntityType;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\FieldDefinition;

/**
 * A metric entity that saves the result of a fastly test.
 *
 * Each bundle definition beeds to be declared in the
 * platformsh_project_entity_bundle_info() also.
 * Cannot use annotations without things getting snarled up.
 */
class FastlyMetric extends Metric {

  /**
   *
   */
  public function label(): string {
    return "Fastly check";
  }

  /**
   *
   */
  public function refresh() {
    $this->set('data', 'pinged ' . date("Y-m-d H:i:s"))
      ->save();
  }

}
