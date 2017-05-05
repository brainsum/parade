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
  public static $modules = ['parade', 'paragraphs', 'field_ui'];

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
   * Tests that the Paragraphs types exists.
   */
  public function testParagraphTypesPage() {
    $this->drupalGet('admin/structure/paragraphs_type');
    $this->assertSession()->statusCodeEquals(200);

    // Test that there are the Paragraphs types.
    $this->assertSession()->pageTextContains('PARAGRAPHS TYPES');

    $this->assertText('Header', "The Header paragraph type exists.");
    $this->assertText('header', "The Header paragraph type's machine name is header.");
    $this->assertText('Images', "The Images paragraph type exists.");
    $this->assertText('images', "The Images paragraph type's machine name is images.");
    $this->assertText('Locations', "The Locations paragraph type exists.");
    $this->assertText('locations', "The Locations paragraph type's machine name is locations.");
    $this->assertText('Parallax', "The Parallax paragraph type exists.");
    $this->assertText('parallax', "The Parallax paragraph type's machine name is parallax.");
    $this->assertText('Simple', "The Simple paragraph type exists.");
    $this->assertText('simple', "The Simple paragraph type's machine name is simple.");
    $this->assertText('Text and Image', "The Text and Image paragraph type exists.");
    $this->assertText('image_text', "The Text and Image paragraph type's machine name is image_text.");
    $this->assertText('Text box', "The Text box paragraph type exists.");
    $this->assertText('text_box', "The Text box paragraph type's machine name is text_box.");
    $this->assertText('Text boxes', "The Text boxes paragraph type exists.");
    $this->assertText('text_boxes', "The Text boxes paragraph type's machine name is text_boxes.");
  }

  private function ParagraphTypeFieldLabels($expected_labels) {
    $nr = count($expected_labels);
    $field_labels = $this->xpath("//table[@id='field-overview']//td[1]/text()");
    $this->assertEqual(count($field_labels), $nr, format_string('Found @nr field label.', array('@nr' => $nr)));
    for ($i = 0; $i < $nr; $i++) {
      $this->assertEquals($expected_labels[$i], $field_labels[$i]->getHtml());
    }
  }

  private function ParagraphTypeFieldMachineNames($expected_machine_names) {
    $nr = count($expected_machine_names);
    $field_machine_names = $this->xpath("//table[@id='field-overview']//td[@class='priority-medium']/text()");
    $this->assertEqual(count($field_machine_names), $nr, format_string('Found @nr field machine name.', array('@nr' => $nr)));
    for ($i = 0; $i < $nr; $i++) {
      $this->assertEquals($expected_machine_names[$i], $field_machine_names[$i]->getHtml());
    }
  }

  private function ParagraphTypeFieldTypes($expected_types) {
    $nr = count($expected_types);
    $field_types = $this->xpath("//table[@id='field-overview']//td/a/text()");
    $this->assertEqual(count($field_types), $nr, format_string('Found @nr field type.', array('@nr' => $nr)));
    for ($i = 0; $i < $nr; $i++) {
      $this->assertEquals($expected_types[$i], $field_types[$i]->getHtml());
    }
  }

  private function ParagraphTypeDisplayFields($expected_fields) {
    $nr = count($expected_fields);
    $fields = $this->xpath("//table[@id='field-display-overview']//td[position()=1 and not(@colspan='7' and text()!='Disabled')]/text()");
    $this->assertEqual(count($fields), $nr, format_string('Found @nr field.', array('@nr' => $nr)));
    for ($i = 0; $i < $nr; $i++) {
      $this->assertEquals($expected_fields[$i], $fields[$i]->getHtml());
    }
  }

  private function ParagraphTypeDisplayLabels($expected_labels) {
    $nr = count($expected_labels);
    $labels = $this->xpath("//table[@id='field-display-overview']//td[4]/div/select/option[@selected='selected']/text()");
    $this->assertEqual(count($labels), $nr, format_string('Found @nr label.', array('@nr' => $nr)));
    for ($i = 0; $i < $nr; $i++) {
      $this->assertEquals($expected_labels[$i], $labels[$i]->getHtml());
    }
  }

  private function ParagraphTypeDisplayFormats($expected_formats) {
    $nr = count($expected_formats);
    $formats = $this->xpath("//table[@id='field-display-overview']//td[5]/div/select/option[@selected='selected']/text()");
    $this->assertEqual(count($formats), $nr, format_string('Found @nr format.', array('@nr' => $nr)));
    for ($i = 0; $i < $nr; $i++) {
      $this->assertEquals($expected_formats[$i], $formats[$i]->getHtml());
    }
  }

  private function ParagraphTypeDisplayPluginSummarys($expected_plugin_summarys) {
    $nr = count($expected_plugin_summarys);
    $plugin_summarys = $this->xpath("//table[@id='field-display-overview']//td[@class='field-plugin-summary-cell']/div[@class='field-plugin-summary']/text()");
    $this->assertEqual(count($plugin_summarys), $nr, format_string('Found @nr plugin summary.', array('@nr' => $nr)));
    for ($i = 0; $i < $nr; $i++) {
      $this->assertEquals($expected_plugin_summarys[$i], $plugin_summarys[$i]->getHtml());
    }
  }

  private function ParagraphTypeDisplaySettingsButtonNames($expected_settings_button_names) {
    $nr = count($expected_settings_button_names);
    $settings_button_names = $this->xpath("//table[@id='field-display-overview']//td[7]//div [@class='field-plugin-settings-edit-wrapper']/input/@name");
    $this->assertEqual(count($settings_button_names), $nr, format_string('Found @nr settings button names.', array('@nr' => $nr)));
    for ($i = 0; $i < $nr; $i++) {
      $this->assertEquals($expected_settings_button_names[$i], $settings_button_names[$i]->getHtml());
    }
  }

  /**
   * Tests that the Paragraphs types are configured corectly.
   */
  public function testParagraphTypesConfiguration() {
    // Header parapgraph type - Fields.
    $this->drupalGet('admin/structure/paragraphs_type/header/fields');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains('Manage fields');

    $expected_labels = ['Anchor', 'Background', 'Call to action', 'Classes', 'Enable parallax', 'Lead text', 'Secondary title', 'Title'];
    $expected_machine_names = ['field_anchor', 'field_background', 'field_call_to_action', 'parade_classes', 'field_enable_parallax', 'field_lead_text', 'field_secondary_title', 'field_title'];
    $expected_types = ['Text (plain)', 'File', 'Link', 'Entity reference', 'Boolean', 'Text (plain, long)', 'Text (plain)', 'Text (plain)'];

    $this->ParagraphTypeFieldLabels($expected_labels);
    $this->ParagraphTypeFieldMachineNames($expected_machine_names);
    $this->ParagraphTypeFieldTypes($expected_types);

    // Header parapgraph type - Manage display - Default.
    $this->drupalGet('admin/structure/paragraphs_type/header/display');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains('Manage display');

    $this->assertText('Lead text', "Authored by exists.");

    $expected_fields = ['Title', 'Secondary title', 'Lead text', 'Call to action', 'Disabled', /*'Authored by', 'Authored on',*/ 'Anchor', 'Background', 'Color scheme', 'Enable parallax'];
    $expected_labels = ['- Hidden -', '- Hidden -', '- Hidden -', '- Hidden -', /*'Above', 'Above',*/ 'Above', 'Above', 'Above', 'Above'];
    $expected_formats = ['Plain text', 'Plain text', 'Plain text', 'Link', /*'- Hidden -', '- Hidden -',*/ '- Hidden -', '- Hidden -', '- Hidden -', '- Hidden -'];
    $expected_plugin_summarys = ['Link text trimmed to 80 characters', 'Add rel="nofollow"'];
    $expected_settings_button_names = [' name="field_title_settings_edit"', ' name="field_secondary_title_settings_edit"', ' name="field_call_to_action_settings_edit"'];

    $this->ParagraphTypeDisplayFields($expected_fields);
    $this->ParagraphTypeDisplayLabels($expected_labels);
    $this->ParagraphTypeDisplayFormats($expected_formats);
    $this->ParagraphTypeDisplayPluginSummarys($expected_plugin_summarys);
    $this->ParagraphTypeDisplaySettingsButtonNames($expected_settings_button_names);

    // Header parapgraph type - Manage display - Preview.
    $this->drupalGet('admin/structure/paragraphs_type/header/display/preview');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains('Manage display');

    $expected_fields = ['Title', 'Disabled', /*'Authored by', 'Authored on',*/ 'Anchor', 'Background', 'Call to action', 'Color scheme', 'Enable parallax', 'Lead text', 'Secondary title'];
    $expected_labels = ['Inline', /*'Above', 'Above',*/ 'Above', 'Above', 'Above', 'Above', 'Above', 'Above', 'Above'];
    $expected_formats = ['Plain text', /*'- Hidden -', '- Hidden -',*/ '- Hidden -', '- Hidden -', '- Hidden -', '- Hidden -', '- Hidden -', '- Hidden -', '- Hidden -'];
    $expected_settings_button_names = [' name="field_title_settings_edit"'];

    $this->ParagraphTypeDisplayFields($expected_fields);
    $this->ParagraphTypeDisplayLabels($expected_labels);
    $this->ParagraphTypeDisplayFormats($expected_formats);
    $this->ParagraphTypeDisplaySettingsButtonNames($expected_settings_button_names);


    // Images parapgraph type - Fields.
    $this->drupalGet('admin/structure/paragraphs_type/images/fields');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains('Manage fields');

    $expected_labels = ['Anchor', 'Color scheme', 'Images', 'Title'];
    $expected_machine_names = ['field_anchor', 'field_color_scheme', 'field_images', 'field_title'];
    $expected_types = ['Text (plain)', 'Entity reference', 'Image', 'Text (plain)'];

    $this->ParagraphTypeFieldLabels($expected_labels);
    $this->ParagraphTypeFieldMachineNames($expected_machine_names);
    $this->ParagraphTypeFieldTypes($expected_types);

    // Images parapgraph type - Manage display - Default.
    $this->drupalGet('admin/structure/paragraphs_type/images/display');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains('Manage display');

    $expected_fields = ['Title', 'Images', 'Disabled', /*'Authored by', 'Authored on',*/ 'Anchor', 'Color scheme'];
    $expected_labels = ['- Hidden -', '- Hidden -', /*'Above', 'Above',*/ 'Above', 'Above'];
    $expected_formats = ['Plain text', 'Image', /*'- Hidden -', '- Hidden -',*/ '- Hidden -', '- Hidden -'];
    $expected_plugin_summarys = ['Image style: Thumbnail (100×100)'];
    $expected_settings_button_names = [' name="field_title_settings_edit"', ' name="field_images_settings_edit"'];

    $this->ParagraphTypeDisplayFields($expected_fields);
    $this->ParagraphTypeDisplayLabels($expected_labels);
    $this->ParagraphTypeDisplayFormats($expected_formats);
    $this->ParagraphTypeDisplayPluginSummarys($expected_plugin_summarys);
    $this->ParagraphTypeDisplaySettingsButtonNames($expected_settings_button_names);

    // Images parapgraph type - Manage display - Preview.
    $this->drupalGet('admin/structure/paragraphs_type/images/display/preview');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains('Manage display');

    $expected_fields = ['Title', 'Disabled', /*'Authored by', 'Authored on',*/ 'Anchor', 'Color scheme', 'Images'];
    $expected_labels = ['Inline', /*'Above', 'Above',*/ 'Above', 'Above', 'Above'];
    $expected_formats = ['Plain text', /*'- Hidden -', '- Hidden -',*/ '- Hidden -', '- Hidden -', '- Hidden -'];
    $expected_settings_button_names = [' name="field_title_settings_edit"'];

    $this->ParagraphTypeDisplayFields($expected_fields);
    $this->ParagraphTypeDisplayLabels($expected_labels);
    $this->ParagraphTypeDisplayFormats($expected_formats);
    $this->ParagraphTypeDisplaySettingsButtonNames($expected_settings_button_names);


    // Locations parapgraph type - Fields.
    $this->drupalGet('admin/structure/paragraphs_type/locations/fields');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains('Manage fields');

    $expected_labels = ['Anchor', 'Color scheme', 'Geofield', 'Map markers', 'Text', 'Title'];
    $expected_machine_names = ['field_anchor', 'field_color_scheme', 'field_geofield', 'field_location', 'field_text', 'field_title'];
    $expected_types = ['Text (plain)', 'Entity reference', 'Geofield', 'Text (plain)', 'Text (formatted, long)', 'Text (plain)'];

    $this->ParagraphTypeFieldLabels($expected_labels);
    $this->ParagraphTypeFieldMachineNames($expected_machine_names);
    $this->ParagraphTypeFieldTypes($expected_types);

    // Locations parapgraph type - Manage display - Default.
    $this->drupalGet('admin/structure/paragraphs_type/locations/display');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains('Manage display');

    $expected_fields = ['Disabled', /*'Authored by', 'Authored on',*/ 'Anchor', 'Color scheme', 'Geofield', 'Map markers', 'Text', 'Title'];
    $expected_labels = [/*'Above', 'Above',*/ 'Above', 'Above', 'Above', 'Above', 'Above', 'Above'];
    $expected_formats = [/*'- Hidden -', '- Hidden -',*/ '- Hidden -', '- Hidden -', '- Hidden -', '- Hidden -', '- Hidden -', '- Hidden -'];
    
    $this->ParagraphTypeDisplayFields($expected_fields);
    $this->ParagraphTypeDisplayLabels($expected_labels);
    $this->ParagraphTypeDisplayFormats($expected_formats);

    // Locations parapgraph type - Manage display - Preview.
    $this->drupalGet('admin/structure/paragraphs_type/locations/display/preview');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains('Manage display');

    $expected_fields = ['Title', 'Anchor', 'Disabled', /*'Authored by', 'Authored on',*/ 'Color scheme', 'Geofield', 'Map markers', 'Text'];
    $expected_labels = ['Inline', 'Inline', /*'Above', 'Above',*/ 'Above', 'Above', 'Above', 'Above'];
    $expected_formats = ['Plain text', 'Plain text', /*'- Hidden -', '- Hidden -',*/ '- Hidden -', '- Hidden -', '- Hidden -', '- Hidden -'];
    $expected_settings_button_names = [' name="field_title_settings_edit"', ' name="field_anchor_settings_edit"'];

    $this->ParagraphTypeDisplayFields($expected_fields);
    $this->ParagraphTypeDisplayLabels($expected_labels);
    $this->ParagraphTypeDisplayFormats($expected_formats);
    $this->ParagraphTypeDisplaySettingsButtonNames($expected_settings_button_names);


    // Parallax parapgraph type - Fields.
    $this->drupalGet('admin/structure/paragraphs_type/parallax/fields');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains('Manage fields');

    $expected_labels = ['Anchor', 'Background', 'Background', 'Enable parallax', 'Minimum height', 'Text'];
    $expected_machine_names = ['field_anchor', 'field_background', 'field_image', 'field_enable_parallax', 'field_minimum_height', 'field_text'];
    $expected_types = ['Text (plain)', 'File', 'Image', 'Boolean', 'Number (integer)', 'Text (formatted, long)'];

    $this->ParagraphTypeFieldLabels($expected_labels);
    $this->ParagraphTypeFieldMachineNames($expected_machine_names);
    $this->ParagraphTypeFieldTypes($expected_types);

    // Parallax parapgraph type - Manage display - Default.
    $this->drupalGet('admin/structure/paragraphs_type/parallax/display');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains('Manage display');

    $expected_fields = ['Text', 'Disabled', /*'Authored by', 'Authored on',*/ 'Anchor', 'Background', 'Enable parallax', 'Background', 'Minimum height'];
    $expected_labels = ['- Hidden -', /*'Above', 'Above',*/ 'Above', 'Above', 'Above', 'Above', 'Above'];
    $expected_formats = ['Default', /*'- Hidden -', '- Hidden -',*/ '- Hidden -', '- Hidden -', '- Hidden -', '- Hidden -', '- Hidden -'];
    
    $this->ParagraphTypeDisplayFields($expected_fields);
    $this->ParagraphTypeDisplayLabels($expected_labels);
    $this->ParagraphTypeDisplayFormats($expected_formats);


    // Simple parapgraph type - Fields.
    $this->drupalGet('admin/structure/paragraphs_type/simple/fields');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains('Manage fields');

    $expected_labels = ['Anchor', 'Color scheme', 'Content', 'Layout', 'Title'];
    $expected_machine_names = ['field_anchor', 'field_color_scheme', 'field_text', 'field_simple_layout', 'field_title'];
    $expected_types = ['Text (plain)', 'Entity reference', 'Text (formatted, long)', 'List (integer)', 'Text (plain)'];

    $this->ParagraphTypeFieldLabels($expected_labels);
    $this->ParagraphTypeFieldMachineNames($expected_machine_names);
    $this->ParagraphTypeFieldTypes($expected_types);

    // Simple parapgraph type - Manage display - Default.
    $this->drupalGet('admin/structure/paragraphs_type/simple/display');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains('Manage display');

    $expected_fields = ['Title', 'Content', 'Disabled', /*'Authored by', 'Authored on',*/ 'Anchor', 'Color scheme', 'Layout'];
    $expected_labels = ['- Hidden -', '- Hidden -', /*'Above', 'Above',*/ 'Above', 'Above', 'Above'];
    $expected_formats = ['Plain text', 'Default', /*'- Hidden -', '- Hidden -',*/ '- Hidden -', '- Hidden -', '- Hidden -'];
    $expected_settings_button_names = [' name="field_title_settings_edit"'];

    $this->ParagraphTypeDisplayFields($expected_fields);
    $this->ParagraphTypeDisplayLabels($expected_labels);
    $this->ParagraphTypeDisplayFormats($expected_formats);
    $this->ParagraphTypeDisplaySettingsButtonNames($expected_settings_button_names);

    // Simple parapgraph type - Manage display - Preview.
    $this->drupalGet('admin/structure/paragraphs_type/simple/display/preview');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains('Manage display');

    $expected_fields = ['Title', 'Layout', 'Disabled', /*'Authored by', 'Authored on',*/ 'Anchor', 'Color scheme', 'Content'];
    $expected_labels = ['Inline', 'Inline', /*'Above', 'Above',*/ 'Above', 'Above', 'Above'];
    $expected_formats = ['Plain text', 'Default', /*'- Hidden -', '- Hidden -',*/ '- Hidden -', '- Hidden -', '- Hidden -'];
    $expected_settings_button_names = [' name="field_title_settings_edit"'];

    $this->ParagraphTypeDisplayFields($expected_fields);
    $this->ParagraphTypeDisplayLabels($expected_labels);
    $this->ParagraphTypeDisplayFormats($expected_formats);
    $this->ParagraphTypeDisplaySettingsButtonNames($expected_settings_button_names);


    // Text and Image parapgraph type - Fields.
    $this->drupalGet('admin/structure/paragraphs_type/image_text/fields');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains('Manage fields');

    $expected_labels = ['Anchor', 'Call to action', 'Color scheme', 'Image', 'Layout', 'Text', 'Title'];
    $expected_machine_names = ['field_anchor', 'field_call_to_action', 'field_color_scheme', 'field_image', 'field_image_text_layout', 'field_text', 'field_title'];
    $expected_types = ['Text (plain)', 'Link', 'Entity reference', 'Image', 'List (integer)', 'Text (formatted, long)', 'Text (plain)'];

    $this->ParagraphTypeFieldLabels($expected_labels);
    $this->ParagraphTypeFieldMachineNames($expected_machine_names);
    $this->ParagraphTypeFieldTypes($expected_types);

    // Text and Image parapgraph type - Manage display - Default.
    $this->drupalGet('admin/structure/paragraphs_type/image_text/display');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains('Manage display');

    $expected_fields = ['Title', 'Image', 'Text', 'Call to action', 'Disabled', /*'Authored by', 'Authored on',*/ 'Anchor', 'Color scheme', 'Layout'];
    $expected_labels = ['- Hidden -', '- Hidden -', '- Hidden -', '- Hidden -', /*'Above', 'Above',*/ 'Above', 'Above', 'Above'];
    $expected_formats = ['Plain text', 'Image', 'Default', 'Link', /*'- Hidden -', '- Hidden -',*/ '- Hidden -', '- Hidden -', '- Hidden -'];
    $expected_plugin_summarys = ['Original image', 'Link text trimmed to 80 characters', 'Add rel="nofollow"'];
    $expected_settings_button_names = [' name="field_title_settings_edit"', ' name="field_image_settings_edit"', ' name="field_call_to_action_settings_edit"'];

    $this->ParagraphTypeDisplayFields($expected_fields);
    $this->ParagraphTypeDisplayLabels($expected_labels);
    $this->ParagraphTypeDisplayFormats($expected_formats);
    $this->ParagraphTypeDisplayPluginSummarys($expected_plugin_summarys);
    $this->ParagraphTypeDisplaySettingsButtonNames($expected_settings_button_names);

    // Text and Image parapgraph type - Manage display - Preview.
    $this->drupalGet('admin/structure/paragraphs_type/image_text/display/preview');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains('Manage display');

    $expected_fields = ['Title', 'Layout', 'Disabled', /*'Authored by', 'Authored on',*/ 'Anchor', 'Call to action', 'Color scheme', 'Image', 'Text'];
    $expected_labels = ['Inline', 'Inline', /*'Above', 'Above',*/ 'Above', 'Above', 'Above', 'Above', 'Above'];
    $expected_formats = ['Plain text', 'Default', /*'- Hidden -', '- Hidden -',*/ '- Hidden -', '- Hidden -', '- Hidden -', '- Hidden -', '- Hidden -'];
    $expected_settings_button_names = [' name="field_title_settings_edit"'];

    $this->ParagraphTypeDisplayFields($expected_fields);
    $this->ParagraphTypeDisplayLabels($expected_labels);
    $this->ParagraphTypeDisplayFormats($expected_formats);
    $this->ParagraphTypeDisplaySettingsButtonNames($expected_settings_button_names);


    // Text box parapgraph type - Fields.
    $this->drupalGet('admin/structure/paragraphs_type/text_box/fields');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains('Manage fields');

    $expected_labels = ['Color scheme', 'Image', 'Text', 'Title'];
    $expected_machine_names = ['field_color_scheme', 'field_image', 'field_text', 'field_title'];
    $expected_types = ['Entity reference', 'Image', 'Text (formatted, long)', 'Text (plain)'];

    $this->ParagraphTypeFieldLabels($expected_labels);
    $this->ParagraphTypeFieldMachineNames($expected_machine_names);
    $this->ParagraphTypeFieldTypes($expected_types);

    // Text box parapgraph type - Manage display - Default.
    $this->drupalGet('admin/structure/paragraphs_type/text_box/display');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains('Manage display');

    $expected_fields = ['Title', 'Text', 'Image', 'Disabled', /*'Authored by', 'Authored on',*/ 'Color scheme'];
    $expected_labels = ['- Hidden -', '- Hidden -', '- Hidden -', /*'Above', 'Above',*/ 'Above'];
    $expected_formats = ['Plain text', 'Default', 'Image', /*'- Hidden -', '- Hidden -',*/ '- Hidden -'];
    $expected_plugin_summarys = ['Original image'];
    $expected_settings_button_names = [' name="field_title_settings_edit"', ' name="field_image_settings_edit"'];

    $this->ParagraphTypeDisplayFields($expected_fields);
    $this->ParagraphTypeDisplayLabels($expected_labels);
    $this->ParagraphTypeDisplayFormats($expected_formats);
    $this->ParagraphTypeDisplayPluginSummarys($expected_plugin_summarys);
    $this->ParagraphTypeDisplaySettingsButtonNames($expected_settings_button_names);

    // Text box parapgraph type - Manage display - Custom.
    $this->drupalGet('admin/structure/paragraphs_type/text_box/display/custom');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains('Manage display');

    $expected_fields = ['Image', 'Title', 'Text', 'Disabled', /*'Authored by', 'Authored on',*/ 'Color scheme'];
    $expected_labels = ['- Hidden -', '- Hidden -', '- Hidden -', /*'Above', 'Above',*/ 'Above'];
    $expected_formats = ['Image', 'Plain text', 'Default', /*'- Hidden -', '- Hidden -',*/ '- Hidden -'];
    $expected_plugin_summarys = ['Original image'];
    $expected_settings_button_names = [' name="field_image_settings_edit"', ' name="field_title_settings_edit"'];

    $this->ParagraphTypeDisplayFields($expected_fields);
    $this->ParagraphTypeDisplayLabels($expected_labels);
    $this->ParagraphTypeDisplayFormats($expected_formats);
    $this->ParagraphTypeDisplayPluginSummarys($expected_plugin_summarys);
    $this->ParagraphTypeDisplaySettingsButtonNames($expected_settings_button_names);

    // Text box parapgraph type - Manage display - Preview.
    $this->drupalGet('admin/structure/paragraphs_type/text_box/display/preview');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains('Manage display');

    $expected_fields = ['Title', 'Disabled', /*'Authored by', 'Authored on',*/ 'Color scheme', 'Image', 'Text'];
    $expected_labels = ['Inline', /*'Above', 'Above',*/ 'Above', 'Above', 'Above'];
    $expected_formats = ['Plain text', /*'- Hidden -', '- Hidden -',*/ '- Hidden -', '- Hidden -', '- Hidden -'];
    $expected_settings_button_names = [' name="field_title_settings_edit"'];

    $this->ParagraphTypeDisplayFields($expected_fields);
    $this->ParagraphTypeDisplayLabels($expected_labels);
    $this->ParagraphTypeDisplayFormats($expected_formats);
    $this->ParagraphTypeDisplaySettingsButtonNames($expected_settings_button_names);


    // Text boxes parapgraph type - Fields.
    $this->drupalGet('admin/structure/paragraphs_type/text_boxes/fields');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains('Manage fields');

    $expected_labels = ['Anchor', 'Boxes', 'Boxes per row', 'Color scheme', 'Layout', 'Title'];
    $expected_machine_names = ['field_anchor', 'field_paragraphs', 'field_boxes_per_row', 'field_color_scheme', 'field_text_boxes_layout', 'field_title'];
    $expected_types = ['Text (plain)', 'Entity reference revisions', 'Number (integer)', 'Entity reference', 'List (integer)', 'Text (plain)'];

    $this->ParagraphTypeFieldLabels($expected_labels);
    $this->ParagraphTypeFieldMachineNames($expected_machine_names);
    $this->ParagraphTypeFieldTypes($expected_types);

    // Text boxes parapgraph type - Manage display - Default.
    $this->drupalGet('admin/structure/paragraphs_type/text_boxes/display');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains('Manage display');

    $expected_fields = ['Title', 'Boxes', 'Disabled', /*'Authored by', 'Authored on',*/ 'Anchor', 'Boxes per row', 'Color scheme', 'Layout'];
    $expected_labels = ['Above', 'Above', /*'Above', 'Above',*/ 'Above', 'Above', 'Above', 'Above'];
    $expected_formats = ['Plain text', 'Parade Rendered entity', /*'- Hidden -', '- Hidden -',*/ '- Hidden -', '- Hidden -', '- Hidden -', '- Hidden -'];
    $expected_plugin_summarys = ['Rendered as Default/Custom'];
    $expected_settings_button_names = [' name="field_title_settings_edit"', ' name="field_paragraphs_settings_edit"'];

    $this->ParagraphTypeDisplayFields($expected_fields);
    $this->ParagraphTypeDisplayLabels($expected_labels);
    $this->ParagraphTypeDisplayFormats($expected_formats);
    $this->ParagraphTypeDisplayPluginSummarys($expected_plugin_summarys);
    $this->ParagraphTypeDisplaySettingsButtonNames($expected_settings_button_names);

    // Text boxes parapgraph type - Manage display - Preview.
    $this->drupalGet('admin/structure/paragraphs_type/text_boxes/display/preview');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains('Manage display');

    $expected_fields = ['Title', 'Layout', 'Disabled', /*'Authored by', 'Authored on',*/ 'Anchor', 'Boxes per row', 'Color scheme', 'Boxes'];
    $expected_labels = ['Inline', 'Inline', /*'Above', 'Above',*/ 'Above', 'Above', 'Above', 'Above'];
    $expected_formats = ['Plain text', 'Default', /*'- Hidden -', '- Hidden -',*/ '- Hidden -', '- Hidden -', '- Hidden -', '- Hidden -'];
    $expected_settings_button_names = [' name="field_title_settings_edit"'];

    $this->ParagraphTypeDisplayFields($expected_fields);
    $this->ParagraphTypeDisplayLabels($expected_labels);
    $this->ParagraphTypeDisplayFormats($expected_formats);
    $this->ParagraphTypeDisplaySettingsButtonNames($expected_settings_button_names);
  }
}
