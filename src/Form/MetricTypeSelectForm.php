<?php

use Drupal\Core\Entity\ContentEntityConfirmFormBase;
use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;

class MetricTypeSelectForm extends ContentEntityConfirmFormBase {
  public function getFormId() {
    return 'metric_type_select';
  }

  public function buildForm(array $form, FormStateInterface $form_state, $node_id = NULL) {

    $form['#title'] = $this->t('Choose the type of metric');

    // Store the node ID in the form's state.
    $form_state->set('node_id', $node_id);

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
