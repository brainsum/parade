(function (exports) {
'use strict';

var index$1 = typeof Symbol === 'undefined' ? function (description) {
	return '@' + (description || '@') + Math.random();
} : Symbol;

/*! npm.im/intervalometer */
function intervalometer(cb, request, cancel, requestParameter) {
	var requestId;
	var previousLoopTime;
	function loop(now) {
		// must be requested before cb() because that might call .stop()
		requestId = request(loop, requestParameter);

		// called with "ms since last call". 0 on start()
		cb(now - (previousLoopTime || now));

		previousLoopTime = now;
	}
	return {
		start: function start() {
			if (!requestId) { // prevent double starts
				loop(0);
			}
		},
		stop: function stop() {
			cancel(requestId);
			requestId = null;
			previousLoopTime = 0;
		}
	};
}

function frameIntervalometer(cb) {
	return intervalometer(cb, requestAnimationFrame, cancelAnimationFrame);
}

/*! npm.im/iphone-inline-video */
function preventEvent(element, eventName, toggleProperty, preventWithProperty) {
	function handler(e) {
		if (Boolean(element[toggleProperty]) === Boolean(preventWithProperty)) {
			e.stopImmediatePropagation();
		}
		delete element[toggleProperty];
	}
	element.addEventListener(eventName, handler, false);

	// Return handler to allow to disable the prevention. Usage:
	// const preventionHandler = preventEvent(el, 'click');
	// el.removeEventHandler('click', preventionHandler);
	return handler;
}

function proxyProperty(object, propertyName, sourceObject, copyFirst) {
	function get() {
		return sourceObject[propertyName];
	}
	function set(value) {
		sourceObject[propertyName] = value;
	}

	if (copyFirst) {
		set(object[propertyName]);
	}

	Object.defineProperty(object, propertyName, {get: get, set: set});
}

function proxyEvent(object, eventName, sourceObject) {
	sourceObject.addEventListener(eventName, function () { return object.dispatchEvent(new Event(eventName)); });
}

function dispatchEventAsync(element, type) {
	Promise.resolve().then(function () {
		element.dispatchEvent(new Event(type));
	});
}

// iOS 10 adds support for native inline playback + silent autoplay
var isWhitelisted = /iPhone|iPod/i.test(navigator.userAgent) && !matchMedia('(-webkit-video-playable-inline)').matches;

var ಠ = index$1();
var ಠevent = index$1();
var ಠplay = index$1('nativeplay');
var ಠpause = index$1('nativepause');

/**
 * UTILS
 */

function getAudioFromVideo(video) {
	var audio = new Audio();
	proxyEvent(video, 'play', audio);
	proxyEvent(video, 'playing', audio);
	proxyEvent(video, 'pause', audio);
	audio.crossOrigin = video.crossOrigin;

	// 'data:' causes audio.networkState > 0
	// which then allows to keep <audio> in a resumable playing state
	// i.e. once you set a real src it will keep playing if it was if .play() was called
	audio.src = video.src || video.currentSrc || 'data:';

	// if (audio.src === 'data:') {
	//   TODO: wait for video to be selected
	// }
	return audio;
}

var lastRequests = [];
var requestIndex = 0;
var lastTimeupdateEvent;

function setTime(video, time, rememberOnly) {
	// allow one timeupdate event every 200+ ms
	if ((lastTimeupdateEvent || 0) + 200 < Date.now()) {
		video[ಠevent] = true;
		lastTimeupdateEvent = Date.now();
	}
	if (!rememberOnly) {
		video.currentTime = time;
	}
	lastRequests[++requestIndex % 3] = time * 100 | 0 / 100;
}

function isPlayerEnded(player) {
	return player.driver.currentTime >= player.video.duration;
}

function update(timeDiff) {
	var player = this;
	if (player.video.readyState >= player.video.HAVE_FUTURE_DATA) {
		if (!player.hasAudio) {
			player.driver.currentTime = player.video.currentTime + ((timeDiff * player.video.playbackRate) / 1000);
			if (player.video.loop && isPlayerEnded(player)) {
				player.driver.currentTime = 0;
			}
		}
		setTime(player.video, player.driver.currentTime);
	} else if (player.video.networkState === player.video.NETWORK_IDLE && !player.video.buffered.length) {
		// this should happen when the source is available but:
		// - it's potentially playing (.paused === false)
		// - it's not ready to play
		// - it's not loading
		// If it hasAudio, that will be loaded in the 'emptied' handler below
		player.video.load();
	}

	// console.assert(player.video.currentTime === player.driver.currentTime, 'Video not updating!');

	if (player.video.ended) {
		delete player.video[ಠevent]; // allow timeupdate event
		player.video.pause(true);
	}
}

/**
 * METHODS
 */

function play() {
	var video = this;
	var player = video[ಠ];

	// if it's fullscreen, use the native player
	if (video.webkitDisplayingFullscreen) {
		video[ಠplay]();
		return;
	}

	if (player.driver.src !== 'data:' && player.driver.src !== video.src) {
		setTime(video, 0, true);
		player.driver.src = video.src;
	}

	if (!video.paused) {
		return;
	}
	player.paused = false;

	if (!video.buffered.length) {
		// .load() causes the emptied event
		// the alternative is .play()+.pause() but that triggers play/pause events, even worse
		// possibly the alternative is preventing this event only once
		video.load();
	}

	player.driver.play();
	player.updater.start();

	if (!player.hasAudio) {
		dispatchEventAsync(video, 'play');
		if (player.video.readyState >= player.video.HAVE_ENOUGH_DATA) {
			dispatchEventAsync(video, 'playing');
		}
	}
}
function pause(forceEvents) {
	var video = this;
	var player = video[ಠ];

	player.driver.pause();
	player.updater.stop();

	// if it's fullscreen, the developer the native player.pause()
	// This is at the end of pause() because it also
	// needs to make sure that the simulation is paused
	if (video.webkitDisplayingFullscreen) {
		video[ಠpause]();
	}

	if (player.paused && !forceEvents) {
		return;
	}

	player.paused = true;
	if (!player.hasAudio) {
		dispatchEventAsync(video, 'pause');
	}
	if (video.ended) {
		video[ಠevent] = true;
		dispatchEventAsync(video, 'ended');
	}
}

/**
 * SETUP
 */

function addPlayer(video, hasAudio) {
	var player = video[ಠ] = {};
	player.paused = true; // track whether 'pause' events have been fired
	player.hasAudio = hasAudio;
	player.video = video;
	player.updater = frameIntervalometer(update.bind(player));

	if (hasAudio) {
		player.driver = getAudioFromVideo(video);
	} else {
		video.addEventListener('canplay', function () {
			if (!video.paused) {
				dispatchEventAsync(video, 'playing');
			}
		});
		player.driver = {
			src: video.src || video.currentSrc || 'data:',
			muted: true,
			paused: true,
			pause: function () {
				player.driver.paused = true;
			},
			play: function () {
				player.driver.paused = false;
				// media automatically goes to 0 if .play() is called when it's done
				if (isPlayerEnded(player)) {
					setTime(video, 0);
				}
			},
			get ended() {
				return isPlayerEnded(player);
			}
		};
	}

	// .load() causes the emptied event
	video.addEventListener('emptied', function () {
		var wasEmpty = !player.driver.src || player.driver.src === 'data:';
		if (player.driver.src && player.driver.src !== video.src) {
			setTime(video, 0, true);
			player.driver.src = video.src;
			// playing videos will only keep playing if no src was present when .play()’ed
			if (wasEmpty) {
				player.driver.play();
			} else {
				player.updater.stop();
			}
		}
	}, false);

	// stop programmatic player when OS takes over
	video.addEventListener('webkitbeginfullscreen', function () {
		if (!video.paused) {
			// make sure that the <audio> and the syncer/updater are stopped
			video.pause();

			// play video natively
			video[ಠplay]();
		} else if (hasAudio && !player.driver.buffered.length) {
			// if the first play is native,
			// the <audio> needs to be buffered manually
			// so when the fullscreen ends, it can be set to the same current time
			player.driver.load();
		}
	});
	if (hasAudio) {
		video.addEventListener('webkitendfullscreen', function () {
			// sync audio to new video position
			player.driver.currentTime = video.currentTime;
			// console.assert(player.driver.currentTime === video.currentTime, 'Audio not synced');
		});

		// allow seeking
		video.addEventListener('seeking', function () {
			if (lastRequests.indexOf(video.currentTime * 100 | 0 / 100) < 0) {
				player.driver.currentTime = video.currentTime;
			}
		});
	}
}

function overloadAPI(video) {
	var player = video[ಠ];
	video[ಠplay] = video.play;
	video[ಠpause] = video.pause;
	video.play = play;
	video.pause = pause;
	proxyProperty(video, 'paused', player.driver);
	proxyProperty(video, 'muted', player.driver, true);
	proxyProperty(video, 'playbackRate', player.driver, true);
	proxyProperty(video, 'ended', player.driver);
	proxyProperty(video, 'loop', player.driver, true);
	preventEvent(video, 'seeking');
	preventEvent(video, 'seeked');
	preventEvent(video, 'timeupdate', ಠevent, false);
	preventEvent(video, 'ended', ಠevent, false); // prevent occasional native ended events
}

function enableInlineVideo(video, hasAudio, onlyWhitelisted) {
	if ( hasAudio === void 0 ) { hasAudio = true; }
	if ( onlyWhitelisted === void 0 ) { onlyWhitelisted = true; }

	if ((onlyWhitelisted && !isWhitelisted) || video[ಠ]) {
		return;
	}
	addPlayer(video, hasAudio);
	overloadAPI(video);
	video.classList.add('IIV');
	if (!hasAudio && video.autoplay) {
		video.play();
	}
	if (!/iPhone|iPod|iPad/.test(navigator.platform)) {
		console.warn('iphone-inline-video is not guaranteed to work in emulated environments');
	}
}

enableInlineVideo.isWhitelisted = isWhitelisted;



var iphoneInlineVideo_esModules = Object.freeze({
	default: enableInlineVideo
});

var require$$0 = ( iphoneInlineVideo_esModules && iphoneInlineVideo_esModules['default'] ) || iphoneInlineVideo_esModules;

/**
 * @file
 * Allow autoplaying videos on mobile.
 *
 * @see  https://github.com/bfred-it/iphone-inline-video
 */

(function () {

	var makeVideoPlayableInline = require$$0;
	var video = document.querySelector('.background-video');
	makeVideoPlayableInline(video, !video.hasAttribute('muted'));

})();

}((this.LaravelElixirBundle = this.LaravelElixirBundle || {})));
//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjpudWxsLCJzb3VyY2VzIjpbIkQ6L2RldmRlc2t0b3AvdGNzLmxvYy93ZWIvdGhlbWVzL3RpZXRvX2FkbWluL25vZGVfbW9kdWxlcy9wb29yLW1hbnMtc3ltYm9sL2Rpc3QvcG9vci1tYW5zLXN5bWJvbC5lcy1tb2R1bGVzLmpzIiwiRDovZGV2ZGVza3RvcC90Y3MubG9jL3dlYi90aGVtZXMvdGlldG9fYWRtaW4vbm9kZV9tb2R1bGVzL2ludGVydmFsb21ldGVyL2Rpc3QvaW50ZXJ2YWxvbWV0ZXIuZXMtbW9kdWxlcy5qcyIsIkQ6L2RldmRlc2t0b3AvdGNzLmxvYy93ZWIvdGhlbWVzL3RpZXRvX2FkbWluL25vZGVfbW9kdWxlcy9pcGhvbmUtaW5saW5lLXZpZGVvL2Rpc3QvaXBob25lLWlubGluZS12aWRlby5lcy1tb2R1bGVzLmpzIiwiRDovZGV2ZGVza3RvcC90Y3MubG9jL3dlYi90aGVtZXMvdGlldG9fYWRtaW4vc3JjL3NjcmlwdHMvaXBob25lLWlubGluZS12aWRlby5qcyJdLCJzb3VyY2VzQ29udGVudCI6WyJ2YXIgaW5kZXggPSB0eXBlb2YgU3ltYm9sID09PSAndW5kZWZpbmVkJyA/IGZ1bmN0aW9uIChkZXNjcmlwdGlvbikge1xuXHRyZXR1cm4gJ0AnICsgKGRlc2NyaXB0aW9uIHx8ICdAJykgKyBNYXRoLnJhbmRvbSgpO1xufSA6IFN5bWJvbDtcblxuZXhwb3J0IGRlZmF1bHQgaW5kZXg7IiwiLyohIG5wbS5pbS9pbnRlcnZhbG9tZXRlciAqL1xuZnVuY3Rpb24gaW50ZXJ2YWxvbWV0ZXIoY2IsIHJlcXVlc3QsIGNhbmNlbCwgcmVxdWVzdFBhcmFtZXRlcikge1xuXHR2YXIgcmVxdWVzdElkO1xuXHR2YXIgcHJldmlvdXNMb29wVGltZTtcblx0ZnVuY3Rpb24gbG9vcChub3cpIHtcblx0XHQvLyBtdXN0IGJlIHJlcXVlc3RlZCBiZWZvcmUgY2IoKSBiZWNhdXNlIHRoYXQgbWlnaHQgY2FsbCAuc3RvcCgpXG5cdFx0cmVxdWVzdElkID0gcmVxdWVzdChsb29wLCByZXF1ZXN0UGFyYW1ldGVyKTtcblxuXHRcdC8vIGNhbGxlZCB3aXRoIFwibXMgc2luY2UgbGFzdCBjYWxsXCIuIDAgb24gc3RhcnQoKVxuXHRcdGNiKG5vdyAtIChwcmV2aW91c0xvb3BUaW1lIHx8IG5vdykpO1xuXG5cdFx0cHJldmlvdXNMb29wVGltZSA9IG5vdztcblx0fVxuXHRyZXR1cm4ge1xuXHRcdHN0YXJ0OiBmdW5jdGlvbiBzdGFydCgpIHtcblx0XHRcdGlmICghcmVxdWVzdElkKSB7IC8vIHByZXZlbnQgZG91YmxlIHN0YXJ0c1xuXHRcdFx0XHRsb29wKDApO1xuXHRcdFx0fVxuXHRcdH0sXG5cdFx0c3RvcDogZnVuY3Rpb24gc3RvcCgpIHtcblx0XHRcdGNhbmNlbChyZXF1ZXN0SWQpO1xuXHRcdFx0cmVxdWVzdElkID0gbnVsbDtcblx0XHRcdHByZXZpb3VzTG9vcFRpbWUgPSAwO1xuXHRcdH1cblx0fTtcbn1cblxuZnVuY3Rpb24gZnJhbWVJbnRlcnZhbG9tZXRlcihjYikge1xuXHRyZXR1cm4gaW50ZXJ2YWxvbWV0ZXIoY2IsIHJlcXVlc3RBbmltYXRpb25GcmFtZSwgY2FuY2VsQW5pbWF0aW9uRnJhbWUpO1xufVxuXG5mdW5jdGlvbiB0aW1lckludGVydmFsb21ldGVyKGNiLCBkZWxheSkge1xuXHRyZXR1cm4gaW50ZXJ2YWxvbWV0ZXIoY2IsIHNldFRpbWVvdXQsIGNsZWFyVGltZW91dCwgZGVsYXkpO1xufVxuXG5leHBvcnQgeyBpbnRlcnZhbG9tZXRlciwgZnJhbWVJbnRlcnZhbG9tZXRlciwgdGltZXJJbnRlcnZhbG9tZXRlciB9OyIsIi8qISBucG0uaW0vaXBob25lLWlubGluZS12aWRlbyAqL1xuaW1wb3J0IFN5bWJvbCBmcm9tICdwb29yLW1hbnMtc3ltYm9sJztcbmltcG9ydCB7IGZyYW1lSW50ZXJ2YWxvbWV0ZXIgfSBmcm9tICdpbnRlcnZhbG9tZXRlcic7XG5cbmZ1bmN0aW9uIHByZXZlbnRFdmVudChlbGVtZW50LCBldmVudE5hbWUsIHRvZ2dsZVByb3BlcnR5LCBwcmV2ZW50V2l0aFByb3BlcnR5KSB7XG5cdGZ1bmN0aW9uIGhhbmRsZXIoZSkge1xuXHRcdGlmIChCb29sZWFuKGVsZW1lbnRbdG9nZ2xlUHJvcGVydHldKSA9PT0gQm9vbGVhbihwcmV2ZW50V2l0aFByb3BlcnR5KSkge1xuXHRcdFx0ZS5zdG9wSW1tZWRpYXRlUHJvcGFnYXRpb24oKTtcblx0XHRcdC8vIGNvbnNvbGUubG9nKGV2ZW50TmFtZSwgJ3ByZXZlbnRlZCBvbicsIGVsZW1lbnQpO1xuXHRcdH1cblx0XHRkZWxldGUgZWxlbWVudFt0b2dnbGVQcm9wZXJ0eV07XG5cdH1cblx0ZWxlbWVudC5hZGRFdmVudExpc3RlbmVyKGV2ZW50TmFtZSwgaGFuZGxlciwgZmFsc2UpO1xuXG5cdC8vIFJldHVybiBoYW5kbGVyIHRvIGFsbG93IHRvIGRpc2FibGUgdGhlIHByZXZlbnRpb24uIFVzYWdlOlxuXHQvLyBjb25zdCBwcmV2ZW50aW9uSGFuZGxlciA9IHByZXZlbnRFdmVudChlbCwgJ2NsaWNrJyk7XG5cdC8vIGVsLnJlbW92ZUV2ZW50SGFuZGxlcignY2xpY2snLCBwcmV2ZW50aW9uSGFuZGxlcik7XG5cdHJldHVybiBoYW5kbGVyO1xufVxuXG5mdW5jdGlvbiBwcm94eVByb3BlcnR5KG9iamVjdCwgcHJvcGVydHlOYW1lLCBzb3VyY2VPYmplY3QsIGNvcHlGaXJzdCkge1xuXHRmdW5jdGlvbiBnZXQoKSB7XG5cdFx0cmV0dXJuIHNvdXJjZU9iamVjdFtwcm9wZXJ0eU5hbWVdO1xuXHR9XG5cdGZ1bmN0aW9uIHNldCh2YWx1ZSkge1xuXHRcdHNvdXJjZU9iamVjdFtwcm9wZXJ0eU5hbWVdID0gdmFsdWU7XG5cdH1cblxuXHRpZiAoY29weUZpcnN0KSB7XG5cdFx0c2V0KG9iamVjdFtwcm9wZXJ0eU5hbWVdKTtcblx0fVxuXG5cdE9iamVjdC5kZWZpbmVQcm9wZXJ0eShvYmplY3QsIHByb3BlcnR5TmFtZSwge2dldDogZ2V0LCBzZXQ6IHNldH0pO1xufVxuXG5mdW5jdGlvbiBwcm94eUV2ZW50KG9iamVjdCwgZXZlbnROYW1lLCBzb3VyY2VPYmplY3QpIHtcblx0c291cmNlT2JqZWN0LmFkZEV2ZW50TGlzdGVuZXIoZXZlbnROYW1lLCBmdW5jdGlvbiAoKSB7IHJldHVybiBvYmplY3QuZGlzcGF0Y2hFdmVudChuZXcgRXZlbnQoZXZlbnROYW1lKSk7IH0pO1xufVxuXG5mdW5jdGlvbiBkaXNwYXRjaEV2ZW50QXN5bmMoZWxlbWVudCwgdHlwZSkge1xuXHRQcm9taXNlLnJlc29sdmUoKS50aGVuKGZ1bmN0aW9uICgpIHtcblx0XHRlbGVtZW50LmRpc3BhdGNoRXZlbnQobmV3IEV2ZW50KHR5cGUpKTtcblx0fSk7XG59XG5cbi8vIGlPUyAxMCBhZGRzIHN1cHBvcnQgZm9yIG5hdGl2ZSBpbmxpbmUgcGxheWJhY2sgKyBzaWxlbnQgYXV0b3BsYXlcbnZhciBpc1doaXRlbGlzdGVkID0gL2lQaG9uZXxpUG9kL2kudGVzdChuYXZpZ2F0b3IudXNlckFnZW50KSAmJiAhbWF0Y2hNZWRpYSgnKC13ZWJraXQtdmlkZW8tcGxheWFibGUtaW5saW5lKScpLm1hdGNoZXM7XG5cbnZhciDgsqAgPSBTeW1ib2woKTtcbnZhciDgsqBldmVudCA9IFN5bWJvbCgpO1xudmFyIOCyoHBsYXkgPSBTeW1ib2woJ25hdGl2ZXBsYXknKTtcbnZhciDgsqBwYXVzZSA9IFN5bWJvbCgnbmF0aXZlcGF1c2UnKTtcblxuLyoqXG4gKiBVVElMU1xuICovXG5cbmZ1bmN0aW9uIGdldEF1ZGlvRnJvbVZpZGVvKHZpZGVvKSB7XG5cdHZhciBhdWRpbyA9IG5ldyBBdWRpbygpO1xuXHRwcm94eUV2ZW50KHZpZGVvLCAncGxheScsIGF1ZGlvKTtcblx0cHJveHlFdmVudCh2aWRlbywgJ3BsYXlpbmcnLCBhdWRpbyk7XG5cdHByb3h5RXZlbnQodmlkZW8sICdwYXVzZScsIGF1ZGlvKTtcblx0YXVkaW8uY3Jvc3NPcmlnaW4gPSB2aWRlby5jcm9zc09yaWdpbjtcblxuXHQvLyAnZGF0YTonIGNhdXNlcyBhdWRpby5uZXR3b3JrU3RhdGUgPiAwXG5cdC8vIHdoaWNoIHRoZW4gYWxsb3dzIHRvIGtlZXAgPGF1ZGlvPiBpbiBhIHJlc3VtYWJsZSBwbGF5aW5nIHN0YXRlXG5cdC8vIGkuZS4gb25jZSB5b3Ugc2V0IGEgcmVhbCBzcmMgaXQgd2lsbCBrZWVwIHBsYXlpbmcgaWYgaXQgd2FzIGlmIC5wbGF5KCkgd2FzIGNhbGxlZFxuXHRhdWRpby5zcmMgPSB2aWRlby5zcmMgfHwgdmlkZW8uY3VycmVudFNyYyB8fCAnZGF0YTonO1xuXG5cdC8vIGlmIChhdWRpby5zcmMgPT09ICdkYXRhOicpIHtcblx0Ly8gICBUT0RPOiB3YWl0IGZvciB2aWRlbyB0byBiZSBzZWxlY3RlZFxuXHQvLyB9XG5cdHJldHVybiBhdWRpbztcbn1cblxudmFyIGxhc3RSZXF1ZXN0cyA9IFtdO1xudmFyIHJlcXVlc3RJbmRleCA9IDA7XG52YXIgbGFzdFRpbWV1cGRhdGVFdmVudDtcblxuZnVuY3Rpb24gc2V0VGltZSh2aWRlbywgdGltZSwgcmVtZW1iZXJPbmx5KSB7XG5cdC8vIGFsbG93IG9uZSB0aW1ldXBkYXRlIGV2ZW50IGV2ZXJ5IDIwMCsgbXNcblx0aWYgKChsYXN0VGltZXVwZGF0ZUV2ZW50IHx8IDApICsgMjAwIDwgRGF0ZS5ub3coKSkge1xuXHRcdHZpZGVvW+CyoGV2ZW50XSA9IHRydWU7XG5cdFx0bGFzdFRpbWV1cGRhdGVFdmVudCA9IERhdGUubm93KCk7XG5cdH1cblx0aWYgKCFyZW1lbWJlck9ubHkpIHtcblx0XHR2aWRlby5jdXJyZW50VGltZSA9IHRpbWU7XG5cdH1cblx0bGFzdFJlcXVlc3RzWysrcmVxdWVzdEluZGV4ICUgM10gPSB0aW1lICogMTAwIHwgMCAvIDEwMDtcbn1cblxuZnVuY3Rpb24gaXNQbGF5ZXJFbmRlZChwbGF5ZXIpIHtcblx0cmV0dXJuIHBsYXllci5kcml2ZXIuY3VycmVudFRpbWUgPj0gcGxheWVyLnZpZGVvLmR1cmF0aW9uO1xufVxuXG5mdW5jdGlvbiB1cGRhdGUodGltZURpZmYpIHtcblx0dmFyIHBsYXllciA9IHRoaXM7XG5cdC8vIGNvbnNvbGUubG9nKCd1cGRhdGUnLCBwbGF5ZXIudmlkZW8ucmVhZHlTdGF0ZSwgcGxheWVyLnZpZGVvLm5ldHdvcmtTdGF0ZSwgcGxheWVyLmRyaXZlci5yZWFkeVN0YXRlLCBwbGF5ZXIuZHJpdmVyLm5ldHdvcmtTdGF0ZSwgcGxheWVyLmRyaXZlci5wYXVzZWQpO1xuXHRpZiAocGxheWVyLnZpZGVvLnJlYWR5U3RhdGUgPj0gcGxheWVyLnZpZGVvLkhBVkVfRlVUVVJFX0RBVEEpIHtcblx0XHRpZiAoIXBsYXllci5oYXNBdWRpbykge1xuXHRcdFx0cGxheWVyLmRyaXZlci5jdXJyZW50VGltZSA9IHBsYXllci52aWRlby5jdXJyZW50VGltZSArICgodGltZURpZmYgKiBwbGF5ZXIudmlkZW8ucGxheWJhY2tSYXRlKSAvIDEwMDApO1xuXHRcdFx0aWYgKHBsYXllci52aWRlby5sb29wICYmIGlzUGxheWVyRW5kZWQocGxheWVyKSkge1xuXHRcdFx0XHRwbGF5ZXIuZHJpdmVyLmN1cnJlbnRUaW1lID0gMDtcblx0XHRcdH1cblx0XHR9XG5cdFx0c2V0VGltZShwbGF5ZXIudmlkZW8sIHBsYXllci5kcml2ZXIuY3VycmVudFRpbWUpO1xuXHR9IGVsc2UgaWYgKHBsYXllci52aWRlby5uZXR3b3JrU3RhdGUgPT09IHBsYXllci52aWRlby5ORVRXT1JLX0lETEUgJiYgIXBsYXllci52aWRlby5idWZmZXJlZC5sZW5ndGgpIHtcblx0XHQvLyB0aGlzIHNob3VsZCBoYXBwZW4gd2hlbiB0aGUgc291cmNlIGlzIGF2YWlsYWJsZSBidXQ6XG5cdFx0Ly8gLSBpdCdzIHBvdGVudGlhbGx5IHBsYXlpbmcgKC5wYXVzZWQgPT09IGZhbHNlKVxuXHRcdC8vIC0gaXQncyBub3QgcmVhZHkgdG8gcGxheVxuXHRcdC8vIC0gaXQncyBub3QgbG9hZGluZ1xuXHRcdC8vIElmIGl0IGhhc0F1ZGlvLCB0aGF0IHdpbGwgYmUgbG9hZGVkIGluIHRoZSAnZW1wdGllZCcgaGFuZGxlciBiZWxvd1xuXHRcdHBsYXllci52aWRlby5sb2FkKCk7XG5cdFx0Ly8gY29uc29sZS5sb2coJ1dpbGwgbG9hZCcpO1xuXHR9XG5cblx0Ly8gY29uc29sZS5hc3NlcnQocGxheWVyLnZpZGVvLmN1cnJlbnRUaW1lID09PSBwbGF5ZXIuZHJpdmVyLmN1cnJlbnRUaW1lLCAnVmlkZW8gbm90IHVwZGF0aW5nIScpO1xuXG5cdGlmIChwbGF5ZXIudmlkZW8uZW5kZWQpIHtcblx0XHRkZWxldGUgcGxheWVyLnZpZGVvW+CyoGV2ZW50XTsgLy8gYWxsb3cgdGltZXVwZGF0ZSBldmVudFxuXHRcdHBsYXllci52aWRlby5wYXVzZSh0cnVlKTtcblx0fVxufVxuXG4vKipcbiAqIE1FVEhPRFNcbiAqL1xuXG5mdW5jdGlvbiBwbGF5KCkge1xuXHQvLyBjb25zb2xlLmxvZygncGxheScpO1xuXHR2YXIgdmlkZW8gPSB0aGlzO1xuXHR2YXIgcGxheWVyID0gdmlkZW9b4LKgXTtcblxuXHQvLyBpZiBpdCdzIGZ1bGxzY3JlZW4sIHVzZSB0aGUgbmF0aXZlIHBsYXllclxuXHRpZiAodmlkZW8ud2Via2l0RGlzcGxheWluZ0Z1bGxzY3JlZW4pIHtcblx0XHR2aWRlb1vgsqBwbGF5XSgpO1xuXHRcdHJldHVybjtcblx0fVxuXG5cdGlmIChwbGF5ZXIuZHJpdmVyLnNyYyAhPT0gJ2RhdGE6JyAmJiBwbGF5ZXIuZHJpdmVyLnNyYyAhPT0gdmlkZW8uc3JjKSB7XG5cdFx0Ly8gY29uc29sZS5sb2coJ3NyYyBjaGFuZ2VkIG9uIHBsYXknLCB2aWRlby5zcmMpO1xuXHRcdHNldFRpbWUodmlkZW8sIDAsIHRydWUpO1xuXHRcdHBsYXllci5kcml2ZXIuc3JjID0gdmlkZW8uc3JjO1xuXHR9XG5cblx0aWYgKCF2aWRlby5wYXVzZWQpIHtcblx0XHRyZXR1cm47XG5cdH1cblx0cGxheWVyLnBhdXNlZCA9IGZhbHNlO1xuXG5cdGlmICghdmlkZW8uYnVmZmVyZWQubGVuZ3RoKSB7XG5cdFx0Ly8gLmxvYWQoKSBjYXVzZXMgdGhlIGVtcHRpZWQgZXZlbnRcblx0XHQvLyB0aGUgYWx0ZXJuYXRpdmUgaXMgLnBsYXkoKSsucGF1c2UoKSBidXQgdGhhdCB0cmlnZ2VycyBwbGF5L3BhdXNlIGV2ZW50cywgZXZlbiB3b3JzZVxuXHRcdC8vIHBvc3NpYmx5IHRoZSBhbHRlcm5hdGl2ZSBpcyBwcmV2ZW50aW5nIHRoaXMgZXZlbnQgb25seSBvbmNlXG5cdFx0dmlkZW8ubG9hZCgpO1xuXHR9XG5cblx0cGxheWVyLmRyaXZlci5wbGF5KCk7XG5cdHBsYXllci51cGRhdGVyLnN0YXJ0KCk7XG5cblx0aWYgKCFwbGF5ZXIuaGFzQXVkaW8pIHtcblx0XHRkaXNwYXRjaEV2ZW50QXN5bmModmlkZW8sICdwbGF5Jyk7XG5cdFx0aWYgKHBsYXllci52aWRlby5yZWFkeVN0YXRlID49IHBsYXllci52aWRlby5IQVZFX0VOT1VHSF9EQVRBKSB7XG5cdFx0XHQvLyBjb25zb2xlLmxvZygnb25wbGF5Jyk7XG5cdFx0XHRkaXNwYXRjaEV2ZW50QXN5bmModmlkZW8sICdwbGF5aW5nJyk7XG5cdFx0fVxuXHR9XG59XG5mdW5jdGlvbiBwYXVzZShmb3JjZUV2ZW50cykge1xuXHQvLyBjb25zb2xlLmxvZygncGF1c2UnKTtcblx0dmFyIHZpZGVvID0gdGhpcztcblx0dmFyIHBsYXllciA9IHZpZGVvW+CyoF07XG5cblx0cGxheWVyLmRyaXZlci5wYXVzZSgpO1xuXHRwbGF5ZXIudXBkYXRlci5zdG9wKCk7XG5cblx0Ly8gaWYgaXQncyBmdWxsc2NyZWVuLCB0aGUgZGV2ZWxvcGVyIHRoZSBuYXRpdmUgcGxheWVyLnBhdXNlKClcblx0Ly8gVGhpcyBpcyBhdCB0aGUgZW5kIG9mIHBhdXNlKCkgYmVjYXVzZSBpdCBhbHNvXG5cdC8vIG5lZWRzIHRvIG1ha2Ugc3VyZSB0aGF0IHRoZSBzaW11bGF0aW9uIGlzIHBhdXNlZFxuXHRpZiAodmlkZW8ud2Via2l0RGlzcGxheWluZ0Z1bGxzY3JlZW4pIHtcblx0XHR2aWRlb1vgsqBwYXVzZV0oKTtcblx0fVxuXG5cdGlmIChwbGF5ZXIucGF1c2VkICYmICFmb3JjZUV2ZW50cykge1xuXHRcdHJldHVybjtcblx0fVxuXG5cdHBsYXllci5wYXVzZWQgPSB0cnVlO1xuXHRpZiAoIXBsYXllci5oYXNBdWRpbykge1xuXHRcdGRpc3BhdGNoRXZlbnRBc3luYyh2aWRlbywgJ3BhdXNlJyk7XG5cdH1cblx0aWYgKHZpZGVvLmVuZGVkKSB7XG5cdFx0dmlkZW9b4LKgZXZlbnRdID0gdHJ1ZTtcblx0XHRkaXNwYXRjaEV2ZW50QXN5bmModmlkZW8sICdlbmRlZCcpO1xuXHR9XG59XG5cbi8qKlxuICogU0VUVVBcbiAqL1xuXG5mdW5jdGlvbiBhZGRQbGF5ZXIodmlkZW8sIGhhc0F1ZGlvKSB7XG5cdHZhciBwbGF5ZXIgPSB2aWRlb1vgsqBdID0ge307XG5cdHBsYXllci5wYXVzZWQgPSB0cnVlOyAvLyB0cmFjayB3aGV0aGVyICdwYXVzZScgZXZlbnRzIGhhdmUgYmVlbiBmaXJlZFxuXHRwbGF5ZXIuaGFzQXVkaW8gPSBoYXNBdWRpbztcblx0cGxheWVyLnZpZGVvID0gdmlkZW87XG5cdHBsYXllci51cGRhdGVyID0gZnJhbWVJbnRlcnZhbG9tZXRlcih1cGRhdGUuYmluZChwbGF5ZXIpKTtcblxuXHRpZiAoaGFzQXVkaW8pIHtcblx0XHRwbGF5ZXIuZHJpdmVyID0gZ2V0QXVkaW9Gcm9tVmlkZW8odmlkZW8pO1xuXHR9IGVsc2Uge1xuXHRcdHZpZGVvLmFkZEV2ZW50TGlzdGVuZXIoJ2NhbnBsYXknLCBmdW5jdGlvbiAoKSB7XG5cdFx0XHRpZiAoIXZpZGVvLnBhdXNlZCkge1xuXHRcdFx0XHQvLyBjb25zb2xlLmxvZygnb25jYW5wbGF5Jyk7XG5cdFx0XHRcdGRpc3BhdGNoRXZlbnRBc3luYyh2aWRlbywgJ3BsYXlpbmcnKTtcblx0XHRcdH1cblx0XHR9KTtcblx0XHRwbGF5ZXIuZHJpdmVyID0ge1xuXHRcdFx0c3JjOiB2aWRlby5zcmMgfHwgdmlkZW8uY3VycmVudFNyYyB8fCAnZGF0YTonLFxuXHRcdFx0bXV0ZWQ6IHRydWUsXG5cdFx0XHRwYXVzZWQ6IHRydWUsXG5cdFx0XHRwYXVzZTogZnVuY3Rpb24gKCkge1xuXHRcdFx0XHRwbGF5ZXIuZHJpdmVyLnBhdXNlZCA9IHRydWU7XG5cdFx0XHR9LFxuXHRcdFx0cGxheTogZnVuY3Rpb24gKCkge1xuXHRcdFx0XHRwbGF5ZXIuZHJpdmVyLnBhdXNlZCA9IGZhbHNlO1xuXHRcdFx0XHQvLyBtZWRpYSBhdXRvbWF0aWNhbGx5IGdvZXMgdG8gMCBpZiAucGxheSgpIGlzIGNhbGxlZCB3aGVuIGl0J3MgZG9uZVxuXHRcdFx0XHRpZiAoaXNQbGF5ZXJFbmRlZChwbGF5ZXIpKSB7XG5cdFx0XHRcdFx0c2V0VGltZSh2aWRlbywgMCk7XG5cdFx0XHRcdH1cblx0XHRcdH0sXG5cdFx0XHRnZXQgZW5kZWQoKSB7XG5cdFx0XHRcdHJldHVybiBpc1BsYXllckVuZGVkKHBsYXllcik7XG5cdFx0XHR9XG5cdFx0fTtcblx0fVxuXG5cdC8vIC5sb2FkKCkgY2F1c2VzIHRoZSBlbXB0aWVkIGV2ZW50XG5cdHZpZGVvLmFkZEV2ZW50TGlzdGVuZXIoJ2VtcHRpZWQnLCBmdW5jdGlvbiAoKSB7XG5cdFx0Ly8gY29uc29sZS5sb2coJ2RyaXZlciBzcmMgaXMnLCBwbGF5ZXIuZHJpdmVyLnNyYyk7XG5cdFx0dmFyIHdhc0VtcHR5ID0gIXBsYXllci5kcml2ZXIuc3JjIHx8IHBsYXllci5kcml2ZXIuc3JjID09PSAnZGF0YTonO1xuXHRcdGlmIChwbGF5ZXIuZHJpdmVyLnNyYyAmJiBwbGF5ZXIuZHJpdmVyLnNyYyAhPT0gdmlkZW8uc3JjKSB7XG5cdFx0XHQvLyBjb25zb2xlLmxvZygnc3JjIGNoYW5nZWQgdG8nLCB2aWRlby5zcmMpO1xuXHRcdFx0c2V0VGltZSh2aWRlbywgMCwgdHJ1ZSk7XG5cdFx0XHRwbGF5ZXIuZHJpdmVyLnNyYyA9IHZpZGVvLnNyYztcblx0XHRcdC8vIHBsYXlpbmcgdmlkZW9zIHdpbGwgb25seSBrZWVwIHBsYXlpbmcgaWYgbm8gc3JjIHdhcyBwcmVzZW50IHdoZW4gLnBsYXkoKeKAmWVkXG5cdFx0XHRpZiAod2FzRW1wdHkpIHtcblx0XHRcdFx0cGxheWVyLmRyaXZlci5wbGF5KCk7XG5cdFx0XHR9IGVsc2Uge1xuXHRcdFx0XHRwbGF5ZXIudXBkYXRlci5zdG9wKCk7XG5cdFx0XHR9XG5cdFx0fVxuXHR9LCBmYWxzZSk7XG5cblx0Ly8gc3RvcCBwcm9ncmFtbWF0aWMgcGxheWVyIHdoZW4gT1MgdGFrZXMgb3ZlclxuXHR2aWRlby5hZGRFdmVudExpc3RlbmVyKCd3ZWJraXRiZWdpbmZ1bGxzY3JlZW4nLCBmdW5jdGlvbiAoKSB7XG5cdFx0aWYgKCF2aWRlby5wYXVzZWQpIHtcblx0XHRcdC8vIG1ha2Ugc3VyZSB0aGF0IHRoZSA8YXVkaW8+IGFuZCB0aGUgc3luY2VyL3VwZGF0ZXIgYXJlIHN0b3BwZWRcblx0XHRcdHZpZGVvLnBhdXNlKCk7XG5cblx0XHRcdC8vIHBsYXkgdmlkZW8gbmF0aXZlbHlcblx0XHRcdHZpZGVvW+CyoHBsYXldKCk7XG5cdFx0fSBlbHNlIGlmIChoYXNBdWRpbyAmJiAhcGxheWVyLmRyaXZlci5idWZmZXJlZC5sZW5ndGgpIHtcblx0XHRcdC8vIGlmIHRoZSBmaXJzdCBwbGF5IGlzIG5hdGl2ZSxcblx0XHRcdC8vIHRoZSA8YXVkaW8+IG5lZWRzIHRvIGJlIGJ1ZmZlcmVkIG1hbnVhbGx5XG5cdFx0XHQvLyBzbyB3aGVuIHRoZSBmdWxsc2NyZWVuIGVuZHMsIGl0IGNhbiBiZSBzZXQgdG8gdGhlIHNhbWUgY3VycmVudCB0aW1lXG5cdFx0XHRwbGF5ZXIuZHJpdmVyLmxvYWQoKTtcblx0XHR9XG5cdH0pO1xuXHRpZiAoaGFzQXVkaW8pIHtcblx0XHR2aWRlby5hZGRFdmVudExpc3RlbmVyKCd3ZWJraXRlbmRmdWxsc2NyZWVuJywgZnVuY3Rpb24gKCkge1xuXHRcdFx0Ly8gc3luYyBhdWRpbyB0byBuZXcgdmlkZW8gcG9zaXRpb25cblx0XHRcdHBsYXllci5kcml2ZXIuY3VycmVudFRpbWUgPSB2aWRlby5jdXJyZW50VGltZTtcblx0XHRcdC8vIGNvbnNvbGUuYXNzZXJ0KHBsYXllci5kcml2ZXIuY3VycmVudFRpbWUgPT09IHZpZGVvLmN1cnJlbnRUaW1lLCAnQXVkaW8gbm90IHN5bmNlZCcpO1xuXHRcdH0pO1xuXG5cdFx0Ly8gYWxsb3cgc2Vla2luZ1xuXHRcdHZpZGVvLmFkZEV2ZW50TGlzdGVuZXIoJ3NlZWtpbmcnLCBmdW5jdGlvbiAoKSB7XG5cdFx0XHRpZiAobGFzdFJlcXVlc3RzLmluZGV4T2YodmlkZW8uY3VycmVudFRpbWUgKiAxMDAgfCAwIC8gMTAwKSA8IDApIHtcblx0XHRcdFx0Ly8gY29uc29sZS5sb2coJ1VzZXItcmVxdWVzdGVkIHNlZWtpbmcnKTtcblx0XHRcdFx0cGxheWVyLmRyaXZlci5jdXJyZW50VGltZSA9IHZpZGVvLmN1cnJlbnRUaW1lO1xuXHRcdFx0fVxuXHRcdH0pO1xuXHR9XG59XG5cbmZ1bmN0aW9uIG92ZXJsb2FkQVBJKHZpZGVvKSB7XG5cdHZhciBwbGF5ZXIgPSB2aWRlb1vgsqBdO1xuXHR2aWRlb1vgsqBwbGF5XSA9IHZpZGVvLnBsYXk7XG5cdHZpZGVvW+CyoHBhdXNlXSA9IHZpZGVvLnBhdXNlO1xuXHR2aWRlby5wbGF5ID0gcGxheTtcblx0dmlkZW8ucGF1c2UgPSBwYXVzZTtcblx0cHJveHlQcm9wZXJ0eSh2aWRlbywgJ3BhdXNlZCcsIHBsYXllci5kcml2ZXIpO1xuXHRwcm94eVByb3BlcnR5KHZpZGVvLCAnbXV0ZWQnLCBwbGF5ZXIuZHJpdmVyLCB0cnVlKTtcblx0cHJveHlQcm9wZXJ0eSh2aWRlbywgJ3BsYXliYWNrUmF0ZScsIHBsYXllci5kcml2ZXIsIHRydWUpO1xuXHRwcm94eVByb3BlcnR5KHZpZGVvLCAnZW5kZWQnLCBwbGF5ZXIuZHJpdmVyKTtcblx0cHJveHlQcm9wZXJ0eSh2aWRlbywgJ2xvb3AnLCBwbGF5ZXIuZHJpdmVyLCB0cnVlKTtcblx0cHJldmVudEV2ZW50KHZpZGVvLCAnc2Vla2luZycpO1xuXHRwcmV2ZW50RXZlbnQodmlkZW8sICdzZWVrZWQnKTtcblx0cHJldmVudEV2ZW50KHZpZGVvLCAndGltZXVwZGF0ZScsIOCyoGV2ZW50LCBmYWxzZSk7XG5cdHByZXZlbnRFdmVudCh2aWRlbywgJ2VuZGVkJywg4LKgZXZlbnQsIGZhbHNlKTsgLy8gcHJldmVudCBvY2Nhc2lvbmFsIG5hdGl2ZSBlbmRlZCBldmVudHNcbn1cblxuZnVuY3Rpb24gZW5hYmxlSW5saW5lVmlkZW8odmlkZW8sIGhhc0F1ZGlvLCBvbmx5V2hpdGVsaXN0ZWQpIHtcblx0aWYgKCBoYXNBdWRpbyA9PT0gdm9pZCAwICkgaGFzQXVkaW8gPSB0cnVlO1xuXHRpZiAoIG9ubHlXaGl0ZWxpc3RlZCA9PT0gdm9pZCAwICkgb25seVdoaXRlbGlzdGVkID0gdHJ1ZTtcblxuXHRpZiAoKG9ubHlXaGl0ZWxpc3RlZCAmJiAhaXNXaGl0ZWxpc3RlZCkgfHwgdmlkZW9b4LKgXSkge1xuXHRcdHJldHVybjtcblx0fVxuXHRhZGRQbGF5ZXIodmlkZW8sIGhhc0F1ZGlvKTtcblx0b3ZlcmxvYWRBUEkodmlkZW8pO1xuXHR2aWRlby5jbGFzc0xpc3QuYWRkKCdJSVYnKTtcblx0aWYgKCFoYXNBdWRpbyAmJiB2aWRlby5hdXRvcGxheSkge1xuXHRcdHZpZGVvLnBsYXkoKTtcblx0fVxuXHRpZiAoIS9pUGhvbmV8aVBvZHxpUGFkLy50ZXN0KG5hdmlnYXRvci5wbGF0Zm9ybSkpIHtcblx0XHRjb25zb2xlLndhcm4oJ2lwaG9uZS1pbmxpbmUtdmlkZW8gaXMgbm90IGd1YXJhbnRlZWQgdG8gd29yayBpbiBlbXVsYXRlZCBlbnZpcm9ubWVudHMnKTtcblx0fVxufVxuXG5lbmFibGVJbmxpbmVWaWRlby5pc1doaXRlbGlzdGVkID0gaXNXaGl0ZWxpc3RlZDtcblxuZXhwb3J0IGRlZmF1bHQgZW5hYmxlSW5saW5lVmlkZW87IiwiLyoqXG4gKiBAZmlsZVxuICogQWxsb3cgYXV0b3BsYXlpbmcgdmlkZW9zIG9uIG1vYmlsZS5cbiAqXG4gKiBAc2VlICBodHRwczovL2dpdGh1Yi5jb20vYmZyZWQtaXQvaXBob25lLWlubGluZS12aWRlb1xuICovXG5cbihmdW5jdGlvbiAoKSB7XG5cblx0Y29uc3QgbWFrZVZpZGVvUGxheWFibGVJbmxpbmUgPSByZXF1aXJlKCdpcGhvbmUtaW5saW5lLXZpZGVvJyk7XG5cdHZhciB2aWRlbyA9IGRvY3VtZW50LnF1ZXJ5U2VsZWN0b3IoJy5iYWNrZ3JvdW5kLXZpZGVvJyk7XG5cdG1ha2VWaWRlb1BsYXlhYmxlSW5saW5lKHZpZGVvLCAhdmlkZW8uaGFzQXR0cmlidXRlKCdtdXRlZCcpKTtcblxufSkoKVxuIl0sIm5hbWVzIjpbImluZGV4IiwiU3ltYm9sIiwiY29uc3QiXSwibWFwcGluZ3MiOiI7OztBQUFBLElBQUlBLE9BQUssR0FBRyxPQUFPLE1BQU0sS0FBSyxXQUFXLEdBQUcsVUFBVSxXQUFXLEVBQUU7Q0FDbEUsT0FBTyxHQUFHLEdBQUcsQ0FBQyxXQUFXLElBQUksR0FBRyxDQUFDLEdBQUcsSUFBSSxDQUFDLE1BQU0sRUFBRSxDQUFDO0NBQ2xELEdBQUcsTUFBTSxDQUFDOztBQ0ZYO0FBQ0EsU0FBUyxjQUFjLENBQUMsRUFBRSxFQUFFLE9BQU8sRUFBRSxNQUFNLEVBQUUsZ0JBQWdCLEVBQUU7Q0FDOUQsSUFBSSxTQUFTLENBQUM7Q0FDZCxJQUFJLGdCQUFnQixDQUFDO0NBQ3JCLFNBQVMsSUFBSSxDQUFDLEdBQUcsRUFBRTs7RUFFbEIsU0FBUyxHQUFHLE9BQU8sQ0FBQyxJQUFJLEVBQUUsZ0JBQWdCLENBQUMsQ0FBQzs7O0VBRzVDLEVBQUUsQ0FBQyxHQUFHLEdBQUcsQ0FBQyxnQkFBZ0IsSUFBSSxHQUFHLENBQUMsQ0FBQyxDQUFDOztFQUVwQyxnQkFBZ0IsR0FBRyxHQUFHLENBQUM7RUFDdkI7Q0FDRCxPQUFPO0VBQ04sS0FBSyxFQUFFLFNBQVMsS0FBSyxHQUFHO0dBQ3ZCLElBQUksQ0FBQyxTQUFTLEVBQUU7SUFDZixJQUFJLENBQUMsQ0FBQyxDQUFDLENBQUM7SUFDUjtHQUNEO0VBQ0QsSUFBSSxFQUFFLFNBQVMsSUFBSSxHQUFHO0dBQ3JCLE1BQU0sQ0FBQyxTQUFTLENBQUMsQ0FBQztHQUNsQixTQUFTLEdBQUcsSUFBSSxDQUFDO0dBQ2pCLGdCQUFnQixHQUFHLENBQUMsQ0FBQztHQUNyQjtFQUNELENBQUM7Q0FDRjs7QUFFRCxTQUFTLG1CQUFtQixDQUFDLEVBQUUsRUFBRTtDQUNoQyxPQUFPLGNBQWMsQ0FBQyxFQUFFLEVBQUUscUJBQXFCLEVBQUUsb0JBQW9CLENBQUMsQ0FBQztDQUN2RTs7QUM3QkQ7QUFDQSxTQUdTLFlBQVksQ0FBQyxPQUFPLEVBQUUsU0FBUyxFQUFFLGNBQWMsRUFBRSxtQkFBbUIsRUFBRTtDQUM5RSxTQUFTLE9BQU8sQ0FBQyxDQUFDLEVBQUU7RUFDbkIsSUFBSSxPQUFPLENBQUMsT0FBTyxDQUFDLGNBQWMsQ0FBQyxDQUFDLEtBQUssT0FBTyxDQUFDLG1CQUFtQixDQUFDLEVBQUU7R0FDdEUsQ0FBQyxDQUFDLHdCQUF3QixFQUFFLENBQUM7O0dBRTdCO0VBQ0QsT0FBTyxPQUFPLENBQUMsY0FBYyxDQUFDLENBQUM7RUFDL0I7Q0FDRCxPQUFPLENBQUMsZ0JBQWdCLENBQUMsU0FBUyxFQUFFLE9BQU8sRUFBRSxLQUFLLENBQUMsQ0FBQzs7Ozs7Q0FLcEQsT0FBTyxPQUFPLENBQUM7Q0FDZjs7QUFFRCxTQUFTLGFBQWEsQ0FBQyxNQUFNLEVBQUUsWUFBWSxFQUFFLFlBQVksRUFBRSxTQUFTLEVBQUU7Q0FDckUsU0FBUyxHQUFHLEdBQUc7RUFDZCxPQUFPLFlBQVksQ0FBQyxZQUFZLENBQUMsQ0FBQztFQUNsQztDQUNELFNBQVMsR0FBRyxDQUFDLEtBQUssRUFBRTtFQUNuQixZQUFZLENBQUMsWUFBWSxDQUFDLEdBQUcsS0FBSyxDQUFDO0VBQ25DOztDQUVELElBQUksU0FBUyxFQUFFO0VBQ2QsR0FBRyxDQUFDLE1BQU0sQ0FBQyxZQUFZLENBQUMsQ0FBQyxDQUFDO0VBQzFCOztDQUVELE1BQU0sQ0FBQyxjQUFjLENBQUMsTUFBTSxFQUFFLFlBQVksRUFBRSxDQUFDLEdBQUcsRUFBRSxHQUFHLEVBQUUsR0FBRyxFQUFFLEdBQUcsQ0FBQyxDQUFDLENBQUM7Q0FDbEU7O0FBRUQsU0FBUyxVQUFVLENBQUMsTUFBTSxFQUFFLFNBQVMsRUFBRSxZQUFZLEVBQUU7Q0FDcEQsWUFBWSxDQUFDLGdCQUFnQixDQUFDLFNBQVMsRUFBRSxZQUFZLEVBQUUsT0FBTyxNQUFNLENBQUMsYUFBYSxDQUFDLElBQUksS0FBSyxDQUFDLFNBQVMsQ0FBQyxDQUFDLENBQUMsRUFBRSxDQUFDLENBQUM7Q0FDN0c7O0FBRUQsU0FBUyxrQkFBa0IsQ0FBQyxPQUFPLEVBQUUsSUFBSSxFQUFFO0NBQzFDLE9BQU8sQ0FBQyxPQUFPLEVBQUUsQ0FBQyxJQUFJLENBQUMsWUFBWTtFQUNsQyxPQUFPLENBQUMsYUFBYSxDQUFDLElBQUksS0FBSyxDQUFDLElBQUksQ0FBQyxDQUFDLENBQUM7RUFDdkMsQ0FBQyxDQUFDO0NBQ0g7OztBQUdELElBQUksYUFBYSxHQUFHLGNBQWMsQ0FBQyxJQUFJLENBQUMsU0FBUyxDQUFDLFNBQVMsQ0FBQyxJQUFJLENBQUMsVUFBVSxDQUFDLGlDQUFpQyxDQUFDLENBQUMsT0FBTyxDQUFDOztBQUV2SCxJQUFJLENBQUMsR0FBR0MsT0FBTSxFQUFFLENBQUM7QUFDakIsSUFBSSxNQUFNLEdBQUdBLE9BQU0sRUFBRSxDQUFDO0FBQ3RCLElBQUksS0FBSyxHQUFHQSxPQUFNLENBQUMsWUFBWSxDQUFDLENBQUM7QUFDakMsSUFBSSxNQUFNLEdBQUdBLE9BQU0sQ0FBQyxhQUFhLENBQUMsQ0FBQzs7Ozs7O0FBTW5DLFNBQVMsaUJBQWlCLENBQUMsS0FBSyxFQUFFO0NBQ2pDLElBQUksS0FBSyxHQUFHLElBQUksS0FBSyxFQUFFLENBQUM7Q0FDeEIsVUFBVSxDQUFDLEtBQUssRUFBRSxNQUFNLEVBQUUsS0FBSyxDQUFDLENBQUM7Q0FDakMsVUFBVSxDQUFDLEtBQUssRUFBRSxTQUFTLEVBQUUsS0FBSyxDQUFDLENBQUM7Q0FDcEMsVUFBVSxDQUFDLEtBQUssRUFBRSxPQUFPLEVBQUUsS0FBSyxDQUFDLENBQUM7Q0FDbEMsS0FBSyxDQUFDLFdBQVcsR0FBRyxLQUFLLENBQUMsV0FBVyxDQUFDOzs7OztDQUt0QyxLQUFLLENBQUMsR0FBRyxHQUFHLEtBQUssQ0FBQyxHQUFHLElBQUksS0FBSyxDQUFDLFVBQVUsSUFBSSxPQUFPLENBQUM7Ozs7O0NBS3JELE9BQU8sS0FBSyxDQUFDO0NBQ2I7O0FBRUQsSUFBSSxZQUFZLEdBQUcsRUFBRSxDQUFDO0FBQ3RCLElBQUksWUFBWSxHQUFHLENBQUMsQ0FBQztBQUNyQixJQUFJLG1CQUFtQixDQUFDOztBQUV4QixTQUFTLE9BQU8sQ0FBQyxLQUFLLEVBQUUsSUFBSSxFQUFFLFlBQVksRUFBRTs7Q0FFM0MsSUFBSSxDQUFDLG1CQUFtQixJQUFJLENBQUMsQ0FBQyxHQUFHLEdBQUcsR0FBRyxJQUFJLENBQUMsR0FBRyxFQUFFLEVBQUU7RUFDbEQsS0FBSyxDQUFDLE1BQU0sQ0FBQyxHQUFHLElBQUksQ0FBQztFQUNyQixtQkFBbUIsR0FBRyxJQUFJLENBQUMsR0FBRyxFQUFFLENBQUM7RUFDakM7Q0FDRCxJQUFJLENBQUMsWUFBWSxFQUFFO0VBQ2xCLEtBQUssQ0FBQyxXQUFXLEdBQUcsSUFBSSxDQUFDO0VBQ3pCO0NBQ0QsWUFBWSxDQUFDLEVBQUUsWUFBWSxHQUFHLENBQUMsQ0FBQyxHQUFHLElBQUksR0FBRyxHQUFHLEdBQUcsQ0FBQyxHQUFHLEdBQUcsQ0FBQztDQUN4RDs7QUFFRCxTQUFTLGFBQWEsQ0FBQyxNQUFNLEVBQUU7Q0FDOUIsT0FBTyxNQUFNLENBQUMsTUFBTSxDQUFDLFdBQVcsSUFBSSxNQUFNLENBQUMsS0FBSyxDQUFDLFFBQVEsQ0FBQztDQUMxRDs7QUFFRCxTQUFTLE1BQU0sQ0FBQyxRQUFRLEVBQUU7Q0FDekIsSUFBSSxNQUFNLEdBQUcsSUFBSSxDQUFDOztDQUVsQixJQUFJLE1BQU0sQ0FBQyxLQUFLLENBQUMsVUFBVSxJQUFJLE1BQU0sQ0FBQyxLQUFLLENBQUMsZ0JBQWdCLEVBQUU7RUFDN0QsSUFBSSxDQUFDLE1BQU0sQ0FBQyxRQUFRLEVBQUU7R0FDckIsTUFBTSxDQUFDLE1BQU0sQ0FBQyxXQUFXLEdBQUcsTUFBTSxDQUFDLEtBQUssQ0FBQyxXQUFXLEdBQUcsQ0FBQyxDQUFDLFFBQVEsR0FBRyxNQUFNLENBQUMsS0FBSyxDQUFDLFlBQVksQ0FBQyxHQUFHLElBQUksQ0FBQyxDQUFDO0dBQ3ZHLElBQUksTUFBTSxDQUFDLEtBQUssQ0FBQyxJQUFJLElBQUksYUFBYSxDQUFDLE1BQU0sQ0FBQyxFQUFFO0lBQy9DLE1BQU0sQ0FBQyxNQUFNLENBQUMsV0FBVyxHQUFHLENBQUMsQ0FBQztJQUM5QjtHQUNEO0VBQ0QsT0FBTyxDQUFDLE1BQU0sQ0FBQyxLQUFLLEVBQUUsTUFBTSxDQUFDLE1BQU0sQ0FBQyxXQUFXLENBQUMsQ0FBQztFQUNqRCxNQUFNLElBQUksTUFBTSxDQUFDLEtBQUssQ0FBQyxZQUFZLEtBQUssTUFBTSxDQUFDLEtBQUssQ0FBQyxZQUFZLElBQUksQ0FBQyxNQUFNLENBQUMsS0FBSyxDQUFDLFFBQVEsQ0FBQyxNQUFNLEVBQUU7Ozs7OztFQU1wRyxNQUFNLENBQUMsS0FBSyxDQUFDLElBQUksRUFBRSxDQUFDOztFQUVwQjs7OztDQUlELElBQUksTUFBTSxDQUFDLEtBQUssQ0FBQyxLQUFLLEVBQUU7RUFDdkIsT0FBTyxNQUFNLENBQUMsS0FBSyxDQUFDLE1BQU0sQ0FBQyxDQUFDO0VBQzVCLE1BQU0sQ0FBQyxLQUFLLENBQUMsS0FBSyxDQUFDLElBQUksQ0FBQyxDQUFDO0VBQ3pCO0NBQ0Q7Ozs7OztBQU1ELFNBQVMsSUFBSSxHQUFHOztDQUVmLElBQUksS0FBSyxHQUFHLElBQUksQ0FBQztDQUNqQixJQUFJLE1BQU0sR0FBRyxLQUFLLENBQUMsQ0FBQyxDQUFDLENBQUM7OztDQUd0QixJQUFJLEtBQUssQ0FBQywwQkFBMEIsRUFBRTtFQUNyQyxLQUFLLENBQUMsS0FBSyxDQUFDLEVBQUUsQ0FBQztFQUNmLE9BQU87RUFDUDs7Q0FFRCxJQUFJLE1BQU0sQ0FBQyxNQUFNLENBQUMsR0FBRyxLQUFLLE9BQU8sSUFBSSxNQUFNLENBQUMsTUFBTSxDQUFDLEdBQUcsS0FBSyxLQUFLLENBQUMsR0FBRyxFQUFFOztFQUVyRSxPQUFPLENBQUMsS0FBSyxFQUFFLENBQUMsRUFBRSxJQUFJLENBQUMsQ0FBQztFQUN4QixNQUFNLENBQUMsTUFBTSxDQUFDLEdBQUcsR0FBRyxLQUFLLENBQUMsR0FBRyxDQUFDO0VBQzlCOztDQUVELElBQUksQ0FBQyxLQUFLLENBQUMsTUFBTSxFQUFFO0VBQ2xCLE9BQU87RUFDUDtDQUNELE1BQU0sQ0FBQyxNQUFNLEdBQUcsS0FBSyxDQUFDOztDQUV0QixJQUFJLENBQUMsS0FBSyxDQUFDLFFBQVEsQ0FBQyxNQUFNLEVBQUU7Ozs7RUFJM0IsS0FBSyxDQUFDLElBQUksRUFBRSxDQUFDO0VBQ2I7O0NBRUQsTUFBTSxDQUFDLE1BQU0sQ0FBQyxJQUFJLEVBQUUsQ0FBQztDQUNyQixNQUFNLENBQUMsT0FBTyxDQUFDLEtBQUssRUFBRSxDQUFDOztDQUV2QixJQUFJLENBQUMsTUFBTSxDQUFDLFFBQVEsRUFBRTtFQUNyQixrQkFBa0IsQ0FBQyxLQUFLLEVBQUUsTUFBTSxDQUFDLENBQUM7RUFDbEMsSUFBSSxNQUFNLENBQUMsS0FBSyxDQUFDLFVBQVUsSUFBSSxNQUFNLENBQUMsS0FBSyxDQUFDLGdCQUFnQixFQUFFOztHQUU3RCxrQkFBa0IsQ0FBQyxLQUFLLEVBQUUsU0FBUyxDQUFDLENBQUM7R0FDckM7RUFDRDtDQUNEO0FBQ0QsU0FBUyxLQUFLLENBQUMsV0FBVyxFQUFFOztDQUUzQixJQUFJLEtBQUssR0FBRyxJQUFJLENBQUM7Q0FDakIsSUFBSSxNQUFNLEdBQUcsS0FBSyxDQUFDLENBQUMsQ0FBQyxDQUFDOztDQUV0QixNQUFNLENBQUMsTUFBTSxDQUFDLEtBQUssRUFBRSxDQUFDO0NBQ3RCLE1BQU0sQ0FBQyxPQUFPLENBQUMsSUFBSSxFQUFFLENBQUM7Ozs7O0NBS3RCLElBQUksS0FBSyxDQUFDLDBCQUEwQixFQUFFO0VBQ3JDLEtBQUssQ0FBQyxNQUFNLENBQUMsRUFBRSxDQUFDO0VBQ2hCOztDQUVELElBQUksTUFBTSxDQUFDLE1BQU0sSUFBSSxDQUFDLFdBQVcsRUFBRTtFQUNsQyxPQUFPO0VBQ1A7O0NBRUQsTUFBTSxDQUFDLE1BQU0sR0FBRyxJQUFJLENBQUM7Q0FDckIsSUFBSSxDQUFDLE1BQU0sQ0FBQyxRQUFRLEVBQUU7RUFDckIsa0JBQWtCLENBQUMsS0FBSyxFQUFFLE9BQU8sQ0FBQyxDQUFDO0VBQ25DO0NBQ0QsSUFBSSxLQUFLLENBQUMsS0FBSyxFQUFFO0VBQ2hCLEtBQUssQ0FBQyxNQUFNLENBQUMsR0FBRyxJQUFJLENBQUM7RUFDckIsa0JBQWtCLENBQUMsS0FBSyxFQUFFLE9BQU8sQ0FBQyxDQUFDO0VBQ25DO0NBQ0Q7Ozs7OztBQU1ELFNBQVMsU0FBUyxDQUFDLEtBQUssRUFBRSxRQUFRLEVBQUU7Q0FDbkMsSUFBSSxNQUFNLEdBQUcsS0FBSyxDQUFDLENBQUMsQ0FBQyxHQUFHLEVBQUUsQ0FBQztDQUMzQixNQUFNLENBQUMsTUFBTSxHQUFHLElBQUksQ0FBQztDQUNyQixNQUFNLENBQUMsUUFBUSxHQUFHLFFBQVEsQ0FBQztDQUMzQixNQUFNLENBQUMsS0FBSyxHQUFHLEtBQUssQ0FBQztDQUNyQixNQUFNLENBQUMsT0FBTyxHQUFHLG1CQUFtQixDQUFDLE1BQU0sQ0FBQyxJQUFJLENBQUMsTUFBTSxDQUFDLENBQUMsQ0FBQzs7Q0FFMUQsSUFBSSxRQUFRLEVBQUU7RUFDYixNQUFNLENBQUMsTUFBTSxHQUFHLGlCQUFpQixDQUFDLEtBQUssQ0FBQyxDQUFDO0VBQ3pDLE1BQU07RUFDTixLQUFLLENBQUMsZ0JBQWdCLENBQUMsU0FBUyxFQUFFLFlBQVk7R0FDN0MsSUFBSSxDQUFDLEtBQUssQ0FBQyxNQUFNLEVBQUU7O0lBRWxCLGtCQUFrQixDQUFDLEtBQUssRUFBRSxTQUFTLENBQUMsQ0FBQztJQUNyQztHQUNELENBQUMsQ0FBQztFQUNILE1BQU0sQ0FBQyxNQUFNLEdBQUc7R0FDZixHQUFHLEVBQUUsS0FBSyxDQUFDLEdBQUcsSUFBSSxLQUFLLENBQUMsVUFBVSxJQUFJLE9BQU87R0FDN0MsS0FBSyxFQUFFLElBQUk7R0FDWCxNQUFNLEVBQUUsSUFBSTtHQUNaLEtBQUssRUFBRSxZQUFZO0lBQ2xCLE1BQU0sQ0FBQyxNQUFNLENBQUMsTUFBTSxHQUFHLElBQUksQ0FBQztJQUM1QjtHQUNELElBQUksRUFBRSxZQUFZO0lBQ2pCLE1BQU0sQ0FBQyxNQUFNLENBQUMsTUFBTSxHQUFHLEtBQUssQ0FBQzs7SUFFN0IsSUFBSSxhQUFhLENBQUMsTUFBTSxDQUFDLEVBQUU7S0FDMUIsT0FBTyxDQUFDLEtBQUssRUFBRSxDQUFDLENBQUMsQ0FBQztLQUNsQjtJQUNEO0dBQ0QsSUFBSSxLQUFLLEdBQUc7SUFDWCxPQUFPLGFBQWEsQ0FBQyxNQUFNLENBQUMsQ0FBQztJQUM3QjtHQUNELENBQUM7RUFDRjs7O0NBR0QsS0FBSyxDQUFDLGdCQUFnQixDQUFDLFNBQVMsRUFBRSxZQUFZOztFQUU3QyxJQUFJLFFBQVEsR0FBRyxDQUFDLE1BQU0sQ0FBQyxNQUFNLENBQUMsR0FBRyxJQUFJLE1BQU0sQ0FBQyxNQUFNLENBQUMsR0FBRyxLQUFLLE9BQU8sQ0FBQztFQUNuRSxJQUFJLE1BQU0sQ0FBQyxNQUFNLENBQUMsR0FBRyxJQUFJLE1BQU0sQ0FBQyxNQUFNLENBQUMsR0FBRyxLQUFLLEtBQUssQ0FBQyxHQUFHLEVBQUU7O0dBRXpELE9BQU8sQ0FBQyxLQUFLLEVBQUUsQ0FBQyxFQUFFLElBQUksQ0FBQyxDQUFDO0dBQ3hCLE1BQU0sQ0FBQyxNQUFNLENBQUMsR0FBRyxHQUFHLEtBQUssQ0FBQyxHQUFHLENBQUM7O0dBRTlCLElBQUksUUFBUSxFQUFFO0lBQ2IsTUFBTSxDQUFDLE1BQU0sQ0FBQyxJQUFJLEVBQUUsQ0FBQztJQUNyQixNQUFNO0lBQ04sTUFBTSxDQUFDLE9BQU8sQ0FBQyxJQUFJLEVBQUUsQ0FBQztJQUN0QjtHQUNEO0VBQ0QsRUFBRSxLQUFLLENBQUMsQ0FBQzs7O0NBR1YsS0FBSyxDQUFDLGdCQUFnQixDQUFDLHVCQUF1QixFQUFFLFlBQVk7RUFDM0QsSUFBSSxDQUFDLEtBQUssQ0FBQyxNQUFNLEVBQUU7O0dBRWxCLEtBQUssQ0FBQyxLQUFLLEVBQUUsQ0FBQzs7O0dBR2QsS0FBSyxDQUFDLEtBQUssQ0FBQyxFQUFFLENBQUM7R0FDZixNQUFNLElBQUksUUFBUSxJQUFJLENBQUMsTUFBTSxDQUFDLE1BQU0sQ0FBQyxRQUFRLENBQUMsTUFBTSxFQUFFOzs7O0dBSXRELE1BQU0sQ0FBQyxNQUFNLENBQUMsSUFBSSxFQUFFLENBQUM7R0FDckI7RUFDRCxDQUFDLENBQUM7Q0FDSCxJQUFJLFFBQVEsRUFBRTtFQUNiLEtBQUssQ0FBQyxnQkFBZ0IsQ0FBQyxxQkFBcUIsRUFBRSxZQUFZOztHQUV6RCxNQUFNLENBQUMsTUFBTSxDQUFDLFdBQVcsR0FBRyxLQUFLLENBQUMsV0FBVyxDQUFDOztHQUU5QyxDQUFDLENBQUM7OztFQUdILEtBQUssQ0FBQyxnQkFBZ0IsQ0FBQyxTQUFTLEVBQUUsWUFBWTtHQUM3QyxJQUFJLFlBQVksQ0FBQyxPQUFPLENBQUMsS0FBSyxDQUFDLFdBQVcsR0FBRyxHQUFHLEdBQUcsQ0FBQyxHQUFHLEdBQUcsQ0FBQyxHQUFHLENBQUMsRUFBRTs7SUFFaEUsTUFBTSxDQUFDLE1BQU0sQ0FBQyxXQUFXLEdBQUcsS0FBSyxDQUFDLFdBQVcsQ0FBQztJQUM5QztHQUNELENBQUMsQ0FBQztFQUNIO0NBQ0Q7O0FBRUQsU0FBUyxXQUFXLENBQUMsS0FBSyxFQUFFO0NBQzNCLElBQUksTUFBTSxHQUFHLEtBQUssQ0FBQyxDQUFDLENBQUMsQ0FBQztDQUN0QixLQUFLLENBQUMsS0FBSyxDQUFDLEdBQUcsS0FBSyxDQUFDLElBQUksQ0FBQztDQUMxQixLQUFLLENBQUMsTUFBTSxDQUFDLEdBQUcsS0FBSyxDQUFDLEtBQUssQ0FBQztDQUM1QixLQUFLLENBQUMsSUFBSSxHQUFHLElBQUksQ0FBQztDQUNsQixLQUFLLENBQUMsS0FBSyxHQUFHLEtBQUssQ0FBQztDQUNwQixhQUFhLENBQUMsS0FBSyxFQUFFLFFBQVEsRUFBRSxNQUFNLENBQUMsTUFBTSxDQUFDLENBQUM7Q0FDOUMsYUFBYSxDQUFDLEtBQUssRUFBRSxPQUFPLEVBQUUsTUFBTSxDQUFDLE1BQU0sRUFBRSxJQUFJLENBQUMsQ0FBQztDQUNuRCxhQUFhLENBQUMsS0FBSyxFQUFFLGNBQWMsRUFBRSxNQUFNLENBQUMsTUFBTSxFQUFFLElBQUksQ0FBQyxDQUFDO0NBQzFELGFBQWEsQ0FBQyxLQUFLLEVBQUUsT0FBTyxFQUFFLE1BQU0sQ0FBQyxNQUFNLENBQUMsQ0FBQztDQUM3QyxhQUFhLENBQUMsS0FBSyxFQUFFLE1BQU0sRUFBRSxNQUFNLENBQUMsTUFBTSxFQUFFLElBQUksQ0FBQyxDQUFDO0NBQ2xELFlBQVksQ0FBQyxLQUFLLEVBQUUsU0FBUyxDQUFDLENBQUM7Q0FDL0IsWUFBWSxDQUFDLEtBQUssRUFBRSxRQUFRLENBQUMsQ0FBQztDQUM5QixZQUFZLENBQUMsS0FBSyxFQUFFLFlBQVksRUFBRSxNQUFNLEVBQUUsS0FBSyxDQUFDLENBQUM7Q0FDakQsWUFBWSxDQUFDLEtBQUssRUFBRSxPQUFPLEVBQUUsTUFBTSxFQUFFLEtBQUssQ0FBQyxDQUFDO0NBQzVDOztBQUVELFNBQVMsaUJBQWlCLENBQUMsS0FBSyxFQUFFLFFBQVEsRUFBRSxlQUFlLEVBQUU7Q0FDNUQsS0FBSyxRQUFRLEtBQUssS0FBSyxDQUFDLEdBQUcsRUFBQSxRQUFRLEdBQUcsSUFBSSxDQUFDLEVBQUE7Q0FDM0MsS0FBSyxlQUFlLEtBQUssS0FBSyxDQUFDLEdBQUcsRUFBQSxlQUFlLEdBQUcsSUFBSSxDQUFDLEVBQUE7O0NBRXpELElBQUksQ0FBQyxlQUFlLElBQUksQ0FBQyxhQUFhLENBQUMsSUFBSSxLQUFLLENBQUMsQ0FBQyxDQUFDLEVBQUU7RUFDcEQsT0FBTztFQUNQO0NBQ0QsU0FBUyxDQUFDLEtBQUssRUFBRSxRQUFRLENBQUMsQ0FBQztDQUMzQixXQUFXLENBQUMsS0FBSyxDQUFDLENBQUM7Q0FDbkIsS0FBSyxDQUFDLFNBQVMsQ0FBQyxHQUFHLENBQUMsS0FBSyxDQUFDLENBQUM7Q0FDM0IsSUFBSSxDQUFDLFFBQVEsSUFBSSxLQUFLLENBQUMsUUFBUSxFQUFFO0VBQ2hDLEtBQUssQ0FBQyxJQUFJLEVBQUUsQ0FBQztFQUNiO0NBQ0QsSUFBSSxDQUFDLGtCQUFrQixDQUFDLElBQUksQ0FBQyxTQUFTLENBQUMsUUFBUSxDQUFDLEVBQUU7RUFDakQsT0FBTyxDQUFDLElBQUksQ0FBQyx3RUFBd0UsQ0FBQyxDQUFDO0VBQ3ZGO0NBQ0Q7O0FBRUQsaUJBQWlCLENBQUMsYUFBYSxHQUFHLGFBQWEsQ0FBQzs7QUFFaEQ7Ozs7Ozs7Ozs7Ozs7OztBQzVUQSxDQUFDLFlBQVk7O0NBRVpDLElBQU0sdUJBQXVCLEdBQUcsVUFBOEIsQ0FBQztDQUMvRCxJQUFJLEtBQUssR0FBRyxRQUFRLENBQUMsYUFBYSxDQUFDLG1CQUFtQixDQUFDLENBQUM7Q0FDeEQsdUJBQXVCLENBQUMsS0FBSyxFQUFFLENBQUMsS0FBSyxDQUFDLFlBQVksQ0FBQyxPQUFPLENBQUMsQ0FBQyxDQUFDOztDQUU3RCxHQUFHLENBQUE7OyJ9