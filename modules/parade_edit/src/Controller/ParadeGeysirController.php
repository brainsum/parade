<?php

namespace Drupal\parade_edit\Controller;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Entity\EntityFieldManager;
use Drupal\geysir\Ajax\GeysirOpenModalDialogCommand;
use Drupal\geysir\Controller\GeysirModalController;
use Drupal\paragraphs\Entity\Paragraph;

/**
 * Controller for up and down actions.
 */
class ParadeGeysirController extends GeysirModalController {

  /**
   * The entity field manager.
   *
   * @var \Drupal\Core\Entity\EntityFieldManager
   */
  private $entityFieldManager;

  /**
   * {@inheritdoc}
   */
  public function __construct(EntityFieldManager $entityFieldManager) {
    parent::__construct($entityFieldManager);
    $this->entityFieldManager = $entityFieldManager;
  }

  /**
   * Override default to add empty button array.
   *
   * {@inheritdoc}
   */
  public function add($parent_entity_type, $parent_entity_bundle, $parent_entity_revision, $field, $field_wrapper_id, $delta, $paragraph, $paragraph_revision, $position, $js = 'nojs', $bundle = NULL) {
    if ($js == 'ajax') {
      $response = new AjaxResponse();
      $paragraph_title = $this->getParagraphTitle($parent_entity_type, $parent_entity_bundle, $field);

      if ($bundle) {
        $newParagraph = Paragraph::create(['type' => $bundle]);
        $form = $this->entityFormBuilder()->getForm($newParagraph, 'geysir_modal_add', []);

        $response->addCommand(new GeysirOpenModalDialogCommand($this->t('Add @paragraph_title', ['@paragraph_title' => $paragraph_title]), render($form), ["buttons" => []]));
      }
      else {
        $entity = $this->entityTypeManager()->getStorage($parent_entity_type)->loadRevision($parent_entity_revision);
        $bundle_fields = $this->entityFieldManager->getFieldDefinitions($parent_entity_type, $entity->bundle());
        $field_definition = $bundle_fields[$field];
        $bundles = $field_definition->getSetting('handler_settings')['target_bundles'];

        $routeParams = [
          'parent_entity_type'   => $parent_entity_type,
          'parent_entity_bundle' => $parent_entity_bundle,
          'parent_entity_revision' => $parent_entity_revision,
          'field'                => $field,
          'field_wrapper_id'     => $field_wrapper_id,
          'delta'                => $delta,
          'paragraph'            => $paragraph->id(),
          'paragraph_revision'   => $paragraph->getRevisionId(),
          'position'             => $position,
          'js'                   => $js,
        ];

        $form = \Drupal::formBuilder()->getForm('\Drupal\geysir\Form\GeysirModalParagraphAddSelectTypeForm', $routeParams, $bundles);
        $response->addCommand(new GeysirOpenModalDialogCommand($this->t('Add @paragraph_title', ['@paragraph_title' => $paragraph_title]), render($form), ["buttons" => []]));
      }

      return $response;
    }

    return $this->t('Javascript is required for this functionality to work properly.');
  }

}
