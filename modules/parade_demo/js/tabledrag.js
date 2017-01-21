/**
 * @file
 * Modify default Drupal tabledrag functionality
 */

(function ($, Drupal) {

  'use strict';

  /**
   * Override Drupal's default, the only difference is,
   * that we pass a third arguments to the tableDrag constructor
   */
  Drupal.behaviors.tableDrag = {
    attach: function (context, settings) {
      function initTableDrag(table, base) {
        if (table.length) {
          // Create the new tableDrag instance. Save in the Drupal variable
          // to allow other scripts access to the object.
          Drupal.tableDrag[base] = new Drupal.tableDrag(table[0], settings.tableDrag[base], base);
        }
      }

      for (var base in settings.tableDrag) {
        if (settings.tableDrag.hasOwnProperty(base)) {
          initTableDrag($(context).find('#' + base).once('tabledrag'), base);
        }
      }
    }
  };

  // References to the original constructor and dragStart handler
  var TableDrag = Drupal.tableDrag;
  var _dragStart = TableDrag.prototype.dragStart;

  /**
   * Override Drupal's default constructor
   * We call the original, and replace the dragStart handler if it's the paragraph list
   */
  Drupal.tableDrag = function (table, tableSettings, base) {
    var tableDrag = new TableDrag(table, tableSettings);

    if (base.indexOf('field-paragraphs-values' === 0)) {
      tableDrag.dragStart = dragStart;
    }

    return tableDrag;
  };

  /**
   * The modified dragStart handler, which prevents dragging if services are open
   * otherwise call the original handler
   */
	var dragStart = function() {
    var numberOfOpenSections = this.$table.find('.paragraphs-subform').length;

    if (numberOfOpenSections) {
      return;
    }

    _dragStart.apply(this, arguments);
	};
})(jQuery, Drupal);