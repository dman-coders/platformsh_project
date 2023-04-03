<?php

namespace Drupal\platformsh_project\Plugin\Action;

use Drupal\Core\Action\ActionBase;
use Drupal\Core\Session\AccountInterface;

/**
 * Provides a refresh metric action.
 *
 * @Action(
 *   id = "platform_project_refresh_metric_action",
 *   label = @Translation("refresh metric"),
 *   type = "metric",
 *   category = @Translation("Custom")
 * )
 *
 * @DCG
 * For a simple updating entity fields consider extending FieldUpdateActionBase.
 */
class RefreshMetric extends ActionBase {

  /**
   *
   */
  public function access($metric, AccountInterface $account = NULL, $return_as_object = FALSE) {
    return true; # TODO: maybe support access?

    /** @var \Drupal\platformsh_project\Entity\Metric $metric */
    $access = $metric->access('update', $account, TRUE)
      ->andIf($metric->title->access('edit', $account, TRUE));
    return $return_as_object ? $access : $access->isAllowed();
  }

  /**
   * {@inheritdoc}
   */
  public function execute($metric = NULL) {
    /** @var \Drupal\platformsh_project\Entity\Metric $metric */
    $metric
      ->set('data', 'New title ' . date("Y-m-d H:i:s"))
      ->save();

    if( method_exists($metric, 'refresh')) {
      $metric->refresh();
    }
  }

}
