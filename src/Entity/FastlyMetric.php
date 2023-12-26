<?php

namespace Drupal\platformsh_project\Entity;

use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\FieldDefinition;

/**
 * A metric entity that saves the result of a fastly test.
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
