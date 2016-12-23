<?php

namespace Drupal\aggregated_leaflet_map\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
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

    $elements = array();
    // We collect all features in a single array.
    $aggregated_features = array();
    foreach ($items as $item) {

      $features = leaflet_process_geofield($item->value);

      // If only a single feature, set the popup content to the entity title.
      if ($settings['popup'] && count($items) === 1) {
        $features[0]['popup'] = $items->getEntity()->label();
      }
      if (!empty($icon_url)) {
        foreach ($features as $key => $feature) {
          $features[$key]['icon'] = $settings['icon'];
        }
      }

      $aggregated_features[] = $features;
    }

    // We only display a single, aggregated map with every feature.
    $elements[0] = aggregated_leaflet_map_render_map($map, $aggregated_features, $settings['height'] . 'px');

    return $elements;
  }

}
