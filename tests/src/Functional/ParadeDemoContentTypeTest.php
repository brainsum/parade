<?php

namespace Drupal\Tests\parade\Functional;

require_once __DIR__ . '/Includes/ParadeOnepageContentTypeExpectedData.inc';


/**
 * Tests the Parade onepage content type created by parade_demo.
 *
 * @group parade
 */
class ParadeDemoContentTypeTest extends ParadeTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = [
    'parade_demo',
    'field_ui',
    'block',
    'menu_ui',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->drupalPlaceBlock('local_actions_block');

    // Create a user with permissions to manage the Parade onepage content type.
    $permissions = [
      'administer parade settings',
      'administer site configuration',
      'administer content types',
      'administer node fields',
      'administer node display',
      'administer node form display',
      'access content overview',
      'administer nodes',
      'create parade_onepage content',
      'administer blocks',
      'access content',
      'access content overview',
    ];
    $account = $this->drupalCreateUser($permissions);
    $this->drupalLogin($account);
  }

  /**
   * Tests the Parade onepage content type.
   */
  public function testParadeOnepageContentType() {
    $contentType = 'parade_onepage';

    self::drupalGet('admin/structure/types/manage/' . $contentType);
    self::assertSession()->statusCodeEquals(200);
    self::assertSession()->pageTextContains('Edit Parade onepage content type');

    $typeData = get_parade_onepage_content_type_data();
    $this->checkContentTypeConfig($contentType, $typeData['fields'], $typeData['views'], $typeData['forms']);
  }

  /**
   * Checks whether the hook_install() created the default content.
   */
  public function testParadeOnepageDefaultContent() {
    self::drupalGet('admin/content', [
      'query' => [
        'type' => 'parade_onepage',
      ],
    ]);

    self::assertSession()->statusCodeEquals(200);
    self::assertSession()->pageTextContains('Content');
    self::assertSession()->pageTextNotContains('No content available.');
    $contentName = 'Parade One Page Site Demo';
    self::assertSession()->pageTextContains($contentName);

    /** @var \Behat\Mink\Element\NodeElement[] $links */
    $links = self::xpath('//a[text()[contains(., "' . $contentName . '")]]');

    self::assertCount(1, $links);

    $nodePath = '';
    foreach ($links as $link) {
      $nodePath = $link->getAttribute('href');
    }

    self::assertNotEmpty($nodePath);
    self::drupalGet($nodePath);
    self::assertSession()->statusCodeEquals(200);
    self::assertSession()->pageTextContains($contentName);
    self::assertSession()->pageTextContains('Parade');
  }

  /**
   * Generic 'Check type' function.
   *
   * @param string $type
   * @param array $expectedFields
   * @param array $expectedViews
   * @param array $expectedForms
   */
  private function checkContentTypeConfig($type, array $expectedFields, array $expectedViews, array $expectedForms) {
    $this->drupalGet('admin/structure/types/manage/' . $type . '/fields');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains('Manage fields');

    $actualFields = $this->getFieldsTableBodyAsArray('//table');
    $this->assertArraysAreEqual($expectedFields, $actualFields);

    foreach ($expectedViews as $viewMode => $expectedView) {
      $mode = ($viewMode === 'default') ? '' : ('/' . $viewMode);

      $this->drupalGet('admin/structure/types/manage/' . $type . '/display' . $mode);
      $this->assertSession()->statusCodeEquals(200);
      $this->assertSession()->pageTextContains('Manage display');

      $actualView = $this->getViewsTableAsArray('//table');
      $this->assertArraysAreEqual($expectedView, $actualView);
    }

//    foreach ($expectedForms as $formMode => $expectedForm) {
//      // @todo
//    }
  }

}