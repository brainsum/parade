/**
 * @file
 * Initialize ScrollSpy scripts
 */

import '../../vendor/jquery-scrollspy'

($ => {

    let targets = $('.field--name-field-menu, .tieto-campaign-page > .logo, #hamburger')

    targets.scrollspy({
        min: 490,
        max: 50000,
        onEnter: function() {
            targets.removeClass('not-fixed')
            targets.addClass('fixed')
        },
        onLeave: function() {
            targets.removeClass('fixed')
            targets.addClass('not-fixed')
        }
    })

    $(document).ready(() => { targets.trigger('scroll.scrollspy') })

})(jQuery)
