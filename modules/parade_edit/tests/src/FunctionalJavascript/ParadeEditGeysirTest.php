<?php

namespace Drupal\Tests\parade_edit\FunctionalJavascript;

use Drupal\FunctionalJavascriptTests\JavascriptTestBase;
use Drupal\Component\Render\FormattableMarkup;
use Drupal\node\Entity\NodeType;
use Drupal\Core\Entity\Entity\EntityViewDisplay;
use Behat\Mink\Exception\ExpectationException;

/**
 * Functional test of dependent dropdown example.
 *
 * @group parade_edit
 */
class ParadeEditGeysirTest extends JavascriptTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'node',
    'block',
    'locale',
    'content_translation',
    'workbench_moderation',
    'menu_ui',
  ];

  /**
   * Admin user.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $adminUser;

  /**
   * Permissions to grant admin user.
   *
   * @var array
   */
  protected $permissions = [
    'access administration pages',
    'administer content types',
    'administer site configuration',
    'administer nodes',
    'administer blocks',
    'administer languages',
    'view latest version',
    'view any unpublished content',
    'access content overview',
    'use draft_draft transition',
    'use draft_published transition',
    'use published_draft transition',
    // @todo
    'bypass node access',
    'administer content translation',
    'create content translations',
    // @todo
    'translate any entity',
    'administer modules',
    'access contextual links',
    'geysir manage paragraphs from front-end',
    'access in-place editing',
  ];

  /**
   * Node type to create, test.
   *
   * @var string
   */
  protected $nodeTypeMachineName = 'parade_edit_content';

  protected $testNodeId;

  /**
   * {@inheritdoc}
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  protected function setUp() {
    parent::setUp();

    \Drupal::service('theme_handler')->install(['bartik', 'seven']);
    $this->config('system.theme')
      ->set('default', 'bartik')
      ->set('admin', 'seven')
      ->save();

    $this->drupalPlaceBlock('local_actions_block');
    $this->drupalPlaceBlock('bartik_page_title', [
      'id' => 'bartik_page_title',
      'region' => 'content',
    ]);

    $this->drupalPlaceBlock('local_tasks_block', [
      'id' => 'tabs_block',
      'region' => 'content',
    ]);

    $success = \Drupal::service('module_installer')->install(['parade_demo', 'parade_edit']);
    self::assertTrue($success, new FormattableMarkup('Enabled module: %modules', ['%module' => 'parade_demo, parade_edit']));

    // Enable workbench_moderation for parade_onepage node type.
    $machineName = 'parade_onepage';
    $nodeType = NodeType::load($machineName);
    $nodeType->setThirdPartySetting('workbench_moderation', 'enabled', TRUE);
    $nodeType->setThirdPartySetting('workbench_moderation', 'allowed_moderation_states', [
      'draft',
      'published',
    ]);
    $nodeType->setThirdPartySetting('workbench_moderation', 'default_moderation_state', 'draft');
    $nodeType->save();

    // Enable parade_edit_moderation_control and disable workbench's.
    $entityDisplay = EntityViewDisplay::load('node.' . $machineName . '.default');
    $entityDisplay->setComponent('parade_edit_moderation_control', ['weight' => -1]);
    $entityDisplay->removeComponent('workbench_moderation_control');
    $entityDisplay->save();


    $this->adminUser = $this->DrupalCreateUser($this->permissions);
    $this->drupalLogin($this->adminUser);
  }

  /**
   * Test the parade edit (Geysir) functionality with AJAX.
   *
   * @throws \Behat\Mink\Exception\ElementNotFoundException
   * @throws \Behat\Mink\Exception\ResponseTextException
   * @throws \Behat\Mink\Exception\ExpectationException
   */
  public function testGeysir() {
    // Get the Mink stuff.
    $assert = $this->assertSession();
    $page = $this->getSession()->getPage();

    $node = $this->drupalGetNodeByTitle('Parade One Page Site Demo');
    $this->testNodeId = $node->id();

    $this->drupalGet('node/' . $this->testNodeId);
    $latestUrl = 'node/' . $this->testNodeId . '/latest';
    $assert->pageTextContains('One Page Site Demo no. 1');

    // Check Geysir links and functionality.
    $this->drupalGet($latestUrl);

    $selectorP1 = 'div[data-geysir-paragraph-id="1"]';
    $selectorP2 = 'div[data-geysir-paragraph-id="2"]';
    $selectorP3 = 'div[data-geysir-paragraph-id="3"]';
    // Tricky way for hover event.
    $this->getSession()->executeScript("jQuery('{$selectorP2}').addClass('hover');");
    $assert->pageTextContains('One Page Site Demo no. 1');

    // Check Geysir links on paragraph #2.
    $geysirLinks = [];
    $geysirLinksWrapper = $page->find('css', 'div[data-geysir-paragraph-id="2"] .geysir-field-paragraph-links');
    foreach (['Edit', 'Delete', 'Up', 'Down'] as $label) {
      $geysirLinks[$label] = $geysirLinksWrapper->findLink($label);
      $this->assertNotNull($geysirLinks[$label], "Geysir '" . $label . "' link exists for paragraph #2.");
    }

    // Open Geysir edit modal for paragraph #1 and cancel it.
    $assert->pageTextContains('Simple - One column - Clean');
    $this->getSession()->executeScript("jQuery('{$selectorP1}').addClass('hover');");
    $geysirLinks['Edit']->click();
    $assert->assertWaitOnAjaxRequest();
    $modalButtons = $page->find('css', '.ui-dialog .ui-dialog-buttonpane');
    $this->assertJsCondition("jQuery('.ui-dialog-titlebar').length > 0");
    $assert->responseContains('Edit section');
    $assert->responseContains('Simple - One column - Clean');
    $modalButtons->pressButton('Cancel');
    $assert->assertWaitOnAjaxRequest();

    // Open Geysir edit modal and edit title of paragraph #2.
    $this->getSession()->executeScript("jQuery('{$selectorP2}').addClass('hover');");
    $geysirLinks['Edit']->click();
    $assert->assertWaitOnAjaxRequest();
    $modalButtons = $page->find('css', '.ui-dialog .ui-dialog-buttonpane');
    $this->assertJsCondition("jQuery('.ui-dialog-titlebar').length > 0");
    $assert->responseContains('Edit section');
    $assert->responseContains('Simple - One column - Clean');
    $title = $page->findField('parade_title[0][value]');
    $title->setValue('Simple Geysir edited - One column - Clean');
    $modalButtons->pressButton('Save as draft');
    $assert->assertWaitOnAjaxRequest();
    $assert->pageTextContains('Simple Geysir edited - One column - Clean');
    $assert->pageTextNotContains('Simple - One column - Clean');

    // Check the original order - paragraph #2 is before the #3.
    $this->assertOrderInPage(['Simple Geysir edited - One column - Clean', 'Simple  - One column - Light Grey']);
    // Move paragraph #2 down.
    $this->getSession()->executeScript("jQuery('{$selectorP2}').addClass('hover');");
    $geysirLinks['Down']->click();
    $assert->assertWaitOnAjaxRequest();
    $this->getSession()->executeScript("jQuery('{$selectorP2}').removeClass('hover');");
    // Check that paragraph #2 is after the #3.
    $this->assertOrderInPage(['Simple  - One column - Light Grey', 'Simple Geysir edited - One column - Clean']);

    // Move paragraph #2 up.
    $this->getSession()->executeScript("jQuery('{$selectorP2}').addClass('hover');");
    $this->getSession()->getPage()->find('css', "{$selectorP2} .geysir-field-paragraph-links")->clickLink('Up');
    $assert->assertWaitOnAjaxRequest();
    // Check that paragraph #2 is after the #3.
    $this->assertOrderInPage(['Simple Geysir edited - One column - Clean', 'Simple  - One column - Light Grey']);

    // Open Geysir delete modal and cancel.
    $assert->pageTextContains('Simple - One column - Light Grey');
    $this->getSession()->executeScript("jQuery('{$selectorP3}').addClass('hover');");
    $page->find('css', "{$selectorP3} .geysir-field-paragraph-links")->clickLink('Delete');
    $assert->assertWaitOnAjaxRequest();
    $modalButtons = $page->find('css', '.ui-dialog .ui-dialog-buttonpane');
    $this->assertJsCondition("jQuery('.ui-dialog-titlebar').length > 0");
    $assert->responseContains('Delete section');
    $assert->responseContains('This action cannot be undone.');
    $modalButtons->pressButton('Cancel');
    $assert->assertWaitOnAjaxRequest();

    // Open Geysir delete modal and delete paragraph #3.
    $page->find('css', "{$selectorP3} .geysir-field-paragraph-links")->clickLink('Delete');
    $assert->assertWaitOnAjaxRequest();
    $modalButtons = $page->find('css', '.ui-dialog .ui-dialog-buttonpane');
    $this->assertJsCondition("jQuery('.ui-dialog-titlebar').length > 0");
    $assert->responseContains('Delete section');
    $assert->responseContains('This action cannot be undone.');
    $modalButtons->pressButton('Delete');
    $assert->assertWaitOnAjaxRequest();
    $assert->pageTextNotContains('Simple  - One column - Light Grey');

    // Check 'Inplace edit' link on paragraph #2.
    $geysirLinksWrapper = $page->find('css', 'div[data-geysir-paragraph-id="2"] .geysir-field-paragraph-links');
    $inplaceEditLink = $geysirLinksWrapper->find('css', '.parade-edit-quickedit .button');
    $this->assertNotNull($inplaceEditLink, "Geysir 'Inplace edit' link exists for paragraph #2.");

    // Open inplace editor for paragraph #2.
    $this->getSession()->executeScript("jQuery('{$selectorP2}').addClass('hover');");
    $inplaceEditLink->click();
    $assert->waitForElementVisible('css', '#quickedit-entity-toolbar');
    $ie = $page->find('css', 'div[data-geysir-paragraph-id="2"] .field--name-parade-title');
    $this->assertNotNull($ie, "Geysir 'Inplace edit' activated for paragraph #2.");
    $ie->click();
    $assert->waitForElement('css', 'div[data-geysir-paragraph-id="2"] .field--name-parade-title--wrapper.quickedit-editing');
    $this->getSession()->executeScript("jQuery('{$selectorP2} .field--name-parade-title').html('Simple quickedited - One column - Clean');");
    $this->getSession()->executeScript("jQuery('{$selectorP2} .field--name-parade-title').trigger('keyup');");
    $page->find('css', '#quickedit-entity-toolbar button.action-save')->click();
    $assert->assertWaitOnAjaxRequest();
  }

  /**
   * Asserts that several pieces of markup are in a given order in the page.
   *
   * @param string[] $items
   *   An ordered list of strings.
   *
   * @throws \Behat\Mink\Exception\ExpectationException
   *   When any of the given string is not found.
   *
   * @todo Remove this once https://www.drupal.org/node/2817657 is committed.
   */
  protected function assertOrderInPage(array $items) {
    $session = $this->getSession();
    $text = $session->getPage()->getHtml();
    $strings = [];
    foreach ($items as $item) {
      if (($pos = strpos($text, $item)) === FALSE) {
        throw new ExpectationException("Cannot find '$item' in the page", $session->getDriver());
      }
      $strings[$pos] = $item;
    }
    ksort($strings);
    $ordered = implode(', ', array_map(function ($item) {
      return "'$item'";
    }, $items));
    $this->assertSame($items, array_values($strings), "Found strings, ordered as: $ordered.");
  }

}
