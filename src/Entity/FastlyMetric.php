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

  /**
   * Fields that only exist on this bundle.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   * @param string $bundle
   * @param array $base_field_definitions
   *
   * @return array|\Drupal\Core\Field\FieldDefinitionInterface[]
   */
  public static function bundleFieldDefinitions(EntityTypeInterface $entity_type, $bundle, array $base_field_definitions) {
    $definitions = [];
    if ($bundle == 'fastly') {
      // Should be bundleFieldDefinition?
      // But that's not actually available.
      $definitions['account_id'] = FieldDefinition::create('string')
        ->setTargetBundle($bundle)
        ->setLabel(t('Fastly Account ID)'))
        ->setRequired(FALSE)
        ->setSetting('max_length', 255)
      // non-standard.
        ->setSetting('cardinality', 1)
        ->setDisplayOptions('form', [
          'type' => 'string_textfield',
          'weight' => -5,
        ])
        ->setDisplayConfigurable('form', TRUE)
        ->setDisplayOptions('view', [
          'label' => 'inline',
          'type' => 'string',
          'weight' => -5,
        ])
        ->setDisplayConfigurable('view', TRUE);
    }
    return $definitions;
  }

}
