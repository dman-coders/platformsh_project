<?php

namespace Drupal\platformsh_project\Check;


/**
 * Defines a generic Check interface
 *
 * A check is a very granular part of a metric. A single vector, boolean or value.
 *
 * A check can be executed by giving it the required parameters
 * A check has no state.
 *
 * It returns the results as text, as JSON, or as HTML
 */
abstract class Check  {

  /**
   * The check name
   *
   * @var string
   */
  protected $name;

  /**
   * The check description
   *
   * @var string
   */
  protected $description;

  abstract public static function execute($args): string|object {
    return "Check $name executed";
  }
  public static function execute_as_json($args):string {
    $raw_result = $this->execute($args);
    $struct_result=[
      'check'=>$this->name,
      'args'=>$args,
      'result'=>$raw_result
    ];
    return json_encode($struct_result);
  }

}
