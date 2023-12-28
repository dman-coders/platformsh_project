<?php

namespace Drupal\platformsh_project\Entity;

use Drupal\Core\Entity\Annotation\ContentEntityType;

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
 *   bundle_entity_type = "metric_type",
 *   entity_keys = {
 *     "id" = "id",
 *     "bundle" = "bundle"
 *   },
 *   handlers = {
 *      "list_builder" = "Drupal\Core\Entity\EntityListBuilder",
 *      "views_data" = "Drupal\views\EntityViewsData",
 *      "form" = {
 *        "default" = "Drupal\platformsh_project\Form\MetricForm",
 *        "add" = "Drupal\platformsh_project\Form\MetricForm",
 *        "edit" = "Drupal\platformsh_project\Form\MetricForm",
 *        "delete" = "Drupal\Core\Entity\ContentEntityDeleteForm",
 *      },
 *      "route_provider" = {
 *        "html" = "Drupal\Core\Entity\Routing\AdminHtmlRouteProvider",
 *      }
 *    },
 *   links = {
 *      "collection" = "/admin/content/metric",
 *      "add-page" = "/metric/add",
 *      "add-form" = "/metric/add/{metric_type}",
 *      "canonical" = "/metric/{metric}",
 *      "edit-form" = "/metric/{metric}/edit",
 *      "delete-form" = "/metric/{metric}/delete",
 *    },
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
