<?php

namespace Drupal\platformsh_project\Plugin\Action;

use Drupal\Core\Action\ActionBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Provides an Add Metric action.
 *
 * 'Add Metric' is an action performed upon a project node.
 * It creates a metric (a placeholder to record the last measurement)
 * of a selected type,
 * associates the metric with the source node,
 * and starts monitoring it.
 *
 * The source project node remains untouched,
 * as metrics reference the projects.
 *
 * The "action" is published and becomes available through Bulk operations
 * (available on the select at /admin/content)
 * Or Views Bulk operations.
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
 * And every metric that is parameterized also must have a schema yaml that
 * defines these parameters?
 *
 * @Action(
 *   id = "platformsh_project_add_metric_action",
 *   label = @Translation("Add Metric"),
 *   type = "node",
 *   category = @Translation("Custom"),
 * )
 */
class AddMetric extends ActionBase implements ContainerFactoryPluginInterface {

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * {@inheritdoc}
   */
  public function access($object, ?AccountInterface $account = NULL, $return_as_object = FALSE) {
    /** @var \Drupal\node\NodeInterface $object */
    $access = $object->access('update', $account, TRUE)
      ->andIf($object->title->access('edit', $account, TRUE));
    return $return_as_object ? $access : $access->isAllowed();
  }

  /**
   * {@inheritdoc}
   */
  public function execute($object = NULL): void {
    /** @var \Drupal\node\NodeInterface $object */
    if ($object->getType() != 'project') {
      $this->messenger()
        ->addError($this->t('This action cannot be applied to the @bundle bundle.', ['@bundle' => $bundle]));
      return;
    }

    // Do it.
    $this->messenger()->addMessage($this->t('Open the add metric form'));
    $url = Url::fromRoute('metric.add_unknown_metric_to_project', ['project' => $object->id()]);
    $redirect = new RedirectResponse($url->toString());
    // Immediately sending here is ill-advised.
    $redirect->send();
  }

}
