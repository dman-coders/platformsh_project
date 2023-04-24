<?php

namespace Drupal\platformsh_project_ui;

use Drupal\Core\Config\Entity\ConfigEntityListBuilder;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Url;

/**
 * Defines a class to build a listing of metric type entities.
 *
 * TODO: remove this
 * This seems to add no extra value, can we do without it?
 * SHows up on /admin/structure/metric_types
 * Thanks to being defined as a `collection` link in MetricType annotations.
 *
 * @see \Drupal\platformsh_project\Entity\MetricType
 */
class MetricTypeListBuilder extends ConfigEntityListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['title'] = $this->t('Label');

    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    $row['title'] = [
      'data' => $entity->label(),
      'class' => ['menu-label'],
    ];

    return $row + parent::buildRow($entity);
  }

  /**
   * {@inheritdoc}
   */
  public function render() {

    $build = parent::render();
    $build['disclaimer'] = [
      '#markup' => 'Editing or adding metric types here probably won\'t do anything. Metric types need to be provided by classes that actually perform a data fetch',
    ];

    $build['table']['#empty'] = $this->t(
      'No minimetric types available. <a href=":link">Add metric type</a>.',
      [':link' => Url::fromRoute('entity.metric_type_fake.add_form')->toString()]
    );

    return $build;
  }

}
