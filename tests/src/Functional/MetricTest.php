<?php
namespace Drupal\Tests\platformsh_project\Functional;

use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\Tests\BrowserTestBase;
use Drupal\platformsh_project\Entity\Metric;

/**
 * Tests the Metric entity.
 *
 * @group platformsh_project
 */
class MetricTest extends BrowserTestBase {

  /**
   * The user to use during the test.
   *
   * @var \Drupal\user\Entity\User
   */
  protected $user;
  protected static $modules = [
    'platformsh_project',
  ];
  protected $profile = 'minimal';
  protected $defaultTheme = 'stark';

  /**
   * Tests creating and saving a Metric entity.
   */
  public function testMetric() {
    // Create a new Metric entity.
    $metric = Metric::create([
      'type' => 'foo',
      'data' => 'bar',
      'timestamp' => strtotime('2022-03-01'),
      'target' => NULL,
    ]);

    // Save the entity.
    $metric->save();

    // Load the entity from the database.
    $loaded_metric = Metric::load($metric->id());

    // Assert that the loaded entity has the same properties as the original.
    $this->assertEquals('foo', $loaded_metric->get('type')->value);
    $this->assertEquals('bar', $loaded_metric->get('data')->value);
    $timestamp = $loaded_metric->get('timestamp')->value;
    $this->assertEquals('2022-03-01', date('Y-m-d', $timestamp));

  }

  /**
   * Tests UI buttons and routes around metric management
   *
   */
  public function testMetricUI() {

  }

  }
