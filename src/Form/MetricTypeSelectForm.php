<?php

use Drupal\Core\Entity\ContentEntityConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form for selecting metric types.
 */
class MetricTypeSelectForm extends ContentEntityConfirmFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'metric_type_select';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $nodeId = NULL) {
    $form['#title'] = $this->t('Choose the type of metric');

    // Store the node ID in the form's state.
    $form_state->set('node_id', $nodeId);

    // List all available metric types.
    $bundleInfo = Drupal::service('entity_type.bundle.info')
      ->getBundleInfo('metric');
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
  public function submitForm(array &$form, FormStateInterface $formState) {
    // Get the node ID from the form's state.
    $nodeId = $formState->get('node_id');

    // Get the bundle value from the form's state.
    $metricType = $formState->getValue('metric_type');

    // Do something with the node ID and bundle.
    // For example, create a new entity of the selected bundle type.
    // ...
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    // No question, just a form selection that is required.
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    $entity = $this->getEntity();
    if ($entity->hasLinkTemplate('collection')) {
      // If available, return the collection URL.
      return $entity->toUrl('collection');
    }
    else {
      // Otherwise fall back to the default link template.
      return $entity->toUrl();
    }
  }

}
