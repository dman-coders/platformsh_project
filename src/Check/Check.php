<?php

namespace Drupal\platformsh_project\Check;

use Psr\Log\LoggerInterface;

/**
 * Defines a generic Check interface.
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

  const NAME = "Check";

  const DESCRIPTION = "This is a check";

  const OK = 0;

  const ERROR = 1;

  const NOTICE = 2;

  const NA = 3;

  /**
   * Get the name of this check.
   *
   * @return string
   *   The check name.
   */
  public function getName(): string {
    return static::NAME;
  }

  /**
   * Get a logger instance.
   *
   * @param \Psr\Log\LoggerInterface|null $logger
   *   An optional logger instance.
   *
   * @return \Psr\Log\LoggerInterface
   *   A logger instance.
   */
  public static function getLogger(?LoggerInterface $logger): LoggerInterface {
    return $logger ?? \Drupal::logger('platformsh_project.checks');
  }

  /**
   * Execute the check.
   *
   * @param array $args
   *   The arguments for the check.
   * @param string|object $result
   *   Reference to the result data.
   * @param \Psr\Log\LoggerInterface|null $logger
   *   Reference to a logger interface.
   *
   * @return int
   *   The status code (OK, ERROR, NOTICE, or NA).
   */
  abstract public static function execute(array $args, string|object &$result, ?LoggerInterface &$logger = NULL): int;

  /**
   * Execute the check and return JSON result.
   *
   * @param array $args
   *   The arguments for the check.
   * @param int|null $status
   *   Reference to store the status code.
   *
   * @return string
   *   The JSON-encoded result.
   */
  public static function executeAsJson($args, &$status = NULL): string {
    $rawResult = '';
    $status = self::execute($args, $rawResult);
    $structResult = [
      'check' => static::NAME,
      'args' => $args,
      'result' => $rawResult,
    ];
    return json_encode($structResult);
  }

}
