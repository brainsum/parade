/**
 * @file
 * JavaScript for paragraph type Header.
 */

(function (inlineVideo, Rellax, drupalSettings) {
  'use strict';

  // Allow autoplaying videos on mobile.
  // @todo use hook_page_attachments().
  if ('background is video according to drupalSettings') {
    var video = document.querySelector('.background-video');
    inlineVideo(video, !video.hasAttribute('muted'));
  }

  else if ('background is image according to drupalSettings') {
    var parallax = new Rellax('.parallax');
  }

})(inlineVideo, Rellax, drupalSettings)
