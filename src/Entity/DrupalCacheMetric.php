<?php /** @noinspection PhpUnreachableStatementInspection */

/** @noinspection PhpUnreachableStatementInspection */

namespace Drupal\platformsh_project\Entity;

use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;

/**
 * A metric entity that checks the state of Drupal page cache and TTL.
 *
 * Every metric bundle type also needs to be published at
 * `config/install/platformsh_project.metric_type.{id}.yml`
 * and referred to by
 * platformsh_project_entity_bundle_info_alter.
 *
 * @inheritDoc
 *
 * Inheriting annotations doesn't seem to work well.
 * The canonical link ended up unavailable on subclasses.
 * Do I have to replicate all the same annotation here
 * to make sure the entity bundle metadata is available to the system?
 *
 * @ContentEntityType(
 *   id = "drupalcache",
 *   description = @Translation("Reviews the Drupal cache settings"),
 *   label = @Translation("Drupal Cache"),
 *   links = {
 *     "canonical" = "/metric/{metric}",
 *   },
 *   base_table = "metric",
 *   entity_keys = {
 *     "id" = "id",
 *     "bundle" = "bundle"
 *   },
 * )
 */
class DrupalCacheMetric extends Metric {

  /**
   *
   */
  public function label() {
    return "Cache review";
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    // Base fields are attached directly to the main entity table
    // as additional columns, like a traditional db schema
    // Base fields are not referred to as `field_data` style lookups
    // like most other UI-added fields would do.
    $fields = parent::baseFieldDefinitions($entity_type);
    #return $fields;

    // The data field.
    $fields['response_header'] = BaseFieldDefinition::create('string_long')
      ->setLabel(t('Header Response'))
      ->setDescription(t('HTTP server response'))
      ->setRequired(FALSE)
      ->setDisplayOptions('form', [
        'type' => 'text_textarea',
        'weight' => 10,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setDisplayOptions('view', ['weight' => 0]);
    return $fields;
  }

  /**
   * Probe the project and check the cache settings.
   *
   * @return void
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
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
    /** @var \GuzzleHttp\Client $client */
    /** @var \Psr\Http\Message\ResponseInterface $response */
    $response = \Drupal::httpClient()->request('GET', $url);
    $statusCode = $response->getStatusCode();
    return $response->getHeaders();
  }

}
