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
  public function execute($metric = NULL) {
    /** @var \Drupal\platformsh_project\Entity\Metric $metric */
    $metric
      ->set('data', 'New title')
      ->save();
  }

}
