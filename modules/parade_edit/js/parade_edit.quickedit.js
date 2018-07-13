/**
 * @file
 * JavaScript for quick edit functionality in Geysir.
 */

(function ($, Drupal) {
  "use strict";

  var selectedParent = null;

  Drupal.behaviors.parade_edit = {
    attach(context) {
      $(".geysir-field-paragraph-wrapper.contextual-region, .field--name-parade-paragraphs .contextual-region").once('mouse-over').on('mouseover', function (event) {
        if ($(event.currentTarget).find(".contextual-region").find(event.target).length == 0) {
          if (selectedParent === null) {
            selectedParent = $(event.currentTarget);
            selectedParent.find(".contextual-links .quickedit > a").trigger('click');
          }
        }
      });
      $(".geysir-field-paragraph-wrapper.contextual-region, .field--name-parade-paragraphs .contextual-region").once('mouse-out').on('mouseout', function (event) {
        if ($(event.currentTarget).find(".contextual-region").find(event.target).length == 0) {
          //if (selectedParent !== $(event.currentTarget)) {
            $(".quickedit-toolbar-content .ops .action-cancel").trigger('click');
            selectedParent = null;
          //}
        }
      });
    }
  };

})(jQuery, Drupal);
