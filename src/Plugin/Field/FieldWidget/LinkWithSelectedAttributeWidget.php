<?php

namespace Drupal\parade\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\link\Plugin\Field\FieldWidget\LinkWidget;

/**
 * @FieldWidget(
 *   id = "link_with_selected_attribute",
 *   label = @Translation("Link (with selected attribute)"),
 *   description = @Translation("A link with the selected attribute."),
 *   field_types = {
 *     "link"
 *   }
 * )
 */
class LinkWithSelectedAttributeWidget extends LinkWidget {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $element = parent::formElement($items, $delta, $element, $form, $form_state);
    // Add each of the enabled attributes.
    // @todo move this to plugins that nominate form and label.

    $attribute = 'data-selected';

    $options = $items[$delta]->get('options')->getValue();
    $attributes = isset($options['attributes']) ? $options['attributes'] : [];

    $element['options']['attributes'][$attribute] = [
      '#type' => 'checkbox',
      '#title' => $this->t("Highlighted"),
      '#default_value' => !empty($attributes[$attribute]) ? TRUE : FALSE,
    ];

    return $element;
  }

}
