<?php

namespace Drupal\platformsh_project\Plugin\Action;

use Drupal\Core\Action\ActionBase;
use Drupal\Core\Entity\EntityReferenceSelection\SelectionWithAutocreateInterface;
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
 * so `drush cget platformsh_project.action.platformsh_project_refresh_from_api_action`
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
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ApiService $api_service) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->api_service = $api_service;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static($configuration, $plugin_id, $plugin_definition, $container->get('platformsh_api.fetcher'));
  }

  /**
   * {@inheritdoc}
   */
  public function access($node, AccountInterface $account = NULL, $return_as_object = FALSE) {
    /** @var \Drupal\node\NodeInterface $node */
    $access = $node->access('update', $account, TRUE)
      ->andIf($node->title->access('edit', $account, TRUE));
    return $return_as_object ? $access : $access->isAllowed();
  }

  /**
   * {@inheritdoc}
   */
  public function execute($node = NULL) {

    $projectID = $node->get('field_id')->value;
    if (!empty($projectID)) {
      /** @var \Platformsh\Client\Model\Project $response */
      $response = $this->api_service->getProject($projectID);
      $raw_dump=$response->getData();
      // Excise the links for brevity
      unset($raw_dump['_links']);
      $json_dump = json_encode($raw_dump, JSON_PRETTY_PRINT);
      $this->messenger()->addStatus($json_dump );
      /** @var \Drupal\node\NodeInterface $node */
      $node->setTitle($response->title);
      // Store the raw data for review
      $node->set('field_data', $json_dump);
      // Now set the values we extracted
      $keys = ['plan', 'default_domain', 'region', 'namespace'];
      foreach($keys as $keyname) {
        if (isset($response->getData()[$keyname])) {
          $node->set('field_' . $keyname , $response->getData()[$keyname]);
        }
      }

      // References need extra help.
      // They probably need to trigger autocomplete
      $keys = ['owner', 'organization'];
      foreach($keys as $keyname) {
        if (isset($response->getData()[$keyname])) {
          // Fetch or create the target first
          $target_guid = $response->getData()[$keyname];
          $target = $this->api_service::getEntityById($target_guid);

          if (empty($target)) {
            // Attempt autocreate.
            $target = $this->autocreateTargetEntityFromFieldWidget($keyname, $target_guid);

            if (!empty($target)) {
              $target_info = ['target_id' => $target->id];
              $node->set('field_' . $keyname , $target_info);
            } else {
              throw new \InvalidArgumentException("Could not find or autocreate target entity $target_guid");
            }

          }
        }
      }
      #$node->set('field_' . 'updated_at' , $response->getData()['updated_at']);

      $node->save();
    }
    else {
      $this->messenger()->addError("No valid project ID");
    }

  }

  /*
   * The parameters we need to know in order to create a target entity can be extracted from the field widget.
   */
  private function autocreateTargetEntityFromFieldWidget($field_name = 'organsation', $label = 'New Organisation') {
    // In order to leverage most of the existing autocreate functionality,
    // This should start by retrieving the form field $element that contains all the settings.
    // The options and things are all derived from that.

    // Alternatively I can short cut that introspection and make a bunch of assumptions.
    $options = [
      'auto_create' => true,
      'auto_create_bundle' => '',
      'target_type' => 'node',
      'target_bundles' => ['organisation' => 'organisation'],
      'handler' => 'default:node'
    ];
    // Identify the field handler
    /** @var \Drupal\node\Plugin\EntityReferenceSelection\NodeSelection $handler */
    $handler = \Drupal::service('plugin.manager.entity_reference_selection')->getInstance($options);
    // Just check that worked:
    if ($handler instanceof SelectionWithAutocreateInterface) {

      $entity_type_id = $options['target_type'];
      $bundle = reset($options['target_bundles']);
      $uid = 1;

      // Invoke the handlers Create func.
      $target = $handler->createNewEntity($entity_type_id, $bundle, $label, $uid);

    }
    else {
      throw new \InvalidArgumentException("Could not find or autocreate target entity $target_guid - autocomplete widget handelr not found.");
    }
    return $target;
  }

  private function autocreateTargetEntity($entity_type_id = 'node', $bundle = 'organisation', $label = 'New organisation', $uid = 1) {
    $values = [
      'bundle' => $bundle,
      'label' => $label,
      'uid' => $uid,
    ];
    $entity = $this->entityTypeManager->getStorage($entity_type_id)->create($values);
    $entity->save();
    return $entity;
  }

}
