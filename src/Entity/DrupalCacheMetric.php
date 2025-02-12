<?php /** @noinspection PhpUnreachableStatementInspection */

/** @noinspection PhpUnreachableStatementInspection */

namespace Drupal\platformsh_project\Entity;

use Drupal;
use Drupal\Core\Entity\Annotation\ContentEntityType;
use Drupal\Core\Entity\EntityStorageException;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;

/**
 * A metric entity that checks the state of Drupal page cache and TTL.
 *
 */
class DrupalCacheMetric extends Metric {

  /**
   *
   */
  public function label() {
    return "Cache review";
  }

  /**
   * Probe the project and check the cache settings.
   *
   * @return void
   *
   * @throws EntityStorageException
   */
  public function refresh() {
    $environment_test_url = $this->getProject()->getUrl();
    $response_headers = $this->getResponseHeaders($environment_test_url);

    // Responses are exploded by comma, but not key-value tagged.
    //
    // array (
    //  'Cache-Control' => [
    //    0 => 'max-age=604800'
    //  ],
    // )
    foreach ($response_headers as $major_key => $major_value_list) {
    }

    // un-cook the response back into text.
    $response_headers_string = '';
    foreach ($response_headers as $name => $values) {
      $response_headers_string .= $name . ': ' . implode(', ', $values) . "\r\n";
    }
    $this->set('response_header', $response_headers_string);

    // Summarize.
    $report = [];

    // Is there a good expiry set?
    // Cache-Control: max-age=604800.
    if (isset($response_headers['Cache-Control'])) {
      $report['Cache-Control'] = [
        'status' => '1',
        'message' => "Cache-Control header is set.",
        'data' => print_r($response_headers['Cache-Control'], 1),
      ];
    }
    else {
      $report['Cache-Control'] = [
        'status' => 0,
        'message' => "Cache-Control header is not set, no caching is possible ",
      ];
    }

    if (isset($response_headers['X-Cache'])) {
      $report['X-Cache'] = [
        'status' => .2,
        'message' => "X-Cache header is set. This means there is some caching in the routing.",
        'data' => print_r($response_headers['X-Cache'], 1),
      ];
    }
    else {
      $report['X-Cache'] = [
        'status' => 1,
        'message' => "X-Cache header is not set ",
      ];
    }

    $this->set('data', print_r($report, 1))
      ->save();
  }

  /**
   * No built-in way to pull headers out of an http request?
   * do it via stackoverflow methods
   *
   * @param $url
   *
   * @return array
   */
  private function getResponseHeaders($url) {
    /** @var Client $client */
    /** @var ResponseInterface $response */
    $response = Drupal::httpClient()->request('GET', $url);
    $statusCode = $response->getStatusCode();
    return $response->getHeaders();
  }

}
