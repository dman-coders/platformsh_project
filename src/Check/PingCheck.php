<?php

namespace Drupal\platformsh_project\Check;


use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;

/**
 * Check if an URL responds OK
 */
class PingCheck  {

  /**
   * The check name
   *
   * @var string
   */
  protected $name="PingCheck";

  /**
   * The check description
   *
   * @var string
   */
  protected $description="Does an URL respond without error?";

  /**
   * Execute the check
   *
   * @param array $args
   *   The arguments to the check
   *
   * @return string
   *   The result of the check
   */
  public static function execute($args, &$status): string|object {
    $client = new Client();
    $url = $args['url'];
    $result='';

    try {
      $response = $client->request('GET', $url);
      if ($response->getStatusCode() === 200) {
        $result="<info>Response from $url: OK</info>";
        $status = 0;
      } else {
        $result="<error>Response from $url: Fail</error>";
        $status = 1;
      }
    } catch (RequestException $exception) {
      $result="<error>Could not reach $url: " . $exception->getMessage() . "</error>";
      $status = 1;
    }
    return $result;
  }

}
