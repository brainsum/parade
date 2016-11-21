/**
 * @file
 * JavaScript for paragraph type Header.
 */

(function (makeVideoPlayableInline) {
  'use strict';

  var video = document.querySelector('.bg-video');
  makeVideoPlayableInline(video, !video.hasAttribute('muted'));

})(makeVideoPlayableInline);
