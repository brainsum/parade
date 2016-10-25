<?php

namespace Drupal\parade\Plugin\Field\FieldFormatter;

use Drupal\entity_reference_revisions\Plugin\Field\FieldFormatter\EntityReferenceRevisionsEntityFormatter;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'entity reference rendered entity' formatter.
 *
 * @FieldFormatter(
 *   id = "parade_entity_reference_revisions_entity_view",
 *   label = @Translation("Parade Rendered entity"),
 *   description = @Translation("Display the referenced entities rendered by entity_view()."),
 *   field_types = {
 *     "entity_reference_revisions"
 *   }
 * )
 */
class ParadeEntityReferenceEntityFormatter extends EntityReferenceRevisionsEntityFormatter {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return array(
      'view_mode' => 'default_custom',
      'link' => FALSE,
    ) + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $options = ['default_custom' => $this->t('Default/Custom')] + $this->entityDisplayRepository->getViewModeOptions($this->getFieldSetting('target_type'));
    $elements['view_mode'] = array(
      '#type' => 'select',
      '#options' => $options,
      '#title' => $this->t('View mode'),
      '#default_value' => $this->getSetting('view_mode'),
      '#required' => TRUE,
    );

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = array();

    $view_modes = ['default_custom' => $this->t('Default/Custom')] + $this->entityDisplayRepository->getViewModeOptions($this->getFieldSetting('target_type'));
    $view_mode = $this->getSetting('view_mode');
    $summary[] = $this->t('Rendered as @mode', array('@mode' => isset($view_modes[$view_mode]) ? $view_modes[$view_mode] : $view_mode));

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $view_mode = $this->getSetting('view_mode');
    if ($view_mode === 'default_custom') {
      $view_mode = 'default';
      $field = 'field_text_boxes_layout';
      $entity = $items->getEntity();
      // 5 - 'rounded_image'.
      if ($entity->hasField($field) && $entity->{$field}->value == 5) {
        $view_mode = 'custom';
      }
    }

    $elements = array();

    foreach ($this->getEntitiesToView($items, $langcode) as $delta => $entity) {
      // Protect ourselves from recursive rendering.
      static $depth = 0;
      $depth++;
      if ($depth > 20) {
        $this->loggerFactory->get('entity')->error('Recursive rendering detected when rendering entity @entity_type @entity_id. Aborting rendering.', array('@entity_type' => $entity->getEntityTypeId(), '@entity_id' => $entity->id()));
        return $elements;
      }
      $view_builder = \Drupal::entityTypeManager()->getViewBuilder($entity->getEntityTypeId());
      $elements[$delta] = $view_builder->view($entity, $view_mode, $entity->language()->getId());

      // Add a resource attribute to set the mapping property's value to the
      // entity's url. Since we don't know what the markup of the entity will
      // be, we shouldn't rely on it for structured data such as RDFa.
      if (!empty($items[$delta]->_attributes) && !$entity->isNew() && $entity->hasLinkTemplate('canonical')) {
        $items[$delta]->_attributes += array('resource' => $entity->toUrl()->toString());
      }
      $depth = 0;
    }

    return $elements;
  }

}
