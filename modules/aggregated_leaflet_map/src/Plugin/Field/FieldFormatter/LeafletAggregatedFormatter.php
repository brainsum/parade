<?php

namespace Drupal\aggregated_leaflet_map\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\leaflet\Plugin\Field\FieldFormatter\LeafletDefaultFormatter;

/**
 * Plugin implementation of the 'leaflet_default' formatter.
 *
 * @FieldFormatter(
 *   id = "leaflet_formatter_aggregated",
 *   label = @Translation("Leaflet aggregated map"),
 *   field_types = {
 *     "geofield"
 *   }
 * )
 */
class LeafletAggregatedFormatter extends LeafletDefaultFormatter {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'source_field' => '',
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $formState) {
    $elements = parent::settingsForm($form, $formState);

    $bundleFields = $this->getBundleFieldDefinitions($form['#entity_type'], $form['#bundle']);
    $options = [];
    foreach ($form['#fields'] as $field) {
      if (!isset($bundleFields[$field])) {
        continue;
      }

      $options[$field] = $bundleFields[$field]->getLabel();
    }

    $elements['source_field'] = [
      '#title' => $this->t('Source field'),
      '#type' => 'select',
      '#options' => $options,
      '#default_value' => $this->getSetting('source_field'),
      '#required' => TRUE,
    ];

    return $elements;
  }

  /**
   * {@inheritdoc}
   *
   * This function is called from parent::view().
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $settings = $this->getSettings();
    $icon_url = $settings['icon']['icon_url'];

    $map = leaflet_map_get_info($settings['leaflet_map']);
    $map['settings']['zoom'] = isset($settings['zoom']) ? $settings['zoom'] : NULL;
    $map['settings']['minZoom'] = isset($settings['minZoom']) ? $settings['minZoom'] : NULL;
    $map['settings']['maxZoom'] = isset($settings['zoom']) ? $settings['maxZoom'] : NULL;

    // We collect all features in a single array.
    $aggregated_features = array();
    // Try to get the source_field from the entity.
    /** @var \Drupal\Core\Field\FieldItemList $sourceField */
    if ($items->getEntity()->hasField($settings['source_field'])) {
      $sourceField = $items->getEntity()->get($settings['source_field']);
    }

    foreach ($items as $delta => $item) {
      $features = leaflet_process_geofield($item->value);

      // If the sourceField is not found, provide a fallback popup.
      if (NULL === $sourceField) {
        $features[0]['popup'] = $this->t('Location @delta', ['@delta' => $delta]);
      }
      // Otherwise, get the value at $delta from the source field.
      else {
        $features[0]['popup'] = $sourceField->getValue()[$delta]['value'];
      }

      if (!empty($icon_url)) {
        foreach ($features as $key => $feature) {
          $features[$key]['icon'] = $settings['icon'];
        }
      }

      $aggregated_features[] = $features;
    }

    $elements = [];
    // We only display a single, aggregated map with every feature.
    $elements[0] = aggregated_leaflet_map_render_map($map, $aggregated_features, $settings['height'] . 'px');

    return $elements;
  }

  /**
   * Get the field definitions for an entity type's bundle.
   *
   * @param string $entityType
   *   The entity type.
   * @param string $bundle
   *   The bundle.
   *
   * @return \Drupal\Core\Field\FieldDefinitionInterface[]
   *   Array of field definitions.
   */
  private function getBundleFieldDefinitions($entityType, $bundle) {
    // @todo: Use dependency injection.
    /** @var \Drupal\Core\Entity\EntityFieldManagerInterface $fieldManager */
    $fieldManager = \Drupal::service('entity_field.manager');
    /** @var \Drupal\Core\Field\FieldDefinitionInterface[] $bundleFields */
    $bundleFields = $fieldManager->getFieldDefinitions(
      $entityType,
      $bundle
    );

    return $bundleFields;
  }

}
