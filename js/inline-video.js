/**
 * @file
 * JavaScript for paragraph type Header.
 */

(function (makeVideoPlayableInline) {
  'use strict';

  var video = document.querySelector('.background-video');
  makeVideoPlayableInline(video, !video.hasAttribute('muted'));

})(makeVideoPlayableInline);
