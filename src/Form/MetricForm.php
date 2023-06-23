<?php

namespace Drupal\platformsh_project\Form;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Routing\RouteMatchInterface;

/**
 * Form controller for the platformsh metric entity edit forms.
 *
 * This only exists to provide friendly messages during save().
 * The parent `save()` method is fine, but does no messaging,
 * and no redirect after save.
 *
 * Invoked by routes:
 * - metric.add_unknown_metric_to_project
 * - metric.add_known_metric_to_project
 *
 * I need to use an HtmlEntityFormController, not just an HtmlFormController
 */
class MetricForm extends ContentEntityForm {


  public function buildForm(array              $form,
                            FormStateInterface $form_state,
                            \Drupal\node\NodeInterface  $project = null,
                            \Drupal\platformsh_project\Entity\MetricType  $metric_type = null
  ) {
    # Need to create a dummy entity if it's not already done.
    # When we 'add metric' through the usual forms, magic happens to prepare that.
    # If this form is being called from a custom context,
    # I need to fill in some context for contentEntityForm requirements.
    if (empty($this->entity)) {
      throw new \exception('Pretty sure we should no longer hit the case where an entity form is being built without a placeholder entity being instantiated. If this logic is never hit, then this chunk should be removed.');
      # Emulate:
      # $entity = $this->getEntityFromRouteMatch($route_match, $metric_type->id());
      # Instantiate a new metric entity of the requested type.
      $entity_type_id = 'metric';
      $entity_type = $this->entityTypeManager->getDefinition($entity_type_id);
      $bundle_key = $entity_type->getKey('bundle'); # 'bundle'
      $values = [];
      $values[$bundle_key] = $metric_type->id();
      $entity = $this->entityTypeManager->getStorage($entity_type_id)->create($values);
      $this->setEntity($entity);
    }
    // If this form was called from the context of a project,
    // then the metric should be preset to use that node as a target.
    if (!empty($project)) {
      $this->entity->set('target', $project);
    }

    $form = parent::buildForm($form, $form_state);

    // This form may be called with The desired metric type already defined.
    // Pre-fill that selection.
    if ($metric_type) {
      $form['metric_type']['#default_value'] = $metric_type->id();
    }
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $result = parent::save($form, $form_state);

    $entity = $this->getEntity();

    $message_arguments = ['%label' => $entity->toLink()->toString()];
    $logger_arguments = [
      '%label' => $entity->label(),
      'link' => $entity->toLink($this->t('View'))->toString(),
    ];

    switch ($result) {
      case SAVED_NEW:
        $this->messenger()->addStatus($this->t('New platformsh metric %label has been created.', $message_arguments));
        $this->logger('platformsh_project')->notice('Created new platformsh metric %label', $logger_arguments);
        break;

      case SAVED_UPDATED:
        $this->messenger()->addStatus($this->t('The platformsh metric %label has been updated.', $message_arguments));
        $this->logger('platformsh_project')->notice('Updated platformsh metric %label.', $logger_arguments);
        break;
    }

    $form_state->setRedirect('entity.metric.canonical', ['metric' => $entity->id()]);

    return $result;
  }

}
