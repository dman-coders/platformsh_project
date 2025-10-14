<?php

namespace Drupal\platformsh_project\DrushCommand;

use Drush\Commands\DrushCommands;

/**
 * A Drush commandfile.
 *
 * Provides utilities for setting up dummy content for testing or automation.
 */
class PlatformshProjectCommands extends DrushCommands {

  /**
   * Create a project from ProjectID.
   *
   * @param string $project_id
   *   Project ID (Platformsh hash) to look up.
   *
   * @return string
   *   The summary message.
   *
   * @command platformsh:create-project
   * @aliases psh:create-project
   * @usage platformsh_project:create-project abcdefgh
   */
  public function createProject(string $project_id): string {
    $field_values = [
      'type' => 'project',
      'title' => $project_id,
      'field_id' => $project_id,
    ];
    $entity_type_id = 'node';

    // Look-ahead to avoid creating a dupe.
    /** @var \Drupal\node\Entity\Node $project */
    $existing_project = \Drupal::entityTypeManager()
      ->getStorage($entity_type_id)
      ->loadByProperties(['field_id' => $project_id]);
    if (!empty($existing_project)) {
      $message = sprintf("Project %s already exists. Not creating a duplicate", $project_id);
      $this->logger()->error($message);
      return $message;
    }

    $this->logger()->info(sprintf("Creating a project %s", $project_id));
    /** @var \Drupal\node\Entity\Node $project */
    $project = \Drupal::entityTypeManager()
      ->getStorage($entity_type_id)
      ->create($field_values);
    // hook_entity_presave() will do a lookup to prepopulate new project info.
    $project->save();
    if ($project->id()) {
      $message = sprintf("Created a project %s", $project->getTitle());
      $this->logger()->success($message, ['link' => $project->toLink('View project')->toString()]);
    }
    else {
      $message = sprintf("Failed to create a project %s", $project->getTitle());
      $this->logger()->error($message);
    }
    return $message;
  }

  /**
   * Prepopulate project with some test IDs.
   *
   * @command platformsh:create-test-content
   * @aliases psh:create-test-content
   * @usage platformsh_project-commandName create-test-content
   *   Pre-load some sample projects for experimentation.
   *   Requires admin-level access if these projects are not yours.
   */
  public function createTestContent(): void {
    $sample_ids = [
      'zgetjqqk5u626' => "dunno",
      'aytf4iaebr2xy' => "another",
      'k5lc5wez3aso2' => "hello world",
      'log5ehrvf6tjg' => "D7",
      '46pkp4iz4jvbi' => "msfsydney",
    ];
    $sample_metrics = [
      'note',
      'ping',
      'drupalcache',
    ];
    $this->logger()->info("Creating sample projects");
    foreach ($sample_ids as $project_id => $label) {
      $this->createProject($project_id);
      foreach ($sample_metrics as $metric_id) {
        $this->createMetric($project_id, $metric_id);
      }
    }
  }

  /**
   * Add a new metric to the given project.
   *
   * @param string $project_id
   *   Project ID (platformsh hash) to attach metric to (must
   *   exist)
   * @param string $metric_type
   *   Machine name of metric type ['ping', 'note', ...].
   *
   * @command platformsh:create-metric
   * @aliases psh:create-metric
   * @usage drush platformsh_project:create-metric abcdefg ping
   */
  public function createMetric(string $project_id, string $metric_type): string {
    $this->output()->writeln("Creating metric for project");

    // Find the project object that the project_id refers to.
    $project = platformsh_project_get_project_by_project_id($project_id);
    if (!$project) {
      $message = sprintf("Failed to find a project with ID %s. Aborting.", $project_id);
      $this->logger()->error($message);
      return $message;
    }

    // Validate the metric type.
    /** @var array $bundle_info */
    $bundle_info = \Drupal::service('entity_type.bundle.info')
      ->getBundleInfo('metric');
    if (!isset($bundle_info[$metric_type])) {
      $message = sprintf("Invalid metric type '%s'. Available types are: %s", $metric_type, implode(', ', array_keys($bundle_info)));
      $this->logger()->error($message);
      return $message;
    }

    $field_values = [
      'bundle' => $metric_type,
      'target' => ['target_id' => $project->id()],
    ];
    $entity_type_id = 'metric';

    // Look-ahead to avoid creating a dupe.
    /** @var \Drupal\node\Entity\Metric $metric */
    $existing_metric = \Drupal::entityTypeManager()
      ->getStorage($entity_type_id)
      ->loadByProperties($field_values);
    if (!empty($existing_metric)) {
      $message = sprintf("Metric %s already exists on project %s. Not creating a duplicate", $metric_type, $project->getTitle());
      $this->logger()->error($message, ['link' => $project->toLink('View project')->toString()]);
      return $message;
    }

    $this->logger()
      ->info(sprintf("Creating a '%s' metric for project:'%s'", $metric_type, $project->getTitle()), [
        'link' => $project->toLink('View project')->toString(),
      ]);

    /** @var \Drupal\platformsh_project\Entity\Metric $metric */
    $metric = \Drupal::entityTypeManager()
      ->getStorage($entity_type_id)
      ->create($field_values);
    $metric->save();
    /** @var \Drupal\platformsh_project\Entity\MetricType $metrictype_entity */
    $metrictype_entity = $metric->bundle->entity;
    // ? Should not be possible, but sometimes we can create a Metric
    // with an invalid $metric_type
    if (empty($metrictype_entity)) {
      throw new \InvalidArgumentException(sprintf("Created a metric of type %s, but this is apparently an invalid MetricType. This happens if there is not a required config/install/platformsh_project.metric_type.{%s}.yml file.", $metric_type, $metric_type));
    }
    /** @var string $bundle_label */
    $bundle_label = $metrictype_entity->label();
    if ($metric->id()) {
      $message = sprintf("Created a metric %s", $bundle_label);
      $this->logger()->success($message, [
        'link' => implode(' | ', [
          $metric->toLink('View metric')->toString(),
          $project->toLink('View project')->toString(),
        ]),
      ]);
    }
    else {
      $message = sprintf("Failed to create a metric %s", $bundle_label);
      $this->logger()->error($message, ['link' => $project->toLink('View project')->toString()]);
    }
    return $message;
  }

  /**
   * Attempt to reset the entity fields to factory settings.
   *
   * Scaffolding function.
   *
   * @command platformsh:reset-fields
   * @aliases psh:reset-fields
   * @bootstrap full
   * @usage platformsh:reset-fields
   *   Reset the fields attached to metric entites to factory settings.
   *   Apply schema updates to the fields if they already exist.
   */
  public function resetFields(): void {
    platformsh_project_update_bundles();
    platformsh_project_update_fields();
    $this->logger()->success("Reset the fields");
  }

  /**
   * Delete all metrics, so the module can be uninstalled.
   *
   * @command platformsh:delete-metrics
   * @aliases psh:delete-metrics
   *
   * @usage drush platformsh_project:delete-metrics
   */
  public function deleteMetrics(): void {
    platformsh_project_delete_all_metrics();
  }

}
