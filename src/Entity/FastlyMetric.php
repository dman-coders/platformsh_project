<?php

namespace Drupal\platformsh_project\Entity;

use Drupal\Core\Entity\Annotation\ContentEntityType;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\FieldDefinition;

/**
 * A metric entity that saves the result of a fastly test.
 *
 *  @ContentEntityType(
 *    id = "fastly",
 *    description = @Translation("Checks the Fastly service associated with a project"),
 *    label = @Translation("Fastly"),
 *    base_table = "metric",
 *    bundle_entity_type = "metric_type",
 *    entity_keys = {
 *      "id" = "id",
 *      "bundle" = "bundle"
 *    }
 *  )
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
