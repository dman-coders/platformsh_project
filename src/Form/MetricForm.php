<?php

namespace Drupal\platformsh_project\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\node\NodeInterface;
use Drupal\platformsh_project\Entity\MetricType;

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

  /**
   * {@inheritdoc}
   *
   * @param array $form
   *   The form array.
   * @param \Drupal\Core\Form\FormStateInterface $formState
   *   The form state.
   * @param \Drupal\node\NodeInterface|null $project
   *   The project node, if any.
   * @param \Drupal\platformsh_project\Entity\MetricType|null $metricType
   *   The metric type, if any.
   *
   * @return array
   *   The form array.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Exception
   */
  public function buildForm(
    array $form,
    FormStateInterface $formState,
    ?NodeInterface $project = NULL,
    ?MetricType $metricType = NULL,
  ): array {
    // Need to create a dummy entity if it's not already done.
    // When we 'add metric' through the usual forms, magic happens to prepare that.
    // If this form is being called from a custom context,
    // I need to fill in some context for contentEntityForm requirements.
    if (empty($this->entity)) {
      throw new \Exception('Pretty sure we should no longer hit the case where an entity form is being built without a placeholder entity being instantiated. If this logic is never hit, then this chunk should be removed.');
      // Emulate:
      // $entity = $this->getEntityFromRouteMatch($route_match, $metricType->id());
      // Instantiate a new metric entity of the requested type.
      $entityTypeId = 'metric';
      $entityType = $this->entityTypeManager->getDefinition($entityTypeId);
      // 'bundle'
      $bundleKey = $entityType->getKey('bundle');
      $values = [];
      $values[$bundleKey] = $metricType->id();
      $entity = $this->entityTypeManager->getStorage($entityTypeId)
        ->create($values);
      $this->setEntity($entity);
    }
    // If this form was called from the context of a project,
    // then the metric should be preset to use that node as a target.
    // @see MetricController, that serves this form when called as
    // '/node/{project}/metric/add/{metric_type}'
    if (!empty($project)) {
      $this->entity->set('target', $project);
    }

    $form = parent::buildForm($form, $formState);

    // This form may be called with the desired metric type already defined.
    // Pre-fill that selection.
    if ($metricType) {
      $form['metric_type']['#default_value'] = $metricType->id();
      if (!empty($project)) {
        $form['#title'] = $this->t('Add a @label metric to @project project', [
          '@label' => $metricType->get('label'),
          '@project' => $project->getTitle(),
        ]);
      }
      else {
        $form['#title'] = $this->t('Add a @label metric', [
          '@label' => $metricType->get('label'),
        ]);
      }
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   *
   * @return int
   *   Either SAVED_NEW or SAVED_UPDATED, depending on the operation performed.
   *
   * @throws \Drupal\Core\Entity\EntityMalformedException
   */
  public function save(array $form, FormStateInterface $formState): int {
    $result = parent::save($form, $formState);

    $entity = $this->getEntity();

    $messageArguments = ['%label' => $entity->toLink()->toString()];
    $loggerArguments = [
      '%label' => $entity->label(),
      'link' => $entity->toLink($this->t('View'))->toString(),
    ];

    switch ($result) {
      case SAVED_NEW:
        $this->messenger()
          ->addStatus($this->t('New platformsh metric %label has been created.', $messageArguments));
        $this->logger('platformsh_project')
          ->notice('Created new platformsh metric %label', $loggerArguments);
        break;

      case SAVED_UPDATED:
        $this->messenger()
          ->addStatus($this->t('The platformsh metric %label has been updated.', $messageArguments));
        $this->logger('platformsh_project')
          ->notice('Updated platformsh metric %label.', $loggerArguments);
        break;
    }

    $formState->setRedirect('entity.metric.canonical', ['metric' => $entity->id()]);

    return $result;
  }

}
