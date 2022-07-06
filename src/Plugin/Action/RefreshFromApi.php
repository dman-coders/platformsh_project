<?php

namespace Drupal\platformsh_project\Plugin\Action;

use Drupal\Core\Action\ActionBase;
use Drupal\Core\Session\AccountInterface;
use Drupal\platformsh\ApiClient;

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
 * @DCG
 * For a simple updating entity fields consider extending FieldUpdateActionBase.
 */
class RefreshFromApi extends ActionBase {

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
    /** @var \Drupal\node\NodeInterface $node */
    $node->setTitle($this->t('New title'))->save();

    $apiClient = new \Drupal\platformsh_api\ApiClient;

    $projectID = $node->get('id');
    if (!empty($projectID)) {
      $this->messenger()
        ->addStatus($this->t('Running API request getProjectInfo.'));
      $response = $apiClient->getProject($projectID);
      $this->messenger()->addStatus($this->t('Ran API request.'));
      $response = $this->projectInfoToRenderable($response);
      $this->messenger()->addStatus($response);
    }
    else {
      $this->messenger()->addError("No valid project ID");
    }

  }

}
