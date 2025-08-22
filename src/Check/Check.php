<?php

namespace Drupal\platformsh_project\Check;

use Psr\Log\LoggerInterface;

use phpDocumentor\Reflection\Types\Integer;

/**
 * Defines a generic Check interface
 *
 * A check is a very granular part of a metric. A single vector, boolean or
 * value.
 *
 * A check can be executed by giving it the required parameters
 * A check has no state.
 *
 * It returns the results as text, as JSON, or as HTML
 */
abstract class Check {

  const name = "Check";

  const description = "This is a check";

  const OK = 0;

  const ERROR = 1;

  const NOTICE = 2;

  const NA = 3;

  public function getName(): string {
    return static::name;
  }

  public static function getLogger($logger): LoggerInterface {
    return $logger ?? \Drupal::logger('platformsh_project.checks');
  }

  /**
   * @param array $args
   * @param string|object $result reference to the result data.
   * @param LoggerInterface|null $logger reference to a logger interface.
   *
   * @return int The status code (OK, ERROR, NOTICE, or NA)
   */
  abstract public static function execute(array $args, string|object &$result, LoggerInterface &$logger = NULL): int;

  public static function execute_as_json($args, &$status = NULL): string {
    $raw_result = '';
    $status = self::execute($args, $raw_result);
    $struct_result = [
      'check' => static::name,
      'args' => $args,
      'result' => $raw_result,
    ];
    return json_encode($struct_result);
  }

}
