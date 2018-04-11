<?php

namespace Drupal\parade_edit\Form;

use Drupal\geysir\Form\GeysirModalParagraphForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax\ReplaceCommand;

/**
 * Functionality to edit a paragraph through a modal.
 */
class ParadeEditGeysirModalParagraphForm extends GeysirModalParagraphForm {

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);

    $submit = &$form['actions']['submit'];
    $submit['#ajax']['callback'] = '::ajaxSaveM';

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function ajaxSaveM(array $form, FormStateInterface $form_state) {
    $response = parent::ajaxSave($form, $form_state);

    if (!$form_state->getErrors()) {
      $route_match = $this->getRouteMatch();
      $parent_entity_type = $route_match->getParameter('parent_entity_type');
      $temporary_data = $form_state->getTemporary();
      $parent_entity_revision = isset($temporary_data['parent_entity_revision']) ?
        $temporary_data['parent_entity_revision'] :
        $route_match->getParameter('parent_entity_revision');
      $parent_entity_revision = \Drupal::entityManager()
        ->getStorage($parent_entity_type)
        ->loadRevision($parent_entity_revision);

      $moderationForm = \Drupal::formBuilder()
        ->getForm(EntityModerationForm::class, $parent_entity_revision);
      $response->addCommand(
        new ReplaceCommand(
          '#parade-edit-moderation-wrapper',
          $moderationForm));
    }

    return $response;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $route_match = $this->getRouteMatch();
    $parent_entity_type = $route_match->getParameter('parent_entity_type');
    $parent_entity_revision = $route_match->getParameter('parent_entity_revision');
    $field = $route_match->getParameter('field');
    $delta = $route_match->getParameter('delta');

    $this->entity->setNewRevision(TRUE);
    $this->entity->save();

    $parent_entity_revision = $this->entityTypeManager->getStorage($parent_entity_type)->loadRevision($parent_entity_revision);

    $parent_entity_revision->get($field)->get($delta)->setValue([
      'target_id' => $this->entity->id(),
      'target_revision_id' => $this->entity->getRevisionId(),
    ]);

    // Set revision log, revision creationTime, revision user.
    $parent_entity_revision->revision_log = $this->t('Paragraph (ID: @pid) changed with Geysir.', ['@pid' => $this->entity->id()]);
    $parent_entity_revision->setRevisionCreationTime(\Drupal::time()->getRequestTime());
    $parent_entity_revision->setRevisionUserId(\Drupal::currentUser()->id());
    $save_status = $parent_entity_revision->save();

    $form_state->setTemporary(['parent_entity_revision' => $parent_entity_revision->getRevisionId()]);

    return $save_status;
  }

}
