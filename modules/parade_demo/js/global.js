/**
 * @file
 * Global scripts, loaded on every page.
 */

($ => {

	const hamburger = $('#hamburger')

	hamburger.on('click', () => {
		hamburger.toggleClass('is-active')
	})

	// Updates parallax header height with px value instead of vh, so it won't be jumping on Android
	// @todo - remove this from here
	const $header = $('.paragraph--type--header.paragraph--view-mode--default');
	if ($header.length) {
		$header.height($header.height());
	}

})(jQuery)
