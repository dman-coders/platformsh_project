<?php

namespace Drupal\platformsh_project\Entity;

use Drupal\Core\Entity\Annotation\ContentEntityType;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\FieldDefinition;

/**
 * A metric entity that saves the result of a fastly test.
 *
 * These are not really ContentEntityType classes, they are bundle definitions.
 * We should not have to repeat stuff that the Entity type defintion
 * has already taken over.
 * DO NOT declare `base_table` on bundle classes or the bundle will take over
 * from the real entity Type and bad and obscure things go wrong.
 *
 *  @ContentEntityType(
 *    id = "fastly",
 *    description = @Translation("Checks the Fastly service associated with a project"),
 *    label = @Translation("Fastly"),
 *    bundle_entity_type = "metric_type",
 *    entity_keys = {
 *      "id" = "id",
 *      "bundle" = "bundle"
 *    },
 *
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
