<?php

namespace Drupal\platformsh_project\Commands;

use Consolidation\OutputFormatters\StructuredData\RowsOfFields;
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
   * Command description here.
   *
   * @param $arg1
   *   Argument description.
   * @param array $options
   *   An associative array of options whose values come from cli, aliases, config, etc.
   * @option option-name
   *   Description
   * @usage platformsh-commandName foo
   *   Usage description
   *
   * @command platformsh:commandName
   * @aliases foo
   */
  public function commandName($arg1, $options = ['option-name' => 'default']) {
    $this->logger()->success(dt('Achievement unlocked.'));
  }

  /**
   * Create a project from ProjectID.
   *
   * @command platformsh:create-project
   * @param $project_id Argument Project ID to look up
   * @aliases psh:create-project
   * @usage platformsh_project-commandName create-project
   *   Create a project from ProjectID.
   */
  public function createProject($project_id = 'abcdefg', $options = []) {
    $field_values = [
      'type' => 'project',
      'title' => $project_id,
      'field_id' => $project_id
    ];
    $entity_type_id = 'node';
    $this->output()->writeln(sprintf("Creating a project %s", $project_id));
    $project = \Drupal::entityTypeManager()
      ->getStorage($entity_type_id)
      ->create($field_values);
    // hook_entity_presave() will do a lookup to prepopulate new project info.
    $project->save();
    if ($project->id()) {
      $this->output()->writeln(sprintf("Created a project %s", $project->getTitle()));
    } else {
      $this->output()->writeln(sprintf("Failed to create a project %s", $project->getTitle()));
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
      'k5lc5wez3aso2' => "hello world",
      'log5ehrvf6tjg' => "D7"
    ];
    $this->output()->writeln(sprintf("Creating sample projects"));
    foreach ($sample_ids as $project_id => $label) {
      $this->createProject($project_id);
    }
  }

}
