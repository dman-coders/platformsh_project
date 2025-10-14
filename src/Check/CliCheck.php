<?php

namespace Drupal\platformsh_project\Check;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Wrapper to generic CLI checks
 */
class CliCheck extends Check {
  const NAME = "CliCheck";
  const DESCRIPTION = "Executes a CLI check and returns the result";
  const EXPECTED_ARGUMENTS = ['check_name'];

  /**
   * Execute the check.
   *
   * @param array $args
   *   The arguments to the check.
   * @param string|object $result
   *   Reference to the result data.
   * @param \Psr\Log\LoggerInterface|null $logger
   *   Reference to a logger interface.
   *
   * @return int
   *   The status code.
   */
  public static function execute(array $args, string|object &$result, ?LoggerInterface &$logger = NULL): int {
    $logger = $logger ?? new NullLogger();
    $check_name = $args['check_name'] ?? NULL;
    $module_path = \Drupal::service('extension.path.resolver')->getPath('module', 'platformsh_project');
    $cli_dir = $module_path . '/bin/audits';
    $check_file = $cli_dir . '/' . $check_name;
    if (!file_exists($check_file)) {
      $result = "Check file $check_file does not exist in $cli_dir";
      $logger->error($result);
      return static::NA;
    }
    // CLI checks expect to have environment variables set.
    // They list them in EXPECTED_ARGUMENTS.
    // ensure they are populated before running the command
    $env_vars = implode(' ', [
      'PLATFORM_CLI=platform',
      'PLATFORM_PROJECT=' . ($args['PLATFORM_PROJECT'] ?? ''),
      'PLATFORM_ENVIRONMENT=' . ($args['PLATFORM_ENVIRONMENT'] ?? ''),
    ]);

    $cli_command = '$cli_dir/check $check_name';
    // execute the CLI command, capturing the response code and stdout and stderr separately
    $command = "bash -c '$env_vars $cli_command 2>&1' ";
    $logger->info('Running {command}', ['command' => $command]);
    $output = [];
    $return_var = 0;
    exec($command, $output, $return_var);
    $result = implode("\n", $output);
    $logger->info('Command returned {return_var} with output: {output}', ['return_var' => $return_var, 'output' => $result]);
    switch ($return_var) {
      case 0:
        return static::OK;
      case 1:
        return static::ERROR;
      default:
        throw new \Exception('Unexpected value');
    }
  }

}
