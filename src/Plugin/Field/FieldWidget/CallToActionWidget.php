<?php

namespace Drupal\parade\Plugin\Field\FieldWidget;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\link\Plugin\Field\FieldWidget\LinkWidget;

/**
 * @FieldWidget(
 *   id = "link_cta",
 *   label = @Translation("Call to action"),
 *   description = @Translation("Offers CTA customization on links."),
 *   field_types = {
 *     "link"
 *   }
 * )
 */
class CallToActionWidget extends LinkWidget {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'open_on_new_tab' => FALSE,
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $element = parent::formElement($items, $delta, $element, $form, $form_state);

    if ($this->supportsExternalLinks()) {
      $item = $items[$delta];

      $options = $item->get('options')->getValue();
      $attributes = isset($options['attributes']) ? $options['attributes'] : [];

      $element['options']['open_on_new_tab'] = [
        '#type' => 'checkbox',
        '#title' => $this->t('Open on another tab'),
        '#default_value' => isset($options['open_on_new_tab']) ? (bool) $options['open_on_new_tab'] : $this->getSetting('open_on_new_tab'),
      ];
    }

    return $element;
  }

}
