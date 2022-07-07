<?php

namespace Drupal\platformsh_project\Plugin\Action;

use Drupal\Core\Action\ActionBase;
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
      $dump = json_encode($response->getData(), JSON_PRETTY_PRINT);
      $this->messenger()->addStatus($dump );
      /** @var \Drupal\node\NodeInterface $node */
      $node->setTitle($response->title);
      // Store the raw data for review
      $node->set('field_data', $dump);
      // Now set the values we extracted
      $keys = ['plan', 'default_domain', 'region'];
      foreach($keys as $keyname) {
        if (isset($response->getData()[$keyname])) {
          $node->set('field_' . $keyname , $response->getData()[$keyname]);
        }
      }
      $node->save();
    }
    else {
      $this->messenger()->addError("No valid project ID");
    }

  }

}
