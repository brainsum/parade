/**
 * @file
 * JavaScript for domain specific ga checkboxes and textfields.
 */

(function ($) {
  'use strict';

  $(document).ready(function () {
    var domainSpecificSettings = $('#edit-domain-specific');
    if (domainSpecificSettings.length > 0) {
      domainSpecificSettings.find('.google-analytics-account-domains input.form-text').keypress(function () {
        if ($(this).parents('.google-analytics-account-domains').find('input.form-checkbox').is(":checked")) {
          $(this).parents('.google-analytics-account-domains').find('input.form-checkbox').prop('checked', false);
        }
      });
      domainSpecificSettings.find('.google-analytics-account-domains input.form-checkbox').on('change', function () {
        if ($(this).is(":checked")) {
          $(this).parents('.google-analytics-account-domains').find('input.form-text').val('');
        }
      });
    }
  })
})(jQuery);