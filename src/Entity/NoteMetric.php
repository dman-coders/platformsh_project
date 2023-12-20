<?php

namespace Drupal\platformsh_project\Entity;

/**
 * A metric entity that performs no special action,
 * it just exists as a repository for notes entered manually.
 *
 * @inheritDoc
 *
 * @ContentEntityType(
 *   id = "note",
 *   description = @Translation("An arbitrary user-added note"),
 *   label = @Translation("Note"),
 *   base_table = "metric",
 *   bundle_entity_type = "metric_type",
 *   entity_keys = {
 *     "id" = "id",
 *     "bundle" = "bundle"
 *   },
 * )
 */
class NoteMetric extends Metric {

  /**
   *
   */
  public function label() {
    return "A note";
  }

  /**
   *
   */
  public function refresh() {
    $this->set('data', 'Refreshed ' . date("Y-m-d H:i:s"))
      ->save();
  }

}
