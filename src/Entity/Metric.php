<?php

namespace Drupal\platformsh_project\Entity;

use Drupal\aggregator\ItemInterface;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Url;
use Psr\Log\LoggerInterface;

/**
 * Defines the Metric entity.
 *
 * The Metric object holds a single measurement on a single axis.
 * The metric abstract class is subclassed by metrics that define their
 * own dimension and their own way of measuring it.
 * A 'Ping' metric will measure response time, and provide a summary
 * about it being "good" or "bad" or other.
 *
 * This generic Class defines attributes common to all.
 *
 * Every new type of metric that we create with a subclass
 * also needs a corresponding
 * `config/install/platformsh_project.metric_type.{bundle}}.yml`
 * and must be referred to by
 * `platformsh_project.module:platformsh_project_entity_bundle_info_alter()`
 * to register it.
 *
 * Implementation notes:
 * Too much magic is packed into annotations.
 * The existence of 'links' annotation creates magic routes.
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
 * MetricForm also preloads a reference to
 * the target project that a node is attached to
 * - if it is called in a special context,
 * that refers to the project node,
 * so that we can be directed to an 'add metric' form with the target
 * pre-filled. see routes.yaml.
 *
 * Although it's declared, we do not use the list_builder
 * to create the tab that is seen at /admin/content/metric
 * Avoid using a mediocre listbuilder, we provide a full admin view instead
 * as that allows us to add build operations. See
 *   /admin/structure/views/view/metrics
 *   views.view.metrics.yml
 *
 * @ContentEntityType(
 *   id = "metric",
 *   label = @Translation("Metric"),
 *   description = @Translation("Generic Abstract Metric description should get
 *   overridden"), label_collection = @Translation("Platformsh metrics"),
 *   label_singular = @Translation("platformsh metric"), label_plural =
 *   @Translation("platformsh metrics"), label_count = @PluralTranslation(
 *   singular = "@count platformsh metrics", plural = "@count platformsh
 *   metrics",
 *   ),
 *   handlers = {
 *     "list_builder" = "Drupal\Core\Entity\EntityListBuilder",
 *     "views_data" = "Drupal\views\EntityViewsData",
 *     "access" = "Drupal\Core\Entity\EntityAccessControlHandler",
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
 *     "id" = "id",
 *     "bundle" = "bundle"
 *   },
 *   links = {
 *     "collection" = "/admin/content/metric",
 *     "add-page" = "/metric/add",
 *     "add-form" = "/metric/add/{metric_type}",
 *     "canonical" = "/metric/{metric}",
 *     "edit-form" = "/metric/{metric}/edit",
 *     "delete-form" = "/metric/{metric}/delete",
 *   },
 *   bundle_entity_type = "metric_type",
 *   uri_callback = "Drupal\platformsh_project\Entity\metric::buildUri",
 *   bundle_label = @Translation("Metric type"),
 *   field_ui_base_route = "entity.metric_type.edit_form"
 * )
 */
class Metric extends ContentEntityBase implements ContentEntityInterface, EntityChangedInterface {

  use EntityChangedTrait;
  use Drupal\Core\Logger\LoggerChannelTrait;

  const REQUIREMENT_INFO = -1;

  const REQUIREMENT_OK = 0;

  const REQUIREMENT_WARNING = 1;

  const REQUIREMENT_ERROR = 2;

  const REQUIREMENT_INVALID = 3;

  const REQUIREMENT_DICTIONARY = [
    self::REQUIREMENT_INFO => 'Info',
    self::REQUIREMENT_OK => 'OK',
    self::REQUIREMENT_WARNING => 'Warning',
    self::REQUIREMENT_ERROR => 'Error',
    self::REQUIREMENT_INVALID => 'Invalid',
  ];

  /**
   * The project this metric belongs to.
   *
   * @var \Drupal\platformsh_project\Entity\Project
   */
  public Project $project;

  /**
   * The logger service.
   *
   * @var \Psr\Log\LoggerInterface
   */
  public LoggerInterface $logger;

  /**
   * Get the human-readable status description.
   *
   * @param int|null $status
   *   The status code, or NULL to use the current status.
   *
   * @return string
   *   The status description.
   */
  public function getStatusDescription($status = NULL): string {
    $status = $status ? $status : $this->get('status')->value;
    return self::REQUIREMENT_DICTIONARY[$status] ?? $status;
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entityType) {
    // Base fields are attached directly to the main entity table
    // as additional columns, like a traditional db schema
    // Base fields are not referred to as `field_data` style lookups
    // like most other UI-added fields would do.
    // Base fields - ID etc - are provided by the system if we ask for them
    // in the annotation by defining entity_keys.
    // This must be done explicitly for every subclass.
    $fields = parent::baseFieldDefinitions($entity_type);

    // The data model refers of the Drupal hook_requirements report model.
    // A "requirement" check returns a
    // * title
    // * value
    // * description
    // * severity
    // And these mini-reports get collated into a larger report on the system
    // status report page.
    // Our metric checks will be a very similar shape.
    // The 'changed' field is a special thing.
    // Internal tooling (EntityChangedTrait) helps it work the same as other
    // entities.
    // `changed` will only get updated if some value actually changed.
    // Setting a value and running save() will NOT touch `changed` unless
    // appropriate.
    // This seems to be provided by the EntityChangedInterface that we use.
    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the metric was last updated.'))
      ->setDisplayOptions('form', [
        'type' => 'datetime_timestamp',
        'weight' => 10,
      ])
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'timestamp',
        'weight' => 0,
      ]);

    // The simple status field.
    $fields['status'] = BaseFieldDefinition::create('list_string')
      ->setLabel(t('Status'))
      ->setDescription(t('The last known summary of this metric.'))
      ->setSettings([
        'allowed_values' => self::REQUIREMENT_DICTIONARY,
      ])
      ->setRequired(TRUE)
      ->setDefaultValue(self::REQUIREMENT_INVALID)
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setDisplayOptions('form', ['weight' => 10])
      ->setDisplayOptions('view', [
        'weight' => 0,
        'type' => 'status_formatter',
        'label' => 'inline',
      ]
      );

    // The data field. Used for simple values or structured data.
    $fields['data'] = BaseFieldDefinition::create('string_long')
      ->setLabel(t('Data'))
      ->setDescription(t('Used for simple values like response codes, or IDs, or structured data like yaml'))
      ->setRequired(FALSE)
      ->setDisplayOptions('form', [
        'type' => 'text_textarea',
        'weight' => 10,
        'settings' => [
          'rows' => 1,
        ],
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setDisplayOptions('view', [
        'weight' => 3,
        'label' => 'inline',
      ]);

    // The comment field.
    $fields['note'] = BaseFieldDefinition::create('string_long')
      ->setLabel(t('Note'))
      ->setDescription(t('Additional notes. Freetext summary or explanation of warnings.'))
      ->setRequired(FALSE)
      ->setDisplayOptions('form', [
        'type' => 'text_textarea',
        'weight' => 11,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setDisplayOptions('view', [
        'weight' => 6,
        'label' => 'inline',
      ]);

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
        'label' => 'hidden',
        'type' => 'datetime_timestamp',
        'weight' => 0,
      ])
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'timestamp',
        'weight' => 11,
        'settings' => [
          'date_format' => 'medium',
        ],
      ]);

    // The target entity reference field.
    $fields['target'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Target'))
      ->setDescription(t('The linked resource the Metric applies to.'))
      // Make required later.
      ->setRequired(FALSE)
      ->setSetting('target_type', 'node')
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setDisplayOptions('form', [
        'weight' => 10,
      ])
      ->setDisplayOptions('view', [
        'type' => 'entity_reference_label',
        'weight' => 0,
        'label' => 'inline',
        'settings' => ['link' => TRUE],
      ]);

    return $fields;
  }

  /**
   * Entity URI callback.
   *
   * For some reason this wasn't being autodetected from the bundle.
   * I'd expected it to be deduced from the annotation links:canonic.
   */
  public static function buildUri(ItemInterface $item) {
    return Url::fromUri($item->getLink());
  }

  /**
   * Utility to return the referenced project entity.
   *
   * Commonly used by several metrics.
   *
   * @return \Drupal\platformsh_project\Entity\Project
   *   The project entity.
   *
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   */
  protected function getProject() {
    // Dereference the entityreference.
    if (empty($this->project)) {
      $this->project = $this->get('target')
        ?->first()
        ?->get('entity')
        ?->getTarget()
        ?->getValue();
    }
    return $this->project;
  }

  /**
   * Return either the given logger, the already attached logger, or a\n   * generic global one.
   *
   * @param \Psr\Log\LoggerInterface|null $logger
   *   An optional logger instance.
   *
   * @return \Psr\Log\LoggerInterface
   *   The logger instance.
   */
  protected function getLogger($logger = NULL): LoggerInterface {
    if ($logger) {
      (
      $this->logger = $logger;
      )
      if (empty($this->logger)) {
        $this->logger = \Drupal::service('logger.factory')->get('platformsh_project');
      }
    }
    return $this->logger;
  }

  /**
   * Get the label for this metric.
   *
   * @return string
   *   The metric label.
   */
  public function label(): string {
    return "Un-named Metric";
  }

  /**
   * Refresh this metric.
   */
  public function refresh() {
    $this
      ->set('status', self::REQUIREMENT_INVALID)
      ->set('data', date('c'))
      ->set('note', 'Refreshed ' . date("Y-m-d H:i:s"))
      ->save();
  }

}
