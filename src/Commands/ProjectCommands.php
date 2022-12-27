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
    $project->save();
    $this->output()->writeln("Created a project $project_id");
  }


  /**
   * A custom Drush command to displays the given text.
   *
   * @command platformsh:print-me
   * @param $text Argument with text to be printed
   * @option uppercase Uppercase the text
   * @aliases ccepm,cce-print-me
   */
  public function printMe($text = 'Hello world!', $options = ['uppercase' => FALSE]) {
    if ($options['uppercase']) {
      $text = strtoupper($text);
    }
    $this->output()->writeln($text);
  }
}
