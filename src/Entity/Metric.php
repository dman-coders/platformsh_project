<?php

namespace Drupal\platformsh_project\Entity;

use Drupal\Core\Entity\Annotation\ContentEntityType;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\platformsh_project\MetricInterface;

/**
 * Defines the Metric entity.
 *
 * Too much magic is packed into annotations.
 * The existence of 'links' creates magic routes.
 *  links[collection] brings into existence route `entity.metric.collection`
 *  links[add-page] brings into existence route `entity.metric.add-page`
 *  It seem these links need to be aligned with yamls like
 * `{}.links.task.yaml`
 * `{}.links.action.yaml`
 * Seems that though links are listed with dashes `links[edit-form]`
 * the corresponding routes are listed with underscores
 * /metric/{metric}/edit : entity.metric.edit_form
 *
 * @ContentEntityType(
 *   id = "metric",
 *   label = @Translation("Metric"),
 *   label_collection = @Translation("Platformsh metrics"),
 *   label_singular = @Translation("platformsh metric"),
 *   label_plural = @Translation("platformsh metrics"),
 *   label_count = @PluralTranslation(
 *     singular = "@count platformsh metrics",
 *     plural = "@count platformsh metrics",
 *   ),
 *   handlers = {
 *     "list_builder" = "Drupal\platformsh_project\MetricListBuilder",
 *     "views_data" = "Drupal\views\EntityViewsData",
 *     "form" = {
 *       "default" = "Drupal\platformsh_project\Form\MetricForm",
 *       "add" = "Drupal\platformsh_project\Form\MetricForm",
 *       "edit" = "Drupal\platformsh_project\Form\MetricForm",
 *       "delete" = "Drupal\Core\Entity\ContentEntityDeleteForm",
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\AdminHtmlRouteProvider",
 *     }
 *   },
 *   base_table = "metric",
 *   admin_permission = "administer metrics",
 *   entity_keys = {
 *     "id" = "id"
 *   },
 *   links = {
 *     "collection" = "/admin/content/metric",
 *     "add-form" = "/metric/add",
 *     "canonical" = "/metric/{metric}",
 *     "edit-form" = "/metric/{metric}/edit",
 *     "delete-form" = "/metric/{metric}/delete",
 *   },
 * )
 */
class Metric extends ContentEntityBase implements MetricInterface {

  // Hidden nastyness. If THIS is not here, then the `entity.metric.collection` route never shows up by magic.
  use EntityChangedTrait;

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    // Base fields - ID etc - are provided by the system if we ask for them
    // in the annotation.
    $fields = parent::baseFieldDefinitions($entity_type);


    // The type field.
    // TODO - this is really a bundle, probably.
    $fields['type'] = BaseFieldDefinition::create('list_string')
      ->setLabel(t('Type'))
      ->setDescription(t('The type of Metric.'))
      ->setSettings([
        'allowed_values' => [
          'cache_check' => 'Cache header check',
          'cdn_check' => 'CDN check',
          'storage_check' => 'Storage check',
        ],
      ])
      ->setRequired(TRUE)
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ;


    // The data field.
    $fields['data'] = BaseFieldDefinition::create('text_long')
      ->setLabel(t('Data'))
      ->setDescription(t('The data for the Metric.'))
      ->setRequired(FALSE);

    // The date field.
    $fields['timestamp'] = BaseFieldDefinition::create('timestamp')
      ->setLabel(t('Timestamp'))
      ->setDescription(t('The time the metric was measured.'))
      ->setRequired(TRUE)
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
    ;


    // The target entity reference field.
    $fields['target'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Target'))
      ->setDescription(t('The linked resource the Metric applies to.'))
      ->setRequired(False) # make required later
      ->setSetting('target_type', 'node')
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
    ;


    return $fields;
  }

}
