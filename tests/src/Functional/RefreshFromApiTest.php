<?php

namespace Drupal\Tests\platformsh_project\Functional;

use Drupal\Tests\platformsh_api\Functional\PlatformshBrowserTestBase;
use Drupal\user\Entity\User;

/**
 * Default test case for the action_example module.
 *
 * @group platformsh
 */
class RefreshFromApiTest extends PlatformshBrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'platformsh_project'
  ];

  /**
   * The installation profile to use with this test.
   *
   * We need the 'minimal' profile in order to make sure the Tool block is
   * available.
   *
   * @var string
   */
  protected $profile = 'minimal';

  /**
   */
  public function testActionRefreshFromApi() {
    // Create an administrative user.
    $admin_user = $this->drupalCreateUser(['administer actions']);
    $this->drupalLogin($admin_user);

    $this->drupalGet('/admin/config/system/actions');
    $this->assertSession()->pageTextContains('Refresh from API');

  }

}
