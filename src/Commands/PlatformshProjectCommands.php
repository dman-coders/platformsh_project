<?php

namespace Drupal\platformsh_project\Commands;

use Consolidation\OutputFormatters\StructuredData\RowsOfFields;
use Drupal;
use Drush\Attributes\Argument;
use Drush\Commands\DrushCommands;

/**
 * A Drush commandfile.
 *
 * In addition to this file, you need a drush.services.yml
 * in root of your module, and a composer.json file that provides the name
 * of the services file to use.
 *
 * See these files for an example of injecting Drupal services:
 *   - http://cgit.drupalcode.org/devel/tree/src/Commands/DevelCommands.php
 *   - http://cgit.drupalcode.org/devel/tree/drush.services.yml
 */
class PlatformshProjectCommands extends DrushCommands {

  /**
   * Create a project from ProjectID.
   *
   * @command platformsh:create-project
   *
   * @param $project_id string Project ID (Platformsh hash) to look up
   *
   * @aliases psh:create-project
   * @usage platformsh_project:create-project abcdefgh
   */
  public function createProject(string $project_id, $options = []) {
    $field_values = [
      'type' => 'project',
      'title' => $project_id,
      'field_id' => $project_id,
    ];
    $entity_type_id = 'node';
    $this->logger()->info(sprintf("Creating a project %s", $project_id));
    $project = Drupal::entityTypeManager()
      ->getStorage($entity_type_id)
      ->create($field_values);
    // hook_entity_presave() will do a lookup to prepopulate new project info.
    $project->save();
    if ($project->id()) {
      $this->logger()
        ->success(sprintf("Created a project %s", $project->getTitle()));
    }
    else {
      $this->logger()
        ->error(sprintf("Failed to create a project %s", $project->getTitle()));
    }
  }

  /**
   * @command platformsh:create-test-content
   * @aliases psh:create-test-content
   * @usage platformsh_project-commandName create-test-content
   *   Pre-load some sample projects for experimentation.
   *   Requires admin-level access if these projects are not yours.
   */
  public function createTestContent() {
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
    $this->logger()->info(sprintf("Creating sample projects"));
    foreach ($sample_ids as $project_id => $label) {
      $this->createProject($project_id);
      foreach($sample_metrics as $metric_id) {
        $this->createMetric($project_id, $metric_id);
      }
    }
  }

  /**
   * Add a new metric to the given project.
   *
   * @command platformsh:create-metric
   * @aliases psh:create-metric
   *
   * @param $project_id Project ID (platformsh hash) to attach metric to (must
   *   exist)
   * @param $metric_type Machine name of metric type ['ping', 'note', ...]
   *
   * @usage drush platformsh_project:create-metric abcdefg ping
   */
  public function createMetric($project_id, $metric_type, $options = []) {
    $this->output()->writeln(sprintf("Creating metric for project"));
    // Find the project object that the project_id refers to.
    $project = platformsh_project_get_project_by_project_id($project_id);
    if (!$project) {
      $this->logger()
        ->error(sprintf("Failed to find a project with ID %s. aborting.", $project_id));
      return;
    }

    $field_values = [
      'bundle' => $metric_type,
      'target' => ['target_id' => $project->id()],
    ];
    $entity_type_id = 'metric';
    $this->logger()
      ->info(sprintf("Creating a %s metric for %s", $metric_type, $project_id));

    /** @var \Drupal\platformsh_project\Entity\Metric $metric */
    $metric = Drupal::entityTypeManager()
      ->getStorage($entity_type_id)
      ->create($field_values);
    $metric->save();
    $bundle_label = $metric->bundle->entity->label();
    if ($metric->id()) {
      $this->logger()->success(sprintf("Created a metric %s", $bundle_label));
      $this->logger()->info(print_r($metric->get('target')->getValue(), TRUE));
    }
    else {
      $this->logger()
        ->error(sprintf("Failed to create a metric %s", $bundle_label));
    }

  }

  /**
   * @command platformsh:reset-fields
   * @aliases psh:reset-fields
   * @bootstrap full
   * @usage platformsh:reset-fields
   *   Reset the fields attached to metric entites to factory settings.
   *   Apply schema updates to the fields if they already exist.
   */
  public function resetFields() {
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
  public function deleteMetrics($options = []) {
    platformsh_project_delete_all_metrics();
  }

}
