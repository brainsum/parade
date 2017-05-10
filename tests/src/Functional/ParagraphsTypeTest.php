<?php

namespace Drupal\Tests\parade\Functional;

use Drupal\Component\Render\FormattableMarkup;
use Drupal\Tests\BrowserTestBase;

/**
 * Tests that the Paragraphs types exists.
 *
 * @todo: Add tests for 'Form display'.
 * @todo: Include view and form descriptions (e.g Default vs Custom render for text boxes).
 * @todo: Add stricter array comparison/better 'get text from dom'.
 *
 * @group parade
 */
class ParagraphsTypeTest extends BrowserTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = ['parade', 'field_ui'];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    // Create a user with permissions to manage the paragraphs types.
    $permissions = array(
      'administer paragraphs types',
      'administer site configuration',
      'administer paragraph fields',
      'administer paragraph display',
      'administer paragraph form display',
      'administer site configuration',
    );
    $account = $this->drupalCreateUser($permissions);
    $this->drupalLogin($account);
  }

  /**
   * Return the 'Manage fields' table as an array.
   *
   * Does not include the header.
   *
   * @param string $tableXPath
   *   The xpath of the table.
   *
   * @return array
   *   The table (without header) as array.
   */
  private function getFieldsTableBodyAsArray($tableXPath) {
    $headers = $this->xpath($tableXPath . '//thead//th');
    $columnToSkip = -1;

    foreach ($headers as $delta => $header) {
      if (
        strpos(strtolower($header->getText()), 'operations') !== FALSE
      ) {
        $columnToSkip = $delta;
      }
    }

    $rowsXPath = $tableXPath . '//tbody//tr';

    /** @var \Behat\Mink\Element\NodeElement[] $fieldMachineNames */
    $rows = $this->xpath($rowsXPath);
    $tableData = [];
    foreach ($rows as $row) {
      /** @var \Behat\Mink\Element\NodeElement[] $columns */
      $columns = $row->findAll('css', 'td');

      $columnData = [];
      foreach ($columns as $delta => $column) {
        if ($delta === $columnToSkip) {
          continue;
        }
        $columnData[] = $column->getText();
      }

      $tableData[] = $columnData;
    }

    return $tableData;
  }

  /**
   * Return the 'Manage display' table as an array.
   *
   * Does not include the header.
   *
   * @param string $tableXPath
   *   The xpath of the table.
   *
   * @return array
   *   The table (without header) as array.
   */
  private function getViewsTableAsArray($tableXPath) {
    $headers = $this->xpath($tableXPath . '//thead//th');
    $columnsToSkip = [];

    $currentDelta = 0;
    foreach ($headers as $delta => $header) {
      $headerText = strtolower($header->getText());
      if (
        $header->hasClass('tabledrag-hide')
        || strpos($headerText, 'weight') !== FALSE
        || strpos($headerText, 'parent') !== FALSE
        || strpos($headerText, 'region') !== FALSE
      ) {
        $columnsToSkip[] = $currentDelta;
      }

      if ($header->hasAttribute('colspan')) {
        $colspan = ((int) $header->getAttribute('colspan')) - 1;
        ++$currentDelta;
        for ($i = 0; $i < $colspan; ++$i) {
          $columnsToSkip[] = $currentDelta;
          ++$currentDelta;
        }
        --$currentDelta;
      }

      ++$currentDelta;
    }

    $rowsXPath = $tableXPath . '//tbody//tr';

    /** @var \Behat\Mink\Element\NodeElement[] $fieldMachineNames */
    $rows = $this->xpath($rowsXPath);
    $tableData = [];
    foreach ($rows as $row) {
      if (
        $row->hasClass('region-populated')
        || $row->hasClass('region-hidden-title')
      ) {
        continue;
      }


      /** @var \Behat\Mink\Element\NodeElement[] $columns */
      $columns = $row->findAll('css', 'td');

      $columnData = [];
      foreach ($columns as $delta => $column) {
        if (in_array($delta, $columnsToSkip, FALSE)) {
          continue;
        }
        $columnData[] = $column->getText();
      }

      $tableData[] = $columnData;
    }

    return $tableData;
  }

  /**
   * Assert if two arrays are equal or not.
   *
   * @param array $expectedArray
   *   The expected array.
   * @param array $actualArray
   *   The actual array.
   */
  private function assertArraysAreEqual(array $expectedArray, array $actualArray) {
    self::assertCount(count($expectedArray), $actualArray, "Arrays don't have the same amount of values.");

    foreach ($expectedArray as $rInd => $row) {
      foreach ($row as $cInd => $column) {
        $rowOk = (
          $column === $actualArray[$rInd][$cInd]
          || strpos($actualArray[$rInd][$cInd], $column) !== FALSE
        );

        if ($rowOk) {
          continue;
        }

        self::fail(new FormattableMarkup('The value "@val" does not exist.', ['@val' => $column]));
      }
    }
  }

  /**
   * Tests that the Paragraphs types exist.
   */
  public function testParagraphTypesPage() {
    $this->drupalGet('admin/structure/paragraphs_type');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains('Paragraphs types');

    $actualData = $this->getFieldsTableBodyAsArray('//table');
    $expectedData = [
      ['Header', 'header'],
      ['Images', 'images'],
      ['Locations', 'locations'],
      ['Parallax', 'parallax'],
      ['Simple', 'simple'],
      ['Social links', 'social_links'],
      ['Text & image', 'image_text'],
      ['Text box', 'text_box'],
      ['Text boxes', 'text_boxes'],
    ];

    $this->assertArraysAreEqual($expectedData, $actualData);
  }

  /**
   * Generic 'Check type' function.
   *
   * @param string $type
   * @param array $expectedFields
   * @param array $expectedViews
   * @param array $expectedForms
   */
  private function checkParagraphTypeConfig($type, array $expectedFields, array $expectedViews, array $expectedForms) {
    $this->drupalGet('admin/structure/paragraphs_type/' . $type . '/fields');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains('Manage fields');

    $actualFields = $this->getFieldsTableBodyAsArray('//table');
    $this->assertArraysAreEqual($expectedFields, $actualFields);

    foreach ($expectedViews as $viewMode => $expectedView) {
      $mode = ($viewMode === 'default') ? '' : ('/' . $viewMode);

      $this->drupalGet('admin/structure/paragraphs_type/' . $type . '/display' . $mode);
      $this->assertSession()->statusCodeEquals(200);
      $this->assertSession()->pageTextContains('Manage display');

      $actualView = $this->getViewsTableAsArray('//table');
      $this->assertArraysAreEqual($expectedView, $actualView);
    }

//    foreach ($expectedForms as $formMode => $expectedForm) {
//      // @todo
//    }
  }

  /**
   * Check config for 'Header'.
   */
  private function checkHeaderParagraphTypeConfig() {
    $expectedFields = [
      ['Anchor', 'parade_anchor', 'Text (plain)'],
      ['Background', 'parade_background', 'File'],
      ['Call to action', 'parade_call_to_action', 'Link'],
      ['Color scheme', 'parade_color_scheme', 'Entity reference'],
      ['Enable parallax', 'parade_enable_parallax', 'Boolean'],
      ['Lead text', 'parade_lead_text', 'Text (plain, long)'],
      ['Secondary title', 'parade_secondary_title', 'Text (plain)'],
      ['Title', 'parade_title', 'Text (plain)'],
    ];

    $expectedViews = [
      'default' => [
        ['Title', '- Hidden -', 'Plain text'],
        ['Secondary title', '- Hidden -', 'Plain text'],
        ['Lead text', '- Hidden -', 'Plain text'],
        ['Call to action', '- Hidden -', 'Link'],
        ['Anchor', 'Above', 'Plain text'],
        ['Background', 'Above', 'URL to file'],
        ['Color scheme', 'Above', 'Label'],
        ['Enable parallax', 'Above', 'Boolean'],
      ],
//    preview
//    $expected_fields = ['Title', 'Disabled', /*'Authored by', 'Authored on',*/ 'Anchor', 'Background', 'Call to action', 'Color scheme', 'Enable parallax', 'Lead text', 'Secondary title'];
//    $expected_labels = ['Inline', /*'Above', 'Above',*/ 'Above', 'Above', 'Above', 'Above', 'Above', 'Above', 'Above'];
//    $expected_formats = ['Plain text', /*'- Hidden -', '- Hidden -',*/ '- Hidden -', '- Hidden -', '- Hidden -', '- Hidden -', '- Hidden -', '- Hidden -', '- Hidden -'];
    ];

    $expectedForms = [];

    $this->checkParagraphTypeConfig('header', $expectedFields, $expectedViews, $expectedForms);
  }

  /**
   * Check config for 'Images'.
   */
  private function checkImagesParagraphTypeConfig() {
    $expectedFields = [
      ['Anchor', 'parade_anchor', 'Text (plain)'],
      ['Color scheme', 'parade_color_scheme', 'Entity reference'],
      ['Images', 'parade_images', 'Image'],
      ['Title', 'parade_title', 'Text (plain)'],
    ];

    $expectedViews = [
      'default' => [
        ['Title', '- Hidden -', 'Plain text'],
        ['Images', '- Hidden -', 'Image'],
        ['Anchor', 'Above', 'Plain text'],
        ['Color scheme', 'Above', 'Label'],
      ],
      'preview' => [
        ['Title', 'Inline', 'Plain text'],
        ['Anchor', 'Above', 'Plain text'],
        ['Color scheme', 'Above', 'Label'],
        ['Images', 'Above', 'Image'],
      ],
    ];

    $expectedForms = [];

    $this->checkParagraphTypeConfig('images', $expectedFields, $expectedViews, $expectedForms);
  }

  /**
   * Check config for 'Locations'.
   */
  private function checkLocationsParagraphTypeConfig() {
    $expectedFields = [
      ['Anchor', 'parade_anchor', 'Text (plain)'],
      ['Color scheme', 'parade_color_scheme', 'Entity reference'],
      ['Geofield', 'parade_geofield', 'Geofield'],
      ['Map markers', 'parade_location', 'Text (plain)'],
      ['Text', 'parade_text', 'Text (formatted, long)'],
      ['Title', 'parade_title', 'Text (plain)'],
    ];

    $expectedViews = [
      'default' => [
        ['Title', '- Hidden -', 'Plain text'],
        ['Geofield', '- Hidden -', 'Leaflet aggregated map'],
        ['Text', '- Hidden -', 'Default'],
        ['Anchor', 'Above', 'Plain text'],
        ['Color scheme', 'Above', 'Label'],
        ['Map markers', 'Above', 'Plain text'],
      ],
      'preview' => [
        ['Title', 'Inline', 'Plain text'],
        ['Anchor', 'Inline', 'Plain text'],
        ['Color scheme', 'Above', 'Label'],
        ['Geofield', 'Above', 'Leaflet aggregated map'],
        ['Map markers', 'Above', 'Plain text'],
        ['Text', 'Above', 'Default'],
      ],
    ];

    $expectedForms = [];

    $this->checkParagraphTypeConfig('locations', $expectedFields, $expectedViews, $expectedForms);
  }

  /**
   * Check config for 'Parallax'.
   */
  private function checkParallaxParagraphTypeConfig() {
    $expectedFields = [
      ['Anchor', 'parade_anchor', 'Text (plain)'],
      ['Background', 'parade_background', 'File'],
      ['Background', 'parade_image', 'Image'],
      ['Color scheme', 'parade_color_scheme', 'Entity reference'],
      ['Enable parallax', 'parade_enable_parallax', 'Boolean'],
      ['Minimum height', 'parade_minimum_height', 'Number (integer)'],
      ['Text', 'parade_text', 'Text (formatted, long)'],
    ];

    $expectedViews = [
      'default' => [
        ['Text', '- Hidden -', 'Default'],
        ['Anchor', 'Above', 'Plain text'],
        ['Background', 'Above', 'URL to file'],
        ['Color scheme', 'Above', 'Label'],
        ['Enable parallax', 'Above', 'Boolean'],
        ['Background', 'Above', 'URL to image'],
        ['Minimum height', 'Above', 'Unformatted'],
      ],
    ];

    $expectedForms = [];

    $this->checkParagraphTypeConfig('parallax', $expectedFields, $expectedViews, $expectedForms);
  }

  /**
   * Check config for 'Simple'.
   */
  private function checkSimpleParagraphTypeConfig() {
    $expectedFields = [
      ['Anchor', 'parade_anchor', 'Text (plain)'],
      ['Color scheme', 'parade_color_scheme', 'Entity reference'],
      ['Content', 'parade_text', 'Text (formatted, long)'],
      ['Layout', 'parade_layout', 'Entity reference'],
      ['Title', 'parade_title', 'Text (plain)'],
      ['View mode', 'parade_view_mode', 'View Mode Selector'],
    ];

    $expectedViews = [
      'default' => [
        ['Title', '- Hidden -', 'Plain text'],
        ['Content', '- Hidden -', 'Default'],
        ['Anchor', 'Above', 'Plain text'],
        ['Color scheme', 'Above', 'Label'],
        ['Layout', 'Above', 'Label'],
        ['View mode', 'Above', 'Selected view mode name as text'],
      ],
      'inverse' => [
        ['Content', '- Hidden -', 'Default'],
        ['Title', '- Hidden -', 'Plain text'],
        ['Anchor', 'Above', 'Plain text'],
        ['Color scheme', 'Above', 'Label'],
        ['Layout', 'Above', 'Label'],
        ['View mode', 'Above', 'Selected view mode name as text'],
      ],
      'preview' => [
        ['Title', 'Inline', 'Plain text'],
        ['Anchor', 'Above', 'Plain text'],
        ['Color scheme', 'Above', 'Label'],
        ['Layout', 'Above', 'Label'],
        ['Content', 'Above', 'Default'],
        ['View mode', 'Above', 'Selected view mode name as text'],
      ],
    ];

    $expectedForms = [];

    $this->checkParagraphTypeConfig('simple', $expectedFields, $expectedViews, $expectedForms);
  }

  /**
   * Check config for 'SocialLinks'.
   */
  private function checkSocialLinksParagraphTypeConfig() {
    $expectedFields = [
      ['Color scheme', 'parade_color_scheme', 'Entity reference'],
      ['Social links', 'parade_social_link', 'Link'],
      ['Title', 'parade_title', 'Text (plain)'],
    ];

    $expectedViews = [
      'default' => [
        ['Title', '- Hidden -', 'Plain text'],
        ['Social links', '- Visually Hidden -', 'Link'],
        ['Color scheme', 'Above', 'Label'],
      ],
    ];

    $expectedForms = [];

    $this->checkParagraphTypeConfig('social_links', $expectedFields, $expectedViews, $expectedForms);
  }

  /**
   * Check config for 'Text & Image'.
   */
  private function checkTextAndImageParagraphTypeConfig() {
    $expectedFields = [
      ['Anchor', 'parade_anchor', 'Text (plain)'],
      ['Call to action', 'parade_call_to_action', 'Link'],
      ['Color scheme', 'parade_color_scheme', 'Entity reference'],
      ['Image', 'parade_image', 'Image'],
      ['Layout', 'parade_layout', 'Entity reference'],
      ['Text', 'parade_text', 'Text (formatted, long)'],
      ['Title', 'parade_title', 'Text (plain)'],
      ['View mode', 'parade_view_mode', 'View Mode Selector'],
    ];

    $expectedViews = [
      'default' => [
        ['Title', '- Hidden -', 'Plain text'],
        ['Image', '- Hidden -', 'Image'],
        ['Text', '- Hidden -', 'Default'],
        ['Call to action', '- Hidden -', 'Link'],
        ['Anchor', 'Above', 'Plain text'],
        ['Color scheme', 'Above', 'Label'],
        ['Layout', 'Above', 'Label'],
        ['View mode', 'Above', 'Selected view mode name as text'],
      ],
      'inverse' => [
        ['Title', '- Hidden -', 'Plain text'],
        ['Text', '- Hidden -', 'Default'],
        ['Image', '- Hidden -', 'Image'],
        ['Call to action', '- Hidden -', 'Link'],
        ['Anchor', 'Above', 'Plain text'],
        ['Color scheme', 'Above', 'Label'],
        ['Layout', 'Above', 'Label'],
        ['View mode', 'Above', 'Selected view mode name as text'],
      ],
      'preview' => [
        ['Title', 'Inline', 'Plain text'],
        ['Anchor', 'Above', 'Plain text'],
        ['Call to action', 'Above', 'Call to action'],
        ['Color scheme', 'Above', 'Label'],
        ['Image', 'Above', 'URL to image'],
        ['Layout', 'Above', 'Label'],
        ['Text', 'Above', 'Default'],
        ['View mode', 'Above', 'Selected view mode name as text'],
      ],
    ];

    $expectedForms = [];

    $this->checkParagraphTypeConfig('image_text', $expectedFields, $expectedViews, $expectedForms);
  }

  /**
   * Check config for 'Text Box'.
   */
  private function checkTextBoxParagraphTypeConfig() {
    $expectedFields = [
      ['Color scheme', 'parade_color_scheme', 'Entity reference'],
      ['Image', 'parade_image', 'Image'],
      ['Layout', 'parade_layout', 'Entity reference'],
      ['Text', 'parade_text', 'Text (formatted, long)'],
      ['Title', 'parade_title', 'Text (plain)'],
    ];

    $expectedViews = [
      'default' => [
        ['Title', '- Hidden -', 'Plain text'],
        ['Text', '- Hidden -', 'Default'],
        ['Image', '- Hidden -', 'Image'],
        ['Color scheme', 'Above', 'Label'],
        ['Layout', 'Above', 'Label'],
      ],
      'custom' => [
        ['Image', '- Hidden -', 'Image'],
        ['Title', '- Hidden -', 'Plain text'],
        ['Text', '- Hidden -', 'Default'],
        ['Color scheme', 'Above', 'Label'],
        ['Layout', 'Above', 'Label'],
      ],
      'preview' => [
        ['Title', 'Inline', 'Plain text'],
        ['Color scheme', 'Above', 'Label'],
        ['Image', 'Above', 'URL to image'],
        ['Layout', 'Above', 'Label'],
        ['Text', 'Above', 'Default'],
      ],
    ];

    $expectedForms = [];

    $this->checkParagraphTypeConfig('text_box', $expectedFields, $expectedViews, $expectedForms);
  }

  /**
   * Check config for 'Text Boxes'.
   */
  private function checkTextBoxesParagraphTypeConfig() {
    $expectedFields = [
      ['Anchor', 'parade_anchor', 'Text (plain)'],
      ['Boxes', 'parade_paragraphs', 'Entity reference revisions'],
      ['Boxes per row', 'parade_boxes_per_row', 'Number (integer)'],
      ['Color scheme', 'parade_color_scheme', 'Entity reference'],
      ['Layout', 'parade_layout', 'Entity reference'],
      ['Title', 'parade_title', 'Text (plain)'],
      ['View mode', 'parade_view_mode', 'View Mode Selector'],
    ];

    $expectedViews = [
      'default' => [
        ['Title', '- Hidden -', 'Plain text'],
        ['Boxes', '- Hidden -', 'Rendered entity'],
        ['Anchor', 'Above', 'Plain text'],
        ['Boxes per row', 'Above', 'Unformatted'],
        ['Color scheme', 'Above', 'Label'],
        ['Layout', 'Above', 'Label'],
        ['View mode', 'Above', 'Selected view mode name as text'],
      ],
      'custom' => [
        ['Title', '- Hidden -', 'Plain text'],
        ['Boxes', '- Hidden -', 'Rendered entity'],
        ['Anchor', 'Above', 'Plain text'],
        ['Boxes per row', 'Above', 'Unformatted'],
        ['Color scheme', 'Above', 'Label'],
        ['Layout', 'Above', 'Label'],
        ['View mode', 'Above', 'Selected view mode name as text'],
      ],
      'preview' => [
        ['Title', 'Inline', 'Plain text'],
        ['Anchor', 'Above', 'Plain text'],
        ['Boxes per row', 'Above', 'Unformatted'],
        ['Color scheme', 'Above', 'Label'],
        ['Layout', 'Above', 'Label'],
        ['Boxes', 'Above', 'Rendered entity'],
        ['View mode', 'Above', 'Selected view mode name as text'],
      ],
    ];

    $expectedForms = [];

    $this->checkParagraphTypeConfig('text_boxes', $expectedFields, $expectedViews, $expectedForms);
  }

  /**
   * Tests that the Paragraphs types are configured correctly.
   */
  public function testParagraphTypesConfiguration() {
    $this->checkHeaderParagraphTypeConfig();
    $this->checkImagesParagraphTypeConfig();
    $this->checkLocationsParagraphTypeConfig();
    $this->checkParallaxParagraphTypeConfig();
    $this->checkSimpleParagraphTypeConfig();
    $this->checkSocialLinksParagraphTypeConfig();
    $this->checkTextAndImageParagraphTypeConfig();
    $this->checkTextBoxParagraphTypeConfig();
    $this->checkTextBoxesParagraphTypeConfig();
  }

}
