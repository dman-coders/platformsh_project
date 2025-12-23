<?php

namespace Drupal\platformsh_project\Check;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Check if an URL responds OK.
 */
class PingCheck extends Check {

  const NAME = "PingCheck";

  const DESCRIPTION = "Does an URL respond without error?";

  const EXPECTED_ARGUMENTS = ['url'];

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

    $client = new Client();
    $url = $args['url'];
    // Allow URL objects.
    if (! is_string($url)) {
      $url = $url->toString();
    }
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
    return $status;
  }

}
