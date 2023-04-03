<?php


namespace Drupal\platformsh_project\Entity;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;

/**
 * A metric entity that saves the result of a ping test.
 */
class PingMetric extends Metric {

  public function label() {
    return "A ping";
  }

  public function refresh() {
    $this->set('data', 'pinged ' . date("Y-m-d H:i:s"))
      ->save();
  }

}
