<?php

namespace Drupal\platformsh_project\Entity;

use Drupal\Core\Entity\Annotation\ContentEntityType;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;

/**
 * A metric entity that saves the result of a ping test.
 *
 * Every metric bundle type also needs to be published at
 * `platformsh_project.metric_type.note.yml`
 * and referred to by
 * platformsh_project_entity_bundle_info_alter.
 *
 * @inheritDoc
 *
 * @ContentEntityType(
 *   id = "ping",
 *   description = @Translation("Pings the site URL to check it responds"),
 *   label = @Translation("Ping"),
 *   bundle_entity_type = "metric_type",
 *   entity_keys = {
 *     "id" = "id",
 *     "bundle" = "bundle"
 *   },
 * )
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
