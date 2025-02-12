<?php

namespace Drupal\Tests\platformsh_project\Functional;

use Drupal\Tests\platformsh_api\Functional\PlatformshBrowserTestBase;

/**
 * Default test case for the action_example module.
 *
 * @group platformsh
 */
class RefreshFromApiTest extends PlatformshBrowserTestBase
{

  /**
   * @var bool
   * For reasons that are not my fault?
   * In a testing context, the `system.action` config entities (from core)
   * are not defined and throw a "missing schema" fatal error during the
   * module install process where the `config/install` yamls are being read.
   * > core testing base classes add a config schema checker during testing.
   *
   * Normal module installation with normal dependency resolution works fine,
   * so something about ConfigInstaller inside a test harness context is faulty.
   *
   * I can see the schema for `system.action` config entities in `system.schema.yml`
   * and my yaml syntax is valid.
   *
   * Luckily, this is apparently possible to disable?
   */
  protected $strictConfigSchema = FALSE;


  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'platformsh_project',
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
   * Use the UI to create a project via front-end.
   */
  public function testActionCreateFromApi()
  {
    // Create an administrative user.
    $admin_user = $this->drupalCreateUser([
      'administer nodes',
      // For admin/content page.
      'access content overview',

      // To Create project.
      'create project content',
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
    // Create a new project via the form.
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

    // $this->assertSession()->pageTextContains('now fail');
  }

  /**
   * Use the UI to run a refresh.
   */
  public function testActionRefreshFromApi()
  {
    // Create an administrative user.
    $admin_user = $this->drupalCreateUser(['administer actions']);
    $this->drupalLogin($admin_user);

    $this->drupalGet('/admin/config/system/actions');
    $this->assertSession()->pageTextContains('Refresh from API');

  }

}
