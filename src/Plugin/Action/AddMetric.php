<?php

namespace Drupal\platformsh_project\Plugin\Action;

use Drupal\Core\Action\ActionBase;
use Drupal\Core\Action\ConfigurableActionBase;
use Drupal\Core\Form\ConfirmFormInterface;
use Drupal\Core\Form\FormInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Plugin\ContextAwarePluginTrait;
use Drupal\Core\Session\SessionManagerInterface;
use Drupal\Core\TempStore\PrivateTempStoreFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides an Add Metric action.
 *
 * 'Add Metric' is an action performed upon a project node.
 * It creates a metric (a placeholder to record the last measurement)
 * of a selected type,
 * associates the metric with the source node,
 * and starts monitoring it
 *
 * The source project node remains untouched,
 * as metrics reference the projects.
 *
 * For an action to be configurable with extra parameters,
 * we add a confirm_form_route_name
 * and define a form
 * And define that route.
 *
 * Drupal\Core\Config\Schema\SchemaIncompleteException: Schema errors for
 * system.action.platformsh_project_add_metric_action with the following
 * errors: system.action.platformsh_project_add_metric_action:configuration
 * missing schema
 * Not all metrics have parameters, but the presence of confirm_form_route_name
 * implies that they do.
 * And every metric that is parameterized also must have a schema yaml that defines these parameters?
 *
 * @Action(
 *   id = "platformsh_project_add_metric_action",
 *   label = @Translation("Add Metric"),
 *   type = "node",
 *   category = @Translation("Custom"),
 *   confirm_form_route_name = "add_metric_action.form"
 * )
 *
 */
class AddMetric extends ActionBase implements ContainerFactoryPluginInterface{

  /**
   * The tempstore factory.
   *
   * @var tempStoreFactory
   */
  protected $tempStoreFactory;
  private string $tempstore_id = 'add_metric_action';

  /**
   * User id is used to index the tempdatastore to remember who is doing what.
   *
   * @var currentUser
   */
  private $currentUser;

  /**
   * Constructor.
   *
   * Uses a TempStore for continuity of selections between confirmation forms.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\TempStore\PrivateTempStoreFactory $temp_store_factory
   *   The tempstore factory.
   * @param \Drupal\Core\Session\AccountInterface $current_user
   *   The session.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, PrivateTempStoreFactory $temp_store_factory, AccountInterface $current_user) {
    $this->configuration = $configuration;
    $this->pluginId = $plugin_id;
    $this->pluginDefinition = $plugin_definition;
    $this->tempStoreFactory = $temp_store_factory;
    $this->currentUser = $current_user;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static($configuration, $plugin_id, $plugin_definition,
      $container->get('tempstore.private'),
      $container->get('current_user')
    );
  }


  function getFormId() {
    return 'add_metric_confirm_form';
  }
  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
  }


  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return ['title' => ''];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    // List all available metric types.
    $bundleInfo = Drupal::service('entity_type.bundle.info')->getBundleInfo('metric');
    // Extract the bundle IDs and labels into a flat array.
    $bundleOptions = array_map(function ($bundle) {
      return $bundle['label'];
    }, $bundleInfo);

    $form['metric_type'] = [
      '#title' => $this->t('Type of metric'),
      '#type' => 'select',
      '#options' => $bundleOptions,
      '#required' => TRUE,
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Get the node ID from the form's state.
    $node_id = $form_state->get('node_id');

    // Get the bundle value from the form's state.
    $metric_type = $form_state->getValue('metric_type');

    // Do something with the node ID and bundle.
    // For example, create a new entity of the selected bundle type.
    // ...
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

    if ($node->getType() != 'project') {
      $this->messenger()->addError($this->t('This action cannot be applied to the @bundle bundle.', ['@bundle' => $bundle]));
      return;
    }

    // Do it.
  }

  /**
   * The common way of executing the action is from the multiple content admin screen.
   *
   * When doing this, we want to present the parameter form once, then apply the
   * action with identical settings to all items.
   * Thus, we need to use a temp store to remember the selected items
   * while we ask for the configs.
   * THis should also make batch processing more robust?
   *
   * @param array $entities
   *
   * @return void
   */
  public function executeMultiple(array $entities) {
    $ids = [];
    foreach ($entities as $entity) {
      $ids[$entity->id()] = $entity;
    }
    $this->tempStoreFactory->get($this->tempstore_id)
      ->set($this->currentUser->id(), $ids);
  }

}
