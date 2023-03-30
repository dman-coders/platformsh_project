<?php

namespace Drupal\platformsh_project\Entity;

use Drupal\Core\Entity\Annotation\ContentEntityType;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;

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
 * `Drupal\platformsh_project\Form\MetricForm`
 * is really just a wrapper around
 * `Drupal\Core\Entity\ContentEntityForm`
 * It exists just for messaging, as ContentEntityForm::save() is weak.
 *
 * Although it's declared, we do not use the list_builder
 * to create the tab that is seen at /admin/content/metric
 * Avoid using a mediocre listbuilder, we provide a full admin view instead
 * as that allows us to add build operations. See
 *   /admin/structure/views/view/metrics
 *   views.view.metrics.yml
 *
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
 *     "list_builder" = "Drupal\Core\Entity\EntityListBuilder",
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
class Metric extends ContentEntityBase implements ContentEntityInterface, EntityChangedInterface {

  use EntityChangedTrait;

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    // Base fields - ID etc - are provided by the system if we ask for them
    // in the annotation.
    $fields = parent::baseFieldDefinitions($entity_type);

    // The 'changed' field is a special thing.
    // Internal tooling (EntityChangedTrait)  helps it work the same as other entities.
    // `changed` will only get updated if some value actually changed.
    // Setting a value and running save() will NOT touch `changed` unless appropriate.
    // This seems to be provided by the EntityChangedInterface that we use.

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the metric was last updated.'))
      ->setDisplayOptions('form', [
        'type' => 'datetime_timestamp',
        'weight' => 10,
      ])
      ->setDisplayOptions('view', [
        'type' => 'number',
        'weight' => 0,
      ])
    ;


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
      ->setDisplayOptions('form', ['weight' => 10])
      ->setDisplayOptions('view', ['weight' => 0])
      ;


    // The data field.
    $fields['data'] = BaseFieldDefinition::create('string_long')
      ->setLabel(t('Data'))
      ->setDescription(t('The data for the Metric.'))
      ->setRequired(FALSE)
      ->setDisplayOptions('form', [
        'type' => 'text_textarea',
        'weight' => 10,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setDisplayOptions('view', ['weight' => 0,])
    ;

    // The date field.
    // Default widget is datetime_timestamp but that's annoying.
    // Drupal\Core\Datetime\Plugin\Field\FieldWidget\TimestampDatetimeWidget
    // I wanna paste.
    // Should set the default value to now()
    // Maybe can just re-use `changed` for most of the purpose this exists.
    $fields['timestamp'] = BaseFieldDefinition::create('timestamp')
      ->setLabel(t('Timestamp'))
      ->setDescription(t('The time the metric was measured.'))
      ->setRequired(FALSE)
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setDisplayOptions('form', [
        'type' => 'number',
        'weight' => 0,
      ])
    ;

    // The target entity reference field.
    $fields['target'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Target'))
      ->setDescription(t('The linked resource the Metric applies to.'))
      ->setRequired(False) # make required later
      ->setSetting('target_type', 'node')
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setDisplayOptions('form', ['weight' => 10])
    ;


    return $fields;
  }

}
