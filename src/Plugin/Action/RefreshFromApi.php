<?php

namespace Drupal\platformsh_project\Plugin\Action;

use Drupal\Core\Access\AccessResultInterface;
use Drupal\Core\Action\ActionBase;
use Drupal\Core\Entity\EntityReferenceSelection\SelectionWithAutocreateInterface;
use Drupal\Core\Entity\EntityStorageException;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\platformsh_api\ApiService;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * When adding actions to a module,
 * the declaration f the action gets saved into the `cache_config` database as
 * cid=system.action.node_unpublish_action etc.
 *
 * This can be seen with `drush cget system.action.node_make_sticky_action` etc
 * so `drush cget
 * platformsh_project.action.platformsh_project_refresh_from_api_action`
 */

/**
 * Provides a Refresh from API action.
 *
 * @Action(
 *   id = "platformsh_project_refresh_from_api_action",
 *   label = @Translation("Refresh from API"),
 *   type = "node",
 *   category = @Translation("Custom")
 * )
 *
 */
class RefreshFromApi extends ActionBase implements ContainerFactoryPluginInterface {

  private ApiService $api_service;
  private EntityTypeManager $entity_type_manager;

  /**
   * Constructs a MessageAction object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param ApiService $api_service
   *   The plugin implementation definition.
   * @param EntityTypeManager $entity_type_manager
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ApiService $api_service, EntityTypeManager $entity_type_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->api_service = $api_service;
    $this->entity_type_manager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition): RefreshFromApi|ContainerFactoryPluginInterface|static {
    return new static($configuration, $plugin_id, $plugin_definition, $container->get('platformsh_api.fetcher'), $container->get('entity_type.manager'));
  }

  /**
   * {@inheritdoc}
   */
  public function access($object, AccountInterface $account = NULL, $return_as_object = FALSE): bool|AccessResultInterface {
    /** @var \Drupal\node\NodeInterface $object */
    $access = $object->access('update', $account, TRUE)
      ->andIf($object->title->access('edit', $account, TRUE));
    return $return_as_object ? $access : $access->isAllowed();
  }

  /**
   * {@inheritdoc}
   */
  public function execute($object = NULL) {
    /** @var \Drupal\node\NodeInterface $object **/

    $field = $object->get('field_id');
    $projectID = $object->get('field_id')->value;
    if (empty($projectID)) {
      $this->messenger()->addError("No valid project ID");
      return FALSE;
    }

    /** @var \Platformsh\Client\Model\Project $response */
    try {
      // Calling the API may fail for many reasons.
      $response = $this->api_service->getProject($projectID);
      // The API may return without error, but still not have data - if project is invalid.
      if (empty($response)) {
        $this->messenger()
          ->addError("API call returned empty. Probably an invalid Project ID. Update failed.");
        return FALSE;
      }
    } catch (\Exception $e) {
      $this->messenger()->addError("API call failed: " . $e->getMessage());
      return FALSE;
    }

    $raw_dump = $response->getData();
    // Excise the links for brevity
    unset($raw_dump['_links']);
    $json_dump = json_encode($raw_dump, JSON_PRETTY_PRINT);
    $this->messenger()->addStatus($json_dump);
    /** @var \Drupal\node\NodeInterface $node */
    $object->setTitle($response->title);
    // Store the raw data for review
    $object->set('field_data', $json_dump);
    // Now set the values we extracted
    $keys = ['plan', 'default_domain', 'region', 'namespace'];
    foreach ($keys as $key_name) {
      if (isset($response->getData()[$key_name])) {
        $object->set('field_' . $key_name, $response->getData()[$key_name]);
      }
    }

    $this->autocreateTargetEntities($object, $response->getData());
    #$node->set('field_' . 'updated_at' , $response->getData()['updated_at']);

    // Take care, as this action may be called on hook_entity_presave, avoid a loop.
    if (!$object->isNew()) {
      try {
        $object->save();
      } catch (EntityStorageException $e) {
        $this->messenger()->addError("Failed to save node " . $e->getMessage());
      }
    }
    return TRUE;
  }


  /**
   * For each external entity that a Project refers to,
   * Ensure the named target exists, creating it if neccessary.
   *
   * @var \Drupal\node\NodeInterface $node
   * @return void
   */
  function autocreateTargetEntities(\Drupal\node\NodeInterface $node, array $raw_data): void {
    // References need extra help.
    $keys = [
      'user' => 'owner',
      'organization' => 'organization_id'
    ];
    foreach ($keys as $key_type => $key_name) {
      /** @var \Drupal\Core\Field\FieldItemListInterface $value */
      $value = $raw_data[$key_name];
      if (!empty($target_guid = $value)) {
        // Fetch or create the target first
        $target = $this->api_service::getEntityById($target_guid);

        if (empty($target)) {
          // Attempt auto create.
          $entity_type_id = 'node';
          $target_data = [
            'bundle' => $key_type, # it's aliased as 'type'. yay.
            'type' => $key_type,
            'title' => $target_guid,
            'field_id' => $target_guid,
          ];
          $target = $this->autocreateTargetEntity($entity_type_id, $target_data);
        }

        if (!empty($target)) {
          $target_info = ['target_id' => $target->id()];
          $node->set('field_' . $key_name, $target_info);
        }
        else {
          throw new \InvalidArgumentException("Could not find or auto create target entity $target_guid");
        }
      }
    }
  }

  /**
   * @param string $entity_type_id
   * @param array $values
   *
   * @return \Drupal\Core\Entity\EntityInterface
   */
  private function autocreateTargetEntity(string $entity_type_id, array $values) {
    $this->messenger()
      ->addStatus("Auto-creating an ${values['bundle']} called ${values['field_id']}");
    $entity = $this->entity_type_manager
      ->getStorage($entity_type_id)
      ->create($values);
    $entity->save();
    return $entity;
  }

}
