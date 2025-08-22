<?php

namespace Drupal\platformsh_project\Plugin\Action;

use Drupal\Core\Access\AccessResultInterface;
use Drupal\Core\Action\ActionBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\platformsh_api\ApiService;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Notes-to-self about how actions work, ignore.
 *
 * When adding actions to a module,
 * the declaration of the action gets saved into the `cache_config` database as
 * cid=system.action.node_unpublish_action etc.
 *
 * This can be seen with `drush cget system.action.node_make_sticky_action` etc.
 * so `drush cget
 * platformsh_project.action.platformsh_project_refresh_from_api_action`
 */

/**
 * Provides a Refresh from API action.
 *
 * The actual work is done by the objects themselves,
 * IF they implement their own refreshFromAPI() method.
 *
 * @see \Drupal\platformsh_project\Entity\ApiResource
 *
 * This will initiate an API request to the remote service,
 * and update the local entity with data retrieved.
 *
 * This should be applied to a node of type Platformsh 'project',
 * though organizations and users can use it also.
 *
 * See \Drupal\platformsh_project\Entity\ApiResource::refreshFromAPI()
 *
 * @Action(
 *   id = "platformsh_project_refresh_from_api_action",
 *   label = @Translation("Refresh from API"),
 *   type = "node",
 *   category = @Translation("Custom")
 * )
 */
class RefreshFromApi extends ActionBase implements ContainerFactoryPluginInterface {

  /**
   * The API service.
   *
   * @var \Drupal\platformsh_api\ApiService
   */
  private ApiService $apiService;

  /**
   * Constructs a RefreshFromApi action.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $pluginId
   *   The plugin_id for the plugin instance.
   * @param mixed $pluginDefinition
   *   The plugin implementation definition.
   * @param \Drupal\platformsh_api\ApiService $apiService
   *   The API service.
   */
  public function __construct(array $configuration, $pluginId, $pluginDefinition, ApiService $apiService) {
    parent::__construct($configuration, $pluginId, $pluginDefinition);
    $this->apiService = $apiService;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition): RefreshFromApi|ContainerFactoryPluginInterface|static {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('platformsh_api.fetcher'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function access($object, ?AccountInterface $account = NULL, $return_as_object = FALSE): bool|AccessResultInterface {
    /** @var \Drupal\node\NodeInterface $object */
    $access = $object->access('update', $account, TRUE)
      ->andIf($object->get('title')->access('edit', $account, TRUE));
    return $return_as_object ? $access : $access->isAllowed();
  }

  /**
   * Tell the given target entity to refreshFromAPI() itself.
   *
   * If that method is implemented.
   *
   * {@inheritdoc}
   */
  public function execute($object = NULL) {
    /** @var \Drupal\node\NodeInterface $object * */
    if (method_exists($object, 'refreshFromAPI')) {
      return $object->refreshFromAPI();
    }
    $this->messenger()
      ->addError("This entity does not implement refreshFromAPI()");
    return FALSE;
  }

}
