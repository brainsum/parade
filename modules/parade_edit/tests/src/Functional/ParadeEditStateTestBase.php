<?php

namespace Drupal\Tests\parade_edit\Functional;

use Drupal\Tests\BrowserTestBase;
use Drupal\Core\Entity\Entity\EntityViewDisplay;

/**
 * Defines a base class for moderation state tests.
 */
abstract class ParadeEditStateTestBase extends BrowserTestBase {

  /**
   * Profile to use.
   *
   * @var string
   */
  protected $profile = 'testing';

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
  ];

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = [
    'node',
    'content_translation',
    'parade_edit',
    'parade_demo',
  ];

  /**
   * Node type to create, test.
   *
   * @var string
   */
  protected $nodeTypeMachineName = 'parade_edit_content';

  /**
   * Sets the test up.
   */
  protected function setUp() {
    parent::setUp();
    $this->adminUser = $this->CreateUser($this->permissions);

    \Drupal::service('theme_handler')->install(['bartik', 'seven']);
    $this->config('system.theme')
      ->set('default', 'bartik')
      ->set('admin', 'seven')
      ->save();

    $this->drupalPlaceBlock('local_tasks_block', [
      'id' => 'tabs_block',
      'region' => 'content',
    ]);
  }

  /**
   * Creates a new node type.
   *
   * Enable workbench_moderation, remove workbench_moderation_control, add
   * parade_edit_moderation_control to view display.
   *
   * @param string $label
   *   The human-readable label of the type to create.
   * @param string $machineName
   *   The machine name of the type to create.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  protected function createNodeType($label, $machineName) {
    /** @var \Drupal\node\Entity\NodeType $node_type */
    $nodeType = $this->createContentType([
      'name' => $label,
      'type' => $machineName,
    ]);
    $nodeType->setThirdPartySetting('workbench_moderation', 'enabled', TRUE);
    $nodeType->setThirdPartySetting('workbench_moderation', 'allowed_moderation_states', [
      'draft',
      'published',
    ]);
    $nodeType->setThirdPartySetting('workbench_moderation', 'default_moderation_state', 'draft');
    $nodeType->save();

    $entityDisplay = EntityViewDisplay::load('node.' . $machineName . '.default');
    $entityDisplay->setComponent('parade_edit_moderation_control', ['weight' => -1]);
    $entityDisplay->removeComponent('workbench_moderation_control');
    $entityDisplay->save();
  }

}
