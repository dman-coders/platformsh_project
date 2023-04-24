<?php

namespace Drupal\platformsh_project\Plugin\Action;

use Drupal\Core\Action\ActionBase;
use Drupal\Core\Action\ConfigurableActionBase;
use Drupal\Core\Form\ConfirmFormInterface;
use Drupal\Core\Form\FormInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Plugin\ContextAwarePluginTrait;

/**
 * Provides an Add Metric action.
 *
 * 'Add Metric' is an action performed upon a project node.
 * It creates a metric of a selected type,
 * associates the metric with the source node, and starts monitoring it
 *
 * The source project node remains untouched, as metrics reference the projects.
 *
 * For an action to be configurable with extra parameters,
 * we add a confirm_form_route_name
 * and define a form
 * And define that route.
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
class AddMetric extends ActionBase{

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

  public function executeMultiple(array $entities) {
    return;
  }

}
