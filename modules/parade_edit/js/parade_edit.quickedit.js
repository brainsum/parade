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

      // @todo - refactor. + it's runs 5 times.
      // Select the node that will be observed for mutations.
      var targetNode = document.body;

      // Options for the observer (which mutations to observe).
      var config = {childList: true};

      // Callback function to execute when mutations are observed.
      var callback = function (mutationsList) {
        for (var mutation of mutationsList) {
          // Todo: refactor, probably there's a better way to do this.
          if (mutation.type == 'childList' && mutation.target.nodeName === "BODY" &&
            mutation.removedNodes.length > 0 && mutation.removedNodes[0].id === "quickedit-toolbar-fence") {
            $(".geysir-field-paragraph-wrapper").removeClass("quickedit-active");
            $(".quickedit-entity-active").closest(".geysir-field-paragraph-wrapper").addClass("quickedit-active");
            // This trigger needed to fix inplace edit bug, to "select" one
            // item.
            $('.quickedit-entity-active .quickedit-editable:eq(0)').trigger('mouseover');
          }
        }
      };

      // Create an observer instance linked to the callback function.
      var observer = new MutationObserver(callback);

      // Start observing the target node for configured mutations.
      observer.observe(targetNode, config);
    }
  };

})(jQuery, Drupal);
