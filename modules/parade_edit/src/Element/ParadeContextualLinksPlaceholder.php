<?php

namespace Drupal\parade_edit\Element;

use Drupal\Core\Template\Attribute;
use Drupal\contextual\Element\ContextualLinksPlaceholder;
use Drupal\Component\Render\FormattableMarkup;

/**
 * Provides a contextual_links_placeholder element.
 *
 * @RenderElement("parade_contextual_links_placeholder")
 */
class ParadeContextualLinksPlaceholder extends ContextualLinksPlaceholder {

  /**
   * Pre-render callback: Renders a contextual links placeholder into #markup.
   *
   * Renders an empty (hence invisible) placeholder div with a data-attribute
   * that contains an identifier ("contextual id"), which allows the JavaScript
   * of the drupal.contextual-links library to dynamically render contextual
   * links.
   * Add 'visually-hidden' class on custom routes to hide contextual menus.
   *
   * @param array $element
   *   A structured array with #id containing a "contextual id".
   *
   * @return array
   *   The passed-in element with a contextual link placeholder in '#markup'.
   *
   * @see ContextualLinksPlaceholder::preRenderPlaceholder()
   */
  public static function preRenderPlaceholder(array $element) {
    $attributes['data-contextual-id'] = $element['#id'];
    $routeName = \Drupal::routeMatch()->getRouteName();
    if (in_array($routeName, ['entity.node.latest_version']) || strstr($routeName, 'geysir.modal.')
      || strstr($routeName, 'parade_edit.modal.')) {
      //$attributes['class'] = 'visually-hidden';
    }
    $element['#markup'] = new FormattableMarkup('<div@attributes></div>', ['@attributes' => new Attribute($attributes)]);

    return $element;
  }

}
