<?php

namespace Drupal\Tests\parade_edit\Functional;

use Drupal\Core\Url;

/**
 * Tests general parade edit feature for nodes.
 *
 * @group parade_edit
 */
class ParadeEditStateNodeTest extends ParadeEditStateTestBase {

  protected $testNodeId;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->drupalLogin($this->adminUser);
  }

  /**
   * Create test content type.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function setNodeType() {
    $this->createNodeType('Parade edit content', $this->nodeTypeMachineName);

    // Add Hungarian language.
    $edit = [];
    $edit['predefined_langcode'] = 'hu';
    $this->drupalPostForm('admin/config/regional/language/add', $edit, t('Add language'));

    // Enable URL language detection and selection.
    $edit = ['language_interface[enabled][language-url]' => 1];
    $this->drupalPostForm('admin/config/regional/language/detection', $edit, t('Save settings'));

    // Enable parade_demo feature for our content.
    $edit = [
      'bundles[' . $this->nodeTypeMachineName . '][enabled]' => TRUE,
      'bundles[' . $this->nodeTypeMachineName . '][css_disabled]' => TRUE,
    ];
    $this->drupalPostForm('admin/config/content/parade_demo', $edit, 'Save configuration');

    // Enable translation for our content.
    $edit = [
      'entity_types[node]' => TRUE,
      'settings[node][' . $this->nodeTypeMachineName . '][translatable]' => TRUE,
      'settings[node][' . $this->nodeTypeMachineName . '][fields][title]' => TRUE,
      'entity_types[paragraph]' => TRUE,
      'settings[paragraph][simple][translatable]' => TRUE,
      'settings[paragraph][simple][fields][parade_title]' => TRUE,
      'settings[paragraph][simple][fields][parade_text]' => TRUE,
    ];
    $this->drupalPostForm('admin/config/regional/content-language', $edit, 'Save configuration');
  }

  /**
   * Tests add EN draft page, publish, add translation.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   * @throws \Behat\Mink\Exception\ExpectationException
   */
  public function testAddNodeWithSections() {
    // Create new content in draft.
    $this->setNodeType();
    // Get a URL object for the form, specifying no JS.
    $nodeAddUrl = Url::fromRoute('node.add', ['node_type' => $this->nodeTypeMachineName, 'nojs' => 'nojs']);

    $this->drupalPostForm($nodeAddUrl, [
      'title[0][value]' => 'Some content',
    ], 'Simple');

    $this->drupalPostForm(NULL, [
      'parade_onepage_sections[0][subform][parade_title][0][value]' => 'Simple title EN',
      'parade_onepage_sections[0][subform][parade_text][0][value]' => 'Simple text EN',
    ], 'Save and Create New Draft');

    // After saving, we should be at the canonical URL and viewing the first
    // revision.
    $node = $this->drupalGetNodeByTitle('Some content');
    $this->testNodeId = $node->id();
    $assertSession = $this->assertSession();
    $page = $this->getSession()->getPage();

    $this->drupalGet('node/' . $this->testNodeId);
    $assertSession->pageTextContains('Simple title EN');
    $assertSession->pageTextContains('Simple text EN');

    // Check TAB labels.
    $tabs = $this->xpath('//ul[contains(@class, "tabs")]//li/a');
    $this->assertEquals('View - Unpublished(active tab)', $tabs[0]->getText());
    $this->assertEquals('Edit', $tabs[1]->getText());
    $this->assertEquals('Try the new editor', $tabs[2]->getText());

    $editUrl = Url::fromRoute('entity.node.edit_form', ['node' => $this->testNodeId, 'nojs' => 'nojs']);
    $latestUrl = 'node/' . $this->testNodeId . '/latest';
    $viewUrl = Url::fromRoute('entity.node.canonical', ['node' => $this->testNodeId]);

    // Add new draft for unpublished EN content.
    $this->drupalPostForm($editUrl, [], 'parade_onepage_sections_0_edit');
    $this->drupalPostForm(NULL, [
      'parade_onepage_sections[0][subform][parade_title][0][value]' => 'Simple title v2 EN',
    ], 'Save and Create New Draft');
    // Check node view.
    $assertSession->addressEquals(Url::fromRoute('entity.node.canonical', ['node' => $this->testNodeId]));
    $assertSession->pageTextContains('Simple title v2 EN');

    // Publish EN.
    $this->drupalGet($latestUrl);
    $page->pressButton('Publish');
    $assertSession->addressEquals(Url::fromRoute('entity.node.canonical', ['node' => $this->testNodeId]));
    $assertSession->pageTextContains('Simple title v2 EN');

    // Check TAB labels.
    $tabs = $this->xpath('//ul[contains(@class, "tabs")]//li/a');
    $this->assertEquals('View - Live(active tab)', $tabs[0]->getText());
    $this->assertEquals('Edit', $tabs[1]->getText());
    $this->assertEquals('Try the new editor', $tabs[2]->getText());

    // Add new draft for published content.
    $this->drupalPostForm($editUrl, [], 'parade_onepage_sections_0_edit');
    $this->drupalPostForm(NULL, [
      'parade_onepage_sections[0][subform][parade_title][0][value]' => 'Simple title draft EN',
    ], 'Save and Create New Draft');

    // Check node view page.
    $this->drupalGet($viewUrl);
    $assertSession->pageTextContains('Simple title v2 EN');

    // Add translation.
    $translateUrl = 'node/' . $this->testNodeId . '/translations';
    $this->drupalGet($translateUrl);
    $this->clickLink('Add');
    $this->getSession()->getPage()->pressButton('parade_onepage_sections_0_edit');
    $this->drupalPostForm(NULL, [
      'title[0][value]' => 'Some HU content',
      'parade_onepage_sections[0][subform][parade_title][0][value]' => 'Simple title draft HU',
      'parade_onepage_sections[0][subform][parade_text][0][value]' => 'Simple text HU',
    ], 'Save and Create New Draft (this translation)');

    // Check node view page.
    $this->drupalGet('node/' . $this->testNodeId);
    $assertSession->pageTextContains('Simple title v2 EN');
    $assertSession->pageTextContains('Simple text EN');
    $this->drupalGet('hu/node/' . $this->testNodeId);
    $assertSession->pageTextContains('Simple title draft HU');
    $assertSession->pageTextContains('Simple text HU');

    // Publish HU.
    $this->drupalGet('hu/' . $latestUrl);
    $page->pressButton('Publish');
    $this->drupalGet('hu/node/' . $this->testNodeId);
    $assertSession->pageTextContains('Simple title draft HU');
    $assertSession->pageTextContains('Simple text HU');

    $this->drupalGet('hu/' . $latestUrl);
    $assertSession->pageTextContains('Simple title draft HU');
    $assertSession->pageTextContains('Simple text HU');
  }

  /**
   * Tests add EN draft page, add published translation.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   * @throws \Behat\Mink\Exception\ElementNotFoundException
   * @throws \Behat\Mink\Exception\ResponseTextException
   */
  public function testModerationPublishNode() {
    $this->setNodeType();
    $assertSession = $this->assertSession();

    $nodeAddUrl = Url::fromRoute('node.add', ['node_type' => $this->nodeTypeMachineName, 'nojs' => 'nojs']);

    $this->drupalPostForm($nodeAddUrl, [
      'title[0][value]' => 'Some content',
    ], 'Simple');

    $this->drupalPostForm(NULL, [
      'parade_onepage_sections[0][subform][parade_title][0][value]' => 'Simple title EN',
      'parade_onepage_sections[0][subform][parade_text][0][value]' => 'Simple text EN',
    ], 'Save and Create New Draft');

    // After saving, we should be at the canonical URL and viewing the first
    // revision.
    $node = $this->drupalGetNodeByTitle('Some content');
    $this->testNodeId = $node->id();
    $latestUrl = 'node/' . $this->testNodeId . '/latest';

    // Add translation.
    $translateUrl = 'node/' . $this->testNodeId . '/translations';
    $this->drupalGet($translateUrl);
    $this->clickLink('Add');
    $this->getSession()->getPage()->pressButton('parade_onepage_sections_0_edit');
    $this->drupalPostForm(NULL, [
      'title[0][value]' => 'Some HU content',
      'parade_onepage_sections[0][subform][parade_title][0][value]' => 'Simple title draft HU',
      'parade_onepage_sections[0][subform][parade_text][0][value]' => 'Simple text HU',
    ], 'Save and Publish (this translation)');

    $this->drupalGet($latestUrl);
    $assertSession->pageTextContains('Simple title EN');
    $assertSession->pageTextContains('Simple text EN');

    $this->drupalGet('hu/' . $latestUrl);
    $assertSession->pageTextContains('Simple title draft HU');
    $assertSession->pageTextContains('Simple text HU');
  }

}
