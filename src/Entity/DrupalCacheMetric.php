<?php

namespace Drupal\platformsh_project\Entity;

/**
 * A metric entity that checks the state of Drupal page cache and TTL.
 */
class DrupalCacheMetric extends Metric {

  /**
   * {@inheritdoc}
   */
  public function label(): string {
    return "Cache review";
  }

  /**
   * Probe the project and check the cache settings.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function refresh(): void {
    $environmentTestUrl = $this->getProject()->getUrl();
    $responseHeaders = $this->getResponseHeaders($environmentTestUrl);

    // Responses are exploded by comma, but not key-value tagged.
    //
    // array (
    // 'Cache-Control' => [
    // 0 => 'max-age=604800'
    // ],
    // )
    foreach ($responseHeaders as $majorKey => $majorValueList) {
      // Process headers as needed.
    }

    // un-cook the response back into text.
    $responseHeadersString = '';
    foreach ($responseHeaders as $name => $values) {
      $responseHeadersString .= $name . ': ' . implode(', ', $values) . "\r\n";
    }
    $this->set('field_response_header', $responseHeadersString);

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
    parent::refresh();
  }

  /**
   * No built-in way to pull headers out of an http request?
   *
   * Do it via stackoverflow methods.
   *
   * @param string $url
   *   The URL to check.
   *
   * @return array
   *   The response headers.
   */
  private function getResponseHeaders($url) {
    /** @var \GuzzleHttp\Client $client */
    /** @var \Psr\Http\Message\ResponseInterface $response */
    $response = \Drupal::httpClient()->request('GET', $url);
    $statusCode = $response->getStatusCode();
    return $response->getHeaders();
  }

}
