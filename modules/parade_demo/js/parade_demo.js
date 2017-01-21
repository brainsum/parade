/**
 * @file
 * Global scripts, loaded on every page.
 */

($ => {

	const hamburger = $('#hamburger')

	hamburger.on('click', () => {
		hamburger.toggleClass('is-active')
	})

})(jQuery)
