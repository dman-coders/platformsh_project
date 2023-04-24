<?php

namespace Drupal\platformsh_project\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;

/**
 * Defines the Metric type configuration entity.
 *
 * I have REMOVED the usual annotaions that define links and handlers
 * as this is functinoality that I don't need.
 * I have SHIFTED that functionality into platformsh_project_ui instead.
 * and re-injected the annotation stuff from there.
 *
 * Although the string doesn't appear anywhere,
 * the  keys mentioned in the links annotation implicitly create the routes
 * `entity.metric_type.collection`
 * `entity.metric_type.add_form`
 * `entity.metric_type.edit_form`
 * `entity.metric_type.delete_form`
 * These magic routes are then referred to by the
 * `{modulename}.links.{}.yaml` definitions.
 *
 * @ConfigEntityType(
 *   id = "metric_type",
 *   label = @Translation("Metric type"),
 *   label_collection = @Translation("Metric types"),
 *   label_singular = @Translation("metric type"),
 *   label_plural = @Translation("metrics types"),
 *   label_count = @PluralTranslation(
 *     singular = "@count metrics type",
 *     plural = "@count metrics types",
 *   ),
 *   handlers = {
*     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\AdminHtmlRouteProvider",
 *     }
 *   },
 *   admin_permission = "administer metric types",
 *   bundle_of = "metric",
 *   config_prefix = "metric_type",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "add-form" = "/admin/structure/metric_types/add"
 *   },
 *   config_export = {
 *     "id",
 *     "label",
 *     "uuid",
 *   }
 * )
 */
class MetricType extends ConfigEntityBundleBase {

  /**
   * The machine name of this metric type.
   *
   * @var string
   */
  protected $id;

  /**
   * The human-readable name of the metric type.
   *
   * @var string
   */
  protected $label;

}
