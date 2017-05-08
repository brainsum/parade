<?php

namespace Drupal\Tests\parade\Functional;

use Drupal\Tests\BrowserTestBase;

use Drupal\node\Entity\Node;
use Drupal\node\Entity\NodeType;
use Drupal\block\Entity\Block;

/**
 * Tests the Parade onepage content type created by parade_demo.
 *
 * @group parade
 */
class ParadeDemoParadeTest extends BrowserTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = ['parade', 'parade_demo', 'node', 'link', 'field_ui', 'block'];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->drupalPlaceBlock('local_actions_block');

    // Create a user with permissions to manage the Parade onepage content type.
    $permissions = array(
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
    );
    $account = $this->drupalCreateUser($permissions);
    $this->drupalLogin($account);
  }

  private function FieldLabels($expected_labels) {
    $nr = count($expected_labels);
    $field_labels = $this->xpath("//table[@id='field-overview']//td[1]/text()");
    $this->assertEqual(count($field_labels), $nr, format_string('Found @nr field label.', array('@nr' => $nr)));
    for ($i = 0; $i < $nr; $i++) {
      $this->assertEquals($expected_labels[$i], $field_labels[$i]->getHtml());
    }
  }

  private function FieldMachineNames($expected_machine_names) {
    $nr = count($expected_machine_names);
    $field_machine_names = $this->xpath("//table[@id='field-overview']//td[@class='priority-medium']/text()");
    $this->assertEqual(count($field_machine_names), $nr, format_string('Found @nr field machine name.', array('@nr' => $nr)));
    for ($i = 0; $i < $nr; $i++) {
      $this->assertEquals($expected_machine_names[$i], $field_machine_names[$i]->getHtml());
    }
  }

  private function FieldTypes($expected_types) {
    $nr = count($expected_types);
    $field_types = $this->xpath("//table[@id='field-overview']//td/a/text()");
    $this->assertEqual(count($field_types), $nr, format_string('Found @nr field type.', array('@nr' => $nr)));
    for ($i = 0; $i < $nr; $i++) {
      $this->assertEquals($expected_types[$i], $field_types[$i]->getHtml());
    }
  }

  private function DisplayFields($expected_fields) {
    $nr = count($expected_fields);
    $fields = $this->xpath("//table[@id='field-display-overview']//td[position()=1 and not(@colspan='7' and text()!='Disabled')]/text()");
    $this->assertEqual(count($fields), $nr, format_string('Found @nr field.', array('@nr' => $nr)));
    for ($i = 0; $i < $nr; $i++) {
      $this->assertEquals($expected_fields[$i], $fields[$i]->getHtml());
    }
  }

  private function DisplayLabels($expected_labels) {
    $nr = count($expected_labels);
    $labels = $this->xpath("//table[@id='field-display-overview']//td[4]/div/select/option[@selected='selected']/text()");
    $this->assertEqual(count($labels), $nr, format_string('Found @nr label.', array('@nr' => $nr)));
    for ($i = 0; $i < $nr; $i++) {
      $this->assertEquals($expected_labels[$i], $labels[$i]->getHtml());
    }
  }

  private function DisplayFormats($expected_formats) {
    $nr = count($expected_formats);
    $formats = $this->xpath("//table[@id='field-display-overview']//td[5]/div/select/option[@selected='selected']/text()");
    $this->assertEqual(count($formats), $nr, format_string('Found @nr format.', array('@nr' => $nr)));
    for ($i = 0; $i < $nr; $i++) {
      $this->assertEquals($expected_formats[$i], $formats[$i]->getHtml());
    }
  }

  private function DisplayPluginSummarys($expected_plugin_summarys) {
    $nr = count($expected_plugin_summarys);
    $plugin_summarys = $this->xpath("//table[@id='field-display-overview']//td[@class='field-plugin-summary-cell']/div[@class='field-plugin-summary']/text()");
    $this->assertEqual(count($plugin_summarys), $nr, format_string('Found @nr plugin summary.', array('@nr' => $nr)));
    for ($i = 0; $i < $nr; $i++) {
      $this->assertEquals($expected_plugin_summarys[$i], $plugin_summarys[$i]->getHtml());
    }
  }

  private function DisplaySettingsButtonNames($expected_settings_button_names) {
    $nr = count($expected_settings_button_names);
    $settings_button_names = $this->xpath("//table[@id='field-display-overview']//td[7]//div [@class='field-plugin-settings-edit-wrapper']/input/@name");
    $this->assertEqual(count($settings_button_names), $nr, format_string('Found @nr settings button names.', array('@nr' => $nr)));
    for ($i = 0; $i < $nr; $i++) {
      $this->assertEquals($expected_settings_button_names[$i], $settings_button_names[$i]->getHtml());
    }
  }

  /**
   * Tests the Parade onepage content type.
   */
  public function testParadeOnepage() {
    $this->drupalGet('admin/structure/types');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains('Content types');

    $this->assertText('Parade onepage', "The Parade onepage content type exists.");
    $this->assertText('Add content type', "Add content type exists.");
    
    // Tests that the Parade onepage content type is configured corectly - Fields.
    $this->drupalGet('admin/structure/types/manage/parade_onepage/fields');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains('Manage fields');

    $expected_labels = ['Machine name', 'Menu', 'Sections'];
    $expected_machine_names = ['parade_onepage_id', 'parade_onepage_menu', 'parade_onepage_sections'];
    $expected_types = ['Machine name', 'Link', 'Entity reference revisions'];

    $this->FieldLabels($expected_labels);
    $this->FieldMachineNames($expected_machine_names);
    $this->FieldTypes($expected_types);

    // Tests that the Parade onepage content type is configured corectly - Manage display.
    $this->drupalGet('admin/structure/types/manage/parade_onepage/display');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains('Manage display');

    $expected_fields = ['Menu', 'Sections', 'Disabled', 'Machine name', 'Links'];
    $expected_labels = ['- Hidden -', '- Hidden -', 'Above'];
    $expected_formats = ['Link', 'Rendered entity', '- Hidden -', '- Hidden -'];
    $expected_plugin_summarys = ['Link text trimmed to 80 characters', 'Rendered as Default'];
    $expected_settings_button_names = [' name="parade_onepage_menu_settings_edit"', ' name="parade_onepage_sections_settings_edit"'];

    $this->DisplayFields($expected_fields);
    $this->DisplayLabels($expected_labels);
    $this->DisplayFormats($expected_formats);
    $this->DisplayPluginSummarys($expected_plugin_summarys);
    $this->DisplaySettingsButtonNames($expected_settings_button_names);
  }

//   /**
//    * Tests creating content with Parade onepage content type.
//    */
//   public function testCreateContentParadeOnepage() {
//     $this->drupalGet('admin/content');
//     $this->assertSession()->statusCodeEquals(200);
//     $this->assertSession()->pageTextContains('Content');
//     $this->assertText('Add content', "Add content exists.");
//     $this->clickLink('Add content');

//     // Check if we got redirected to the add content page.
//     // $this->drupalGet('node/add');

//     // $this->assertUrl('node/add');
//     // $this->assertSession()->statusCodeEquals(200);
//     // $this->assertSession()->pageTextContains('Add content');
//     // $this->assertText('Parade onepage', "The Parade onepage content type exists.");
//     // $this->clickLink('Parade onepage');

//     // // Check if we got redirected to the add Parade onepage content page.
//     // $this->assertUrl('node/add/parade_onepage');
//     // $this->assertSession()->statusCodeEquals(200);
//     // $this->assertSession()->pageTextContains('Create Parade onepage');


//     //////     //div[@class='paragraphs-dropbutton-wrapper']/input/@name
//     //Add text box - css diplay none
//     // Attribute='name=parade_onepage_sections_header_add_more'
//     // Attribute='name=parade_onepage_sections_images_add_more'
//     // Attribute='name=parade_onepage_sections_image_text_add_more'
//     // Attribute='name=parade_onepage_sections_locations_add_more'
//     // Attribute='name=parade_onepage_sections_simple_add_more'
//     // Attribute='name=parade_onepage_sections_text_box_add_more'
//     // Attribute='name=parade_onepage_sections_text_boxes_add_more'
//   }
}
