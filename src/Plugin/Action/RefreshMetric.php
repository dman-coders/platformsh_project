<?php

namespace Drupal\platformsh_project\Plugin\Action;

use Drupal\Core\Action\ActionBase;
use Drupal\Core\Session\AccountInterface;

/**
 * Provides a refresh metric action.
 *
 * Note, every 'Action' that we declare and want to install with a
 * `system.action.{id}.yml in `config/install` also needs to have its
 * configuration definition defined
 * `config/schema:action.configuration.{id}`
 * EVEN IF this action has no configuration options?
 * Not using ConfigurableActionBase, but to do a config import of an action
 * like this, still need to define a `configuration` placeholder. Otherwise the
 * configSchemaChecker complains with 'missing schema` during importing during
 * module installation during testing ONLY.
 *
 * @Action(
 *   id = "platformsh_project_refresh_metric_action",
 *   label = @Translation("Refresh Metric"),
 *   type = "metric",
 *   category = @Translation("Custom")
 * )
 *
 * @DCG
 * For a simple updating entity fields consider extending
 *   FieldUpdateActionBase.
 */
class RefreshMetric extends ActionBase {

  /**
   * {@inheritdoc}
   */
  public function access($object, ?AccountInterface $account = NULL, $return_as_object = FALSE) {
    /** @var \Drupal\platformsh_project\Entity\Metric $object */
    $access = $object->access('update', $account, TRUE);
    return $return_as_object ? $access : $access->isAllowed();
  }

  /**
   * {@inheritdoc}
   */
  public function execute($metric = NULL): void {
    /** @var \Drupal\platformsh_project\Entity\Metric $metric */
    #$metric
    #  ->set('data', 'New title ' . date("Y-m-d H:i:s"))
    #  ->save();

    if (method_exists($metric, 'refresh')) {
      $metric->refresh();
    }
  }

}
