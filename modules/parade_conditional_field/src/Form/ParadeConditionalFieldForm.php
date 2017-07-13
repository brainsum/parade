<?php

namespace Drupal\parade_conditional_field\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\classy_paragraphs\Entity\ClassyParagraphsStyle;
use Drupal\Core\Url;

/**
 * Class ParadeConditionalFieldForm.
 *
 * @package Drupal\parade_conditional_field\Form
 */
class ParadeConditionalFieldForm extends EntityForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    /** @var \Drupal\parade_conditional_field\Entity\ParadeConditionalField $condition */
    $condition = $this->entity;
    $condition_id = $condition->id();
    $paragraphs_type = $condition->getBundle();
    $layouts = $condition->getLayouts();
    $view_mode = $condition->getViewMode();
    $classes = $condition->getClasses();

    if ($condition->isNew()) {
      $route_match = \Drupal::service('current_route_match');
      $paragraphs_type = $route_match->getParameter('paragraphs_type');
      $view_mode = 'default';

      if ($last_condition = array_values(\Drupal::entityTypeManager()->getStorage('parade_conditional_field')->loadByProperties(['bundle' => $paragraphs_type]))) {
        $last_condition = end($last_condition);
        $last_id = $last_condition->getNumericId();
      }
      else {
        $last_id = 0;
      }
      $condition_id = $route_match->getParameter('paragraphs_type') . '_' . ($last_id + 1);
    }

    $bundles = \Drupal::service('entity_type.bundle.info')->getBundleInfo('paragraph');
    $bundle_options = [];
    foreach ($bundles as $bundle_name => $bundle_info) {
      $bundle_options[$bundle_name] = $bundle_info['label'];
    }
    natsort($bundle_options);

    $form['bundle'] = [
      '#type' => 'hidden',
      '#value' => $paragraphs_type,
    ];

    $form['id'] = [
      '#type' => 'hidden',
      '#value' => $condition_id,
    ];

    $color_options = $layout_options = [];
    foreach (ClassyParagraphsStyle::loadMultiple() as $bundle_name => $bundle_info) {
      if (strstr($bundle_name, 'layout_')) {
        $layout_options[$bundle_name] = $bundle_info->label();
      }
      elseif (strstr($bundle_name, 'color_')) {
        $color_options[$bundle_name] = $bundle_info->label();
      }
    }

    $form['layouts'] = [
      '#title' => t('Trigger on Layout value(s)'),
      '#type' => 'select',
      '#multiple' => TRUE,
      '#size' => count($layout_options) > 15 ? 15 : count($layout_options),
      '#options' => $layout_options,
      '#default_value' => $layouts,
      '#description' => t('Trigger conditions when Layout have this value.'),
      '#required' => TRUE,
    ];

    $entity_type = 'paragraph';
    $bundle = $paragraphs_type;
    $view_mode_options = [];

    // Get all view modes for the current bundle.
    $view_modes = \Drupal::service('entity_display.repository')->getViewModeOptionsByBundle($entity_type, $bundle);

    // Get field settings, check enabled view modes.
    $bundle_fields = \Drupal::getContainer()->get('entity_field.manager')->getFieldDefinitions($entity_type, $bundle);
    if (isset($bundle_fields['parade_view_mode']) && $field_definition = $bundle_fields['parade_view_mode']) {
      $field_settings = $field_definition->getSetting('view_modes');
    }

    foreach (array_keys($view_modes) as $view_mode_setting) {
      if (isset($field_settings[$view_mode_setting]['enable']) && $field_settings[$view_mode_setting]['enable']) {
        continue;
      }
      unset($view_modes[$view_mode_setting]);
    }
    // Show all view modes when no view modes are enabled.
    if (!count($view_modes)) {
      $view_modes = \Drupal::service('entity_display.repository')->getViewModeOptionsByBundle($entity_type, $bundle);
    }

    // Remove Preview view mode - it's used only for preview.
    unset($view_modes['preview']);

    foreach ($view_modes as $view_mode_id => $view_mode_label) {
      $view_mode_options[$view_mode_id] = $view_mode_label . ' (' . $view_mode_id . ')';
    }
    $form['view_mode'] = [
      '#title' => t('View mode'),
      '#type' => 'radios',
      '#options' => $view_mode_options,
      '#default_value' => $view_mode,
      '#description' => t('Paragraph will be rendered with this view mode.'),
      '#required' => TRUE,
    ];

    $form['classes'] = [
      '#title' => t('Restrict to the selected classy paragraph styles'),
      '#type' => 'select',
      '#multiple' => TRUE,
      '#size' => count($color_options) > 15 ? 15 : count($color_options),
      '#options' => $color_options,
      '#default_value' => $classes,
      '#description' => t('If no classy paragraph style are selected, all will be allowed.'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  protected function actions(array $form, FormStateInterface $form_state) {
    $actions = parent::actions($form, $form_state);

    if (!$paragraphs_type = $this->entity->getBundle()) {
      $route_match = \Drupal::service('current_route_match');
      $paragraphs_type = $route_match->getParameter('paragraphs_type');
    }

    $url = new Url("entity.paragraph.parade_conditional_field", ['paragraphs_type' => $paragraphs_type]);
    $actions['cancel'] = [
      '#type' => 'link',
      '#title' => $this->t('Cancel'),
      '#attributes' => ['class' => ['button']],
      '#url' => $url,
      '#cache' => [
        'contexts' => [
          'url.query_args:destination',
        ],
      ],
    ];
    return $actions;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $condition = $this->entity;
    $status = $condition->save();

    switch ($status) {
      case SAVED_NEW:
        drupal_set_message($this->t('Created the %bundle #%numeric_id condition.', [
          '%bundle' => $condition->getBundle(),
          '%numeric_id' => $condition->getNumericId(),
        ]));
        break;

      default:
        drupal_set_message($this->t('Saved the %bundle #%numeric_id condition.', [
          '%bundle' => $condition->getBundle(),
          '%numeric_id' => $condition->getNumericId(),
        ]));
    }

    $form_state->setRedirectUrl(new Url("entity.paragraph.parade_conditional_field", ['paragraphs_type' => $condition->getBundle()]));
  }

}
