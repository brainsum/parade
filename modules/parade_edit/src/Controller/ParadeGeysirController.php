<?php

namespace Drupal\parade_edit\Controller;

use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Ajax\HtmlCommand;

/**
 * Controller for up and down actions.
 */
class ParadeGeysirController extends ControllerBase {

  /**
   * Shift up a single paragraph.
   */
  public function up($parent_entity_type, $parent_entity_bundle, $parent_entity_revision, $field, $field_wrapper_id, $delta, $paragraph, $paragraph_revision, $js = 'nojs') {
    if ($js == 'ajax') {
      if ($delta > 0) {
        /* @var $entity \Drupal\node\Entity\Node */
        $entity = \Drupal::entityTypeManager()->getStorage('node')->loadRevision($parent_entity_revision);
        $paragraphs = $entity->$field->getValue();
        $paragraph = $paragraphs[$delta - 1];
        $paragraphs[$delta - 1] = $paragraphs[$delta];
        $paragraphs[$delta] = $paragraph;
        $entity->$field->setValue($paragraphs);

        // For creating new revision.
        $entity->setRevisionLogMessage('Move up a paragraph.');

        $entity->save();
      }

      $response = new AjaxResponse();
      // Refresh the paragraphs field.
      $response->addCommand(
        new HtmlCommand(
          '[data-geysir-field-paragraph-field-wrapper=' . $field_wrapper_id . ']',
          $entity->get($field)->view('default')));
      return $response;
    }

    return $this->t('Javascript is required for this functionality to work properly.');
  }

  /**
   * Shift down a single paragraph.
   */
  public function down($parent_entity_type, $parent_entity_bundle, $parent_entity_revision, $field, $field_wrapper_id, $delta, $paragraph, $paragraph_revision, $js = 'nojs') {
    if ($js == 'ajax') {
      /* @var $entity \Drupal\node\Entity\Node */
      $entity = \Drupal::entityTypeManager()->getStorage('node')->loadRevision($parent_entity_revision);
      $paragraphs = $entity->$field->getValue();
      if ($delta < (count($paragraphs) - 1)) {
        $paragraph = $paragraphs[$delta + 1];
        $paragraphs[$delta + 1] = $paragraphs[$delta];
        $paragraphs[$delta] = $paragraph;
        $entity->$field->setValue($paragraphs);

        // For creating new revision.
        $entity->setRevisionLogMessage('Move down a paragraph.');

        $entity->save();
      }

      $response = new AjaxResponse();
      // Refresh the paragraphs field.
      $response->addCommand(
        new HtmlCommand(
          '[data-geysir-field-paragraph-field-wrapper=' . $field_wrapper_id . ']',
          $entity->get($field)->view('default')));
      return $response;
    }

    return $this->t('Javascript is required for this functionality to work properly.');
  }

}
