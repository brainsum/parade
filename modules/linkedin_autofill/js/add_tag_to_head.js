/**
 * @file
 * Contains the definition of the behaviour addLinkedinScriptTag.
 */

(function ($, Drupal, drupalSettings) {

    'use strict';

    /**
     * Attaches the JS test behavior to to weight div.
     */
    Drupal.behaviors.addLinkedinScriptTag = {
        attach: function (context, settings) {
            var formId = 'mktoForm_' + drupalSettings.addTagToHead.dataFormId;
            var tag = $('<script ' +
                'type="IN/Form2" ' +
                'data-form="' + formId + '" ' +
                'data-field-firstname="FirstName" ' +
                'data-field-lastname="LastName" ' +
                'data-field-phone="MobilePhone" ' +
                'data-field-email="Email" ' +
                'data-field-company="Company" ' +
                'data-field-title="Title" ' +
                'data-field-city="City" ' +
                'data-field-state="State" ' +
                'data-field-country="Country" ' +
                'data-field-zip="ZipCode"' +
                '></script>');

            // @todo: Maybe add it before $('#' + formId) like in the template?
            $('head').append(tag);
        }
    };
})(jQuery, Drupal, drupalSettings);