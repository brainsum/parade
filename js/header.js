/**
 * @file
 * JavaScript for paragraph type Header.
 */

(function (inlineVideo, Rellax, drupalSettings) {
  'use strict';

  // @todo get this from drupalSettings.
  // @todo use hook_page_attachments().
  var background = 'video';

  // Allow autoplaying videos on mobile.
  if (background === 'video') {
    var video = document.querySelector('.background-video');
    inlineVideo(video, !video.hasAttribute('muted'));
  }

  else if (background === 'image') {
    new Rellax('.parallax');
  }

})(inlineVideo, Rellax, drupalSettings);
