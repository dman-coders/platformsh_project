<?php

namespace Drupal\platformsh_project\Plugin\Action;

use Drupal\Core\Access\AccessResultInterface;
use Drupal\Core\Action\ActionBase;
use Drupal\Core\Entity\EntityStorageException;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\node\NodeInterface;
use Drupal\platformsh_api\ApiService;
use Exception;
use InvalidArgumentException;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * When adding actions to a module,
 * the declaration f the action gets saved into the `cache_config` database as
 * cid=system.action.node_unpublish_action etc.
 *
 * This can be seen with `drush cget system.action.node_make_sticky_action` etc.
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
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('platformsh_api.fetcher'),
      $container->get('entity_type.manager')
    );
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
   * Tell the given target entity to refreshFromAPI() itself,
   * if that method is implemented.
   *
   * {@inheritdoc}
   */
  public function execute($object = NULL) {
    /** @var \Drupal\node\NodeInterface $object **/
    if (method_exists($object, 'refreshFromAPI')) {
      return $object->refreshFromAPI();
    }
    $this->messenger()->addError("This entity does not implement refreshFromAPI()");
    return FALSE;
  }

}
