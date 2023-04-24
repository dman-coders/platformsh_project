<?php

namespace Drupal\platformsh_project\Form;

use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Session\SessionManagerInterface;
use Drupal\user\Entity\User;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Routing\RouteBuilderInterface;
use Drupal\Core\TempStore\PrivateTempStoreFactory;

/**
 * AddMetricActionForm.
 *
 * When adding a metric via an action, need to say which type of metric to add.
 * To do this, we provide a confirmation form that will be used to intercept the
 * flow before the Action::execute() runs.
 */
class AddMetricActionForm extends ConfirmFormBase {

  /**
   * Keep track of user input.
   */
  protected $userInput = [];

  /**
   * @return string
   */
  public function getFormId(): string {
    return 'add_metric_action_form';
  }

  /**
   * {@inheritdoc}
   */
  public function updateFields() {
    return 'successfully changed';
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $return_verify = $this->updateFields();
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['#title'] = $this->t('Choose the type of metric');

    // This form may be called with an owning entity (a project node)
    // already chosen. This info should be retained and used to populate the data.
    // Store the node ID in the form's state.
    $form_state->set('node_id', $node_id);

    // List all available metric types.
    $bundleInfo = \Drupal::service('entity_type.bundle.info')->getBundleInfo('metric');
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

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $submit_label,
      '#button_type' => 'primary',
    ];
    return $form;
  }


  public function getQuestion() {
    // TODO: Implement getQuestion() method.
  }

  public function getCancelUrl() {
    // TODO: Implement getCancelUrl() method.
  }

}
