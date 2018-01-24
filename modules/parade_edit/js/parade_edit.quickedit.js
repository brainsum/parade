/**
 * @file
 * JavaScript for quick edit functionality in Geysir.
 */

(function ($, Drupal) {
    "use strict";

    Drupal.behaviors.parade_edit = {
        attach(context) {
            $(".geysir-field-paragraph-wrapper", context).each(function (i, e) {
                var $elm = $(e).find(".geysir-field-paragraph-links .parade-edit-quickedit .button");
                $elm.click(function () {
                    $(e).find(".contextual-links:first .quickedit a").trigger("click");
                    $(e).addClass("quickedit-active");
                });
            });

            // @todo - refactor.
            // Select the node that will be observed for mutations
            var targetNode = document.body;

            // Options for the observer (which mutations to observe)
            var config = { childList: true };

            // Callback function to execute when mutations are observed
            var callback = function(mutationsList) {
                for (var mutation of mutationsList) {
                    if (mutation.type == 'childList') {
                        // Trigger on quickedit close.
                        $("#quickedit-entity-toolbar").once("button.action-cancel").click(function() {
                            $(".quickedit-entity-active").closest(".geysir-field-paragraph-wrapper").removeClass("quickedit-active");
                        });
                    }
                }
            };

            // Create an observer instance linked to the callback function
            var observer = new MutationObserver(callback);

            // Start observing the target node for configured mutations
            observer.observe(targetNode, config);
        }
    };

})(jQuery, Drupal);
