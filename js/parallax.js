/**
 * @file
 * JavaScript for paragraph type Header.
 */

(function ($) {
  'use strict';

  // https://github.com/markdalgleish/stellar.js#configuring-everything
  // .paragraph--type--parallax
  // $.ready(function() {
  //   $.stellar({
  //     scrollProperty: 'transform',
  //     positionProperty: 'transform',
  //     parallaxElements: false,
  //     horizontalScrolling: false,
  //   });
  // });

  var rellax = new Rellax('.bg-parallax', { speed: -4 });

})(jQuery);
