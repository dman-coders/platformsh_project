<?php


namespace Drupal\platformsh_project\Entity;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;

/**
 * A metric entity that saves the result of a ping test.
 *
 * Every metric bundle type also needs to be published at
 * `platformsh_project.metric_type.note.yml`
 * and referred to by
 * platformsh_project_entity_bundle_info_alter
 *
 * @inheritDoc
 *
 * @ContentEntityType(
 *   id = "ping",
 *   description = @Translation("Pings the site URL to check it responds"),
 *   label = @Translation("Ping"),
 *   base_table = "metric",
 *   bundle_entity_type = "metric_type",
 *   entity_keys = {
 *     "id" = "id",
 *     "bundle" = "bundle"
 *   },
 * )

 */
class PingMetric extends Metric {

  public function label() {
    return "A ping";
  }

  public function refresh() {
    $this->set('data', 'pinged ' . date("Y-m-d H:i:s"))
      ->save();
  }

  /**
   * Defines the field definitions for the Metric entity bundles.
   */
  public static function bundleFieldDefinitions(EntityTypeInterface $entity_type, $bundle, array $base_field_definitions) {
    // Define bundle-specific fields here.
    $fields = [];

    if ($bundle == 'ping_metric') {
      $fields['field_ping_data'] = BaseFieldDefinition::create('text')
        ->setLabel(t('Ping Data'))
        ->setDescription(t('Field to store ping data for Ping Metric.'));
    }
    // Add more bundle-specific fields as needed.

    return $fields;
  }

}
