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
   * Use the UI to create a project via front-end
   */
  public function testActionCreateFromApi() {
    // Create an administrative user.
    $admin_user = $this->drupalCreateUser([
      'administer nodes',
      'access content overview', # for admin/content page

      'create project content', # to Create project
      'edit own project content',
      'delete own project content',

      'create organization content',
      'edit own organization content',
      'delete own organization content',
    ]);
    $this->drupalLogin($admin_user);

    // Ensure navigating through the admin UI goes as expected.
    $this->drupalGet('/admin/content');
    $this->assertSession()->pageTextContains('Add content');
    $this->clickLink('Add content');
    $this->assertSession()->pageTextContains('Project');
    $this->clickLink('Project');
    $this->assertSession()->addressEquals('/node/add/project');
    // Create a new project via the form
    $page = $this->getSession()->getPage();
    $page->fillField('edit-title-0-value', $this->TestProjectId);
    $page->fillField('edit-field-id-0-value', $this->TestProjectId);
    $page->pressButton('Save');

    /*
    // Try again. Use a different syntax for the form interaction.
    $this->drupalGet('/node/add/project');
    $this->submitForm([
      'title[0][value]' => $this->TestProjectId,
      'field_id[0][value]' => $this->TestProjectId,
    ], 'Save' );
    */

    // Check the new item was created as expected.
    // If the node title was updated correctly,
    // then we must have succeeded in pulling info from the API.
    $this->assertSession()->pageTextContains($this->TestProjectName);

    // Revisit the admin page and see the new node there for auditing.
    $this->drupalGet('/admin/content');
    $this->assertSession()->pageTextContains($this->TestProjectName);

    #$this->assertSession()->pageTextContains('now fail');

  }

  /**
   * Use the UI to run a refresh.
   */
  public function testActionRefreshFromApi() {
    // Create an administrative user.
    $admin_user = $this->drupalCreateUser(['administer actions']);
    $this->drupalLogin($admin_user);

    $this->drupalGet('/admin/config/system/actions');
    $this->assertSession()->pageTextContains('Refresh from API');

  }

}
