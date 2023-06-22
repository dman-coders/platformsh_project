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
 * UNUSED, we are recycling the entity content form MetricForm
 * instead.
 *
 * This can be called either with or without the attached project,
 * and with or without the known metric type.
 * If unknown, they will be required, and displayed on the form.
 *
 * If all is known, and the metric type requires no additional configuration
 * (or the additional config is added in the query)
 * then the metric will be created and to form auto-submitted.
 */
class AddMetricForm extends FormBase {

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
  public function buildForm(array              $form,
                            FormStateInterface $form_state,
                            \Drupal\node\NodeInterface  $project = null,
                            \Drupal\platformsh_project\Entity\MetricType                     $metric_type = null) {
    $form['#title'] = $this->t('Add Metric');

    // This form may be called with an owning entity (a project node)
    // already chosen. This info should be retained and used to populate the data.
    // Store the node ID in the form's state.
    $form_state->set('node_id', $project->id());

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
      '#default_value' => $metric_type->id(),
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



}
