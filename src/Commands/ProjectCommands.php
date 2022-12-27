<?php
namespace Drupal\platformsh_project\Commands;

use Drush\Attributes\Argument;
use Drush\Commands\DrushCommands;


/**
 * Drush command file.
 */
class ProjectCommands extends DrushCommands {

  /**
   * Create a project from ProjectID.
   *
   * @command platformsh:create-project
   * @param $project_id Argument Project ID to look up
   * @aliases psh:create-project
   */
  public function createProject($project_id = 'abcdefg', $options = []) {
    $field_values = [
      'type' => 'project',
      'title' => $project_id,
      'field_id' => $project_id
    ];
    $entity_type_id = 'node';
    $project = \Drupal::entityTypeManager()
      ->getStorage($entity_type_id)
      ->create($field_values);
    // hook_entity_presave() will do a lookup to prepopulate new project info.
    $project->save();
    $this->output()->writeln(sprintf("Created a project %s", $project->getTitle()));
  }

}
