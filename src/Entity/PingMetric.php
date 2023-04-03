<?php


namespace Drupal\platformsh_project\Entity;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;

/**
 * A metric entity that saves the result of a ping test.
 *
 * Every mertic bundle type also needs to be published at
 * `platformsh_project.metric_type.note.yml`
 * and referred to by
 * platformsh_project_entity_bundle_info_alter
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