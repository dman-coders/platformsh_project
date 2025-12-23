<?php

namespace Drupal\Tests\platformsh_project\Functional;

use Drupal\platformsh_project\Entity\Metric;
use Drupal\platformsh_project\Entity\Bundle\NoteMetric;
use Drupal\Tests\BrowserTestBase;

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

  /**
   * Modules to enable.
   *
   * @var array
   */
  protected static $modules = [
    'platformsh_project',
  ];

  /**
   * The installation profile to use.
   *
   * @var string
   */
  protected $profile = 'minimal';

  /**
   * The default theme to use.
   *
   * @var string
   */
  protected $defaultTheme = 'stark';

  /**
   * Tests creating and saving a Metric entity.
   */
  public function testMetric() {
    // Create a new Metric entity.
    $metric = NoteMetric::create([
      'bundle' => 'note',
      'status' => '0',
      'data' => 'bar',
      'timestamp' => strtotime('2022-03-01'),
      'target' => NULL,
    ]);

    // Save the entity.
    $metric->save();

    // Load the entity from the database.
    $loaded_metric = Metric::load($metric->id());

    // Assert that the loaded entity has the same properties as the original.
    $this->assertEquals('metric', $loaded_metric->getEntityTypeId());
    $this->assertEquals('note', $loaded_metric->bundle());
    $this->assertEquals('0', $loaded_metric->get('status')->value);
    $this->assertEquals('bar', $loaded_metric->get('data')->value);
    $timestamp = $loaded_metric->get('timestamp')->value;
    $this->assertEquals('2022-03-01', date('Y-m-d', $timestamp));
  }

  /**
   * Tests UI buttons and routes around metric management.
   *
   * @throws \Behat\Mink\Exception\ElementNotFoundException
   */
  public function testMetricUi() {
    // Rebuild plugin cache to ensure metric types are discovered.
    \Drupal::service('plugin.cache_clearer')->clearCachedDefinitions();
    \Drupal::service('kernel')->invalidateContainer();
    $this->container->get('router.builder')->rebuild();

    // Add a metric using the UI form.
    $web_user = $this->drupalCreateUser(['administer metrics']);
    $this->drupalLogin($web_user);
    $this->user = $web_user;
    $edit = [
      'type' => 'note',
      'data' => 'bar',
      'status' => 1,
      'timestamp' => strtotime('2022-03-01'),
      'target' => NULL,
    ];
    $this->drupalGet('metric/add/' . $edit['type']);
    $page = $this->getSession()->getPage();
    // Assert the page is OK.
    $this->assertSession()->statusCodeEquals(200);

    // Can use just the label as a locator.
    $page->selectFieldOption('Status', $edit['status']);
    $page->fillField('Data', $edit['data']);
    $this->submitForm([], 'Save');

    // Verify that visiting the metric canonic view page shows a
    // "refresh" button in the UI.
  }

}
