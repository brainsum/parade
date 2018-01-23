/**
 * @file
 * JavaScript for quick edit functionality in Geysir.
 */

(function ($, Drupal) {
    "use strict";

    Drupal.behaviors.parade_edit = {
        attach(context) {
            $(".geysir-field-paragraph-wrapper").each(function (i, e) {
                var $elm = $(e).find(".geysir-field-paragraph-links .parade-edit-quickedit .button");
                $elm.click(function () {
                    $(e).find(".contextual-links:first .quickedit a").trigger("click");
                });
            });
        }
    };

})(jQuery, Drupal);
