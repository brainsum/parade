/**
 * @file
 * Allow autoplaying videos on mobile.
 *
 * @see  https://github.com/bfred-it/iphone-inline-video
 */

(function () {

	const makeVideoPlayableInline = require('iphone-inline-video');
	var video = document.querySelector('.background-video');
	makeVideoPlayableInline(video, !video.hasAttribute('muted'));

})()
