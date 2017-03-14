<?php

namespace Drupal\Tests\parade\Functional;

use Drupal\Tests\BrowserTestBase;

/**
 * Tests that the Paragraphs types exists.
 *
 * @group parade
 */
class ParagraphsParadeTest extends BrowserTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = ['parade'];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
  }

  /**
   * Tests that the Paragraphs types exists.
   */
  public function testParagraphTypesPage() {
    // Create a user with permissions to manage.
    $permissions = array(
      'administer paragraphs types',
      'access geocoder autocomplete',
      'administer parade settings',
      'administer site configuration',
    );
    $account = $this->drupalCreateUser($permissions);
    $this->drupalLogin($account);

    $this->drupalGet('admin/structure/paragraphs_type');
    $this->assertSession()->statusCodeEquals(200);

    // Test that there are the Paragraphs types.
    $this->assertSession()->pageTextContains('PARAGRAPHS TYPES');
  }

}
