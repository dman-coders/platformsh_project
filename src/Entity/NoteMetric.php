<?php

namespace Drupal\platformsh_project\Entity;

/**
 * A metric entity that performs no special action.
 *
 * It just exists as a repository for notes entered manually.
 *
 * Each bundle definition needs to be declared in the
 * platformsh_project_entity_bundle_info() also.
 * Cannot use annotations without things getting snarled up.
 *
 * @ContentEntityType(
 *    description = @Translation("Just a manually aded note."),
 *    id = "note_metric",
 *    label = @Translation("Note Metric via annotation"),
 *    entity_keys = {
 *      "id" = "id",
 *      "label" = "label",
 *      "uuid" = "uuid",
 *      "uid" = "user_id",
 *      "langcode" = "langcode",
 *      "status" = "status",
 *    },
 *    bundle_entity_type = "metric_type",
 *    permission_granularity = "bundle",
 *   )
 */
class NoteMetric extends Metric {

  /**
   * {@inheritdoc}
   */
  public function label(): string {
    return "A note";
  }

  /**
   * Refresh this metric.
   */
  public function refresh(): void {
    $this->set('data', 'Refreshed ' . date("Y-m-d H:i:s"))
      ->save();
    parent::refresh();
  }

}
