<?php

namespace Drupal\platformsh_project\Plugin\MetricType;

use Drupal\platformsh_project\Check\PingCheck;
use Drupal\platformsh_project\Entity\Metric;

/**
 * A metric entity that saves the result of a ping test.
 *
 * @MetricType(
 *   id = "ping",
 *   label = @Translation("Ping"),
 *   description = @Translation("Ping the response from the domain")
 * )
 */
class PingMetric extends Metric {


  /**
   * Refresh this metric.
   */
  public function refresh(): void {
    $url = $this->getProject()->getUrl();
    $status = "pending";
    $response = PingCheck::execute(['url' => $url], $status);
    $this->set('data', $response)
      ->set('note', "pinged $url\n" . date("Y-m-d H:i:s") . "\n" . $response)
      ->set('status', $status)
      ->save();
    parent::refresh();
  }

}
