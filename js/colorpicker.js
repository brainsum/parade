/**
 * @file
 * JavaScript code for Colorpickers.
 */
(function ($, Modernizr, Drupal) {
  'use strict';

  /**
   * Initialize Spectrum color picker with Drupal behaviours.
   *
   * @see http://bgrins.github.io/spectrum/
   * @type {Object}
   */
  Drupal.behaviors.paradeColorpicker = {
    attach: function (context, settings) {

      // If the browser supports `<input type="color">` use that instead.
      if (Modernizr.inputtypes.color) {
        return;
      }

      var $colorpickers = $('input[type="color"]');

      // @todo Make the options extendable by the theme.
      $colorpickers.spectrum({
        preferredFormat: 'hex',
        showInput: true,
        showPalette: false,
        allowEmpty: true,
        chooseText: Drupal.t('Ok'),
        cancelText: Drupal.t('Cancel'),
        hideAfterPaletteSelect: true,
        // @todo Load any colors defined in default theme.
        // Use drupalSettings if needed.
        // palette: ["red", "rgba(0, 255, 0, .5)", "rgb(0, 0, 255)"]
      });

      // Update the `<input type="color">` value after changing with Spectrum.
      // $colorpickers.on('change.spectrum', function (e, color) {
      //   $(e.target).val('#' + color.toHex());
      // });
    }
  };
})(jQuery, Modernizr, Drupal);
