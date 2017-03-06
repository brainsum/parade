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
  public static $modules = ['parade_demo'];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

//    $this->installSchema('geocoder');
//     \Drupal::moduleHandler()->loadInclude('geocoder', 'install');

    // Create paragraphs and article content types.
    /*$values = ['type' => 'article', 'name' => 'Article'];
    $node_type = NodeType::create($values);
    $node_type->save();
    $this->installEntitySchema('user');
    $this->installEntitySchema('node');
    $this->installEntitySchema('paragraph');
    $this->installSchema('system', ['sequences']);
    $this->installSchema('node', ['node_access']);
    \Drupal::moduleHandler()->loadInclude('paragraphs', 'install');*/
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
