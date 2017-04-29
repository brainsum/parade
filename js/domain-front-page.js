/**
 * @file
 * JavaScript for domain front page checkboxes.
 */

(function ($) {
  'use strict';

  $(document).ready(function () {
    var frontPageWrapper = $('#edit-field-domain-front-page-wrapper');
    if (frontPageWrapper.length > 0) {
      frontPageWrapper.find('input.form-checkbox:first').on('change', function () {
        if ($(this).is(":checked")) {
          frontPageWrapper.find('input.form-checkbox:not(:first)').prop('checked', false);
        }
      });
      frontPageWrapper.find('input.form-checkbox:not(:first)').on('change', function () {
        if ($(this).is(":checked")) {
          frontPageWrapper.find('input.form-checkbox:first').prop('checked', false);
        }
      });
    }
  })
})(jQuery);