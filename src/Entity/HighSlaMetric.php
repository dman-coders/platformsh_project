<?php

namespace Drupal\platformsh_project\Entity;

/**
 * A metric entity that checks if Nodeping HighSLA monitoring is enabled for this project.
 *
 * Every metric bundle type also needs to be published at
 * `platformsh_project.metric_type.note.yml`
 * and referred to by
 * platformsh_project_entity_bundle_info_alter.
 *
 * @inheritDoc
 *
 * @ContentEntityType(
 *   id = "highsla",
 *   description = @Translation("Checks if HighSLA monitoring is active"),
 *   label = @Translation("HighSLA"),
 *   base_table = "metric",
 *   bundle_entity_type = "metric_type",
 *   entity_keys = {
 *     "id" = "id",
 *     "bundle" = "bundle"
 *   },
 * ) *
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
