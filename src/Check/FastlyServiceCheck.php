<?php

namespace Drupal\platformsh_project\Check;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Check if there is a fastly service for this project.
 */
class FastlyServiceCheck extends Check {

  const name = "FastlyServiceCheck";
  const description = "Does the project have a Fastly service?";
  const expected_arguments = ['PLATFORM_PROJECT', 'PLATFORM_CLI'];

  /**
   * Execute the check.
   *
   * @param array $args
   *   The arguments to the check
   *
   * @return int`
   *   The status result of the check. Further info can be retained in the
   *   $result.
   */
  public static function execute(array $args, string|object &$result, LoggerInterface &$logger = NULL): int {
    $logger = self::getLogger($logger);
    if (empty($args['PLATFORM_CLI'])) {
      $args['PLATFORM_CLI'] = 'platform';
    }
    $result = '';
    $module_dir = \Drupal::service('extension.list.module')
      ->getPath('platformsh_project');
    $shell_audits_directory = $module_dir . '/bin/audits';

    $command_file = '11_check_named_fastly_service_exists';

    // Each command may have different required parameters.
    // Feed these in as named environment variables.
    $logger->info('Running {command_file}', ['command_file' => $command_file]);
    $env_vars = '';
    foreach ($args as $var_name => $var_value) {
      $env_vars .= $var_name . '=' . escapeshellarg($var_value) . ' ';
    }
    $command = "$env_vars  $shell_audits_directory/$command_file";
    $command .= ' 2>&1';  // Redirect STDERR to STDOUT
    // Need to run this in a bash shell to get the env vars set properly.
    // Also need to escape the whole command.
    $command = "bash -c '" . escapeshellcmd($command) . "'";
    $logger->info('Running {command}', ['command' => $command]);
    try {
      // Run the command, capturing STDOUT, STDERR and return code.
      $output = [];
      $return_var = 0;
      exec($command, $output, $return_var);
      $logger->debug(print_r($output, 1));
      $result = implode("\n", $output);
      $logger->debug("Command: {command}\n output: {result}", ['command' => $command, 'result' => $result]);
      if ($return_var === 0) {
        $logger->info('Command {command} executed successfully', ['command' => $command]);
        $status = static::OK;
      }
      else {
        $message = "Command '{command}' failed with return code {return_code}\n{result}";
        $context = [
          'command' => $command,
          'return_code' => $return_var,
          'result' => $result,
        ];
        $logger->error($message, $context);        $status = static::ERROR;
      }

    }
    catch (RequestException $exception) {
      $logger->error('Error executing {command}: {error}', [
        'command' => $command,
        'error' => $exception->getMessage()
      ]);

      $result = "Error executing '$command' : " . $exception->getMessage() . "";
      $status = static::ERROR;
    }
    return $status;
  }

}
