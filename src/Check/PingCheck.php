<?php

namespace Drupal\platformsh_project\Check;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Check if an URL responds OK
 */
class PingCheck extends Check {

  const name = "PingCheck";

  const description = "Does an URL respond without error?";

  const expected_arguments = ['url'];

  /**
   * Execute the check
   *
   * @param array $args
   *   The arguments to the check
   *
   * @return string
   *   The result of the check
   */
  public static function execute(array $args, int &$status = NULL, LoggerInterface &$logger = NULL): string|object {
    $logger = $logger ?? new NullLogger();

    $client = new Client();
    $url = $args['url'];
    $result = '';
    $logger->info("Requesting $url");

    try {
      $response = $client->request('GET', $url);
      $result = $response->getStatusCode();
      if ($result === 200) {
        $logger->info("Response from $url: OK");
        $status = static::OK;
      }
      else {
        $logger->info("Response from $url: Fail $result");
        $status = static::ERROR;
      }
    }
    catch (RequestException $exception) {
      $result = "Error requesting $url: " . $exception->getMessage() . "";
      $status = static::ERROR;
    }
    return $result;
  }

}
