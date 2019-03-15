/**
 * uf-form plugin.  Handles validation and submission for basic UserFrosting forms.
 *
 * This plugin uses the jQueryvalidation plugin (https://jqueryvalidation.org/) to perform instant, client-side form validation.
 * UserFrosting forms must be wrapped in a <form> element, and contain a <button type=submit> element for submission.
 *
 * Forms are then set to submit via AJAX when the submit button is clicked.
 *
 * === USAGE ===
 *
 * uf-form can be initialized on a form element as follows:
 *
 * $('#myForm').ufForm(options);
 *
 * `options` is an object containing any of the following parameters:
 * @param {JSON} validator An object containing two keys, "rules" and "messages", which specify the jQueryvalidation rules to use.
 * @param {Object} msgTarget a jQuery selector specifying the element where any error messages should be inserted.  Defaults to looking for a container with class .js-form-alerts inside this form.
 * @param {Callback} beforeSubmitCallback a callback function to execute immediately after form validation, before the form is submitted.
 * @param {bool} binaryCheckboxes specify whether to submit checkboxes as binary values 0 and 1, instead of omitting unchecked checkboxes from submission.
 *
 * == EVENTS ==
 *
 * ufForm triggers the following events:
 *
 * `submitSuccess.ufForm`: triggered when the form is successfully submitted, after re-enabling the submit button.
 * `submitError.ufForm`: triggered when the form submission (not validation) fails, after re-enabling the submit button
 * and displaying any error messages.
 *
 * UserFrosting https://www.userfrosting.com
 * @author Alexander Weissman <https://alexanderweissman.com>
 *
 * @todo Implement proper fallback for when `set` function isn't supported by FormData.
 */
;
(function ($, window, document, undefined) {
    'use strict';

    // Define plugin name and defaults.
    var pluginName = 'ufForm',
        defaults = {
            DEBUG: false,
            site: site, // global site variables
            submittingText: "<i class='fa fa-spinner fa-spin'></i>",
            beforeSubmitCallback: null,
            binaryCheckboxes: true, // submit checked/unchecked checkboxes as 0/1 values
            keyupDelay: 0,
            showAlertOnSuccess: false,
            // These are options that will be passed to jQuery Validate
            // See https://jqueryvalidation.org/validate/#-validate()
            validator: {
                rules: {},
                messages: {}
            },
            // Deprecated
            validators: {
                rules: {},
                messages: {}
            }
        };

    // Constructor
    function Plugin(element, options) {
        this.element = element[0];
        this.$element = $(this.element);

        var lateDefaults = {
            submitButton: this.$element.find('button[type=submit]'),
            msgTarget: this.$element.find('.js-form-alerts:first'),
            // These are options that will be passed to jQuery Validate
            // See https://jqueryvalidation.org/validate/#-validate()
            validator: {
                submitHandler: $.proxy(this.defaultSubmitHandler, this),
                onkeyup: $.proxy(this._onKeyUp, this)
            },
            ajax: {
                // Override jQuery's strict JSON parsing
                converters: {
                    'text json': $.proxy(this._defaultResponseParser, this)
                },
                // Response type
                dataType: this._debugAjax ? 'html' : 'json',
                // Disable the submit button before sending the request
                beforeSend: $.proxy(this.disableSubmit, this),
                // enable the submit button once the request completes
                complete: $.proxy(this.enableSubmit, this)
            }
        };

        // Legacy options
        if ((typeof options !== 'undefined') && 'validators' in options) {
            options.validator = options.validators;
        }

        this.settings = $.extend(true, {}, defaults, lateDefaults, options);

        this._defaults = $.extend(true, {}, defaults, lateDefaults);
        this._name = pluginName;
        this._debugAjax = (typeof site !== 'undefined') && site.debug.ajax;

        this.submitButtonText = this.settings.submitButton ? this.settings.submitButton.html() : '';

        // Detect changes to element attributes
        this.$element.attrchange({
            callback: function (event) {
                this.element = event.target;
            }.bind(this)
        });

        // Setup validator
        this.validator = this.$element.validate(this.settings.validator);

        return this;
    }

    // Functions

    /**
     * Set "loading" text for submit button, if it exists, and disable button
     */
    Plugin.prototype.disableSubmit = function () {
        var submitButton = this.settings.submitButton;
        // Do nothing, if the button is already disabled
        if (submitButton.prop('disabled')) {
            return this;
        }

        if (!submitButton) {
            console.error('Submit button not found.');
            return this;
        }

        this.submitButtonText = submitButton.html();
        submitButton.prop('disabled', true);
        submitButton.html(this.settings.submittingText);
        return this;
    };

    /**
     * Restore button text and re-enable submit button
     *
     * @return this
     */
    Plugin.prototype.enableSubmit = function () {
        var submitButton = this.settings.submitButton;

        // Do nothing, if the button is already enabled
        if (!submitButton.prop('disabled')) {
            return this;
        }

        if (!submitButton) {
            console.error('Submit button not found.');
            return this;
        }

        submitButton.prop('disabled', false);
        submitButton.html(this.submitButtonText);
        return this;
    };

    /**
     * Handles the form submission after successful client-side validation.
     *
     * @param {Element} form
     * @param {Event}   event
     */
    Plugin.prototype.defaultSubmitHandler = function (form, event) {
        // Execute any "before submit" callback
        if (this.settings.beforeSubmitCallback) {
            this.settings.beforeSubmitCallback();
        }

        // Get basic request parameters.
        var ajaxParams = this.prepareRequestData(form, this.settings.ajax);

        // Submit the form
        this.submitForm(ajaxParams);
    };

    /**
     * Submit the form via AJAX
     * @param   {object} reqParams
     * @returns {Deferred}
     */
    Plugin.prototype.submitForm = function (reqParams) {
        return $.ajax(reqParams).then(
            // Submission successful
            $.proxy(this._defaultSuccess, this),
            // Submission failed
            $.proxy(this._defaultError, this)
        );
    }

    /**
     * Default handler for AJAX request success.
     *
     * @param {object} data
     * @param {string} textStatus
     * @param {jqXHR}  jqXHR
     * @return {jqXHR}
     */
    Plugin.prototype._defaultSuccess = function (data, textStatus, jqXHR) {
        this.$element.trigger('submitSuccess.ufForm', [data, textStatus, jqXHR]);
        if (this.settings.showAlertOnSuccess) {
            // showing UF alerts on success also. 
            // This will show the Alerts from the Alert stream when the page is not reloaded after the ufForm submit succeeds,
            if (!this.settings.msgTarget.data('ufAlerts')) {
                this.settings.msgTarget.ufAlerts();
            } else {
                this.settings.msgTarget.ufAlerts('clear');
            }

            this.settings.msgTarget.ufAlerts('fetch').ufAlerts('render');
        }
        return jqXHR;
    };

    /**
     * Default handler for AJAX request fail/error.
     *
     * @param {jqXHR}  jqXHR
     * @param {string} textStatus
     * @param {string} errorThrown
     * @return {jqXHR}
     */
    Plugin.prototype._defaultError = function (jqXHR, textStatus, errorThrown) {
        // Error messages
        if (this._debugAjax && jqXHR.responseText) {
            this.$element.trigger('submitError.ufForm', [jqXHR, textStatus, errorThrown]);
            document.write(jqXHR.responseText);
            document.close();
        } else {
            if (this.settings.DEBUG) {
                console.error(jqXHR.status + ': ' + jqXHR.responseText);
            }
            // Display errors on failure
            // TODO: ufAlerts widget should have a 'destroy' method
            if (!this.settings.msgTarget.data('ufAlerts')) {
                this.settings.msgTarget.ufAlerts();
            } else {
                this.settings.msgTarget.ufAlerts('clear');
            }

            this.settings.msgTarget.ufAlerts('fetch').ufAlerts('render');
            this.settings.msgTarget.on('render.ufAlerts', $.proxy(function () {
                this.$element.trigger('submitError.ufForm', [jqXHR, textStatus, errorThrown]);
            }, this));
        }
        return jqXHR;
    };

    /**
     * Default parser for text/json ajax response from server.
     *
     * @param {string} result
     * @return {object}
     */
    Plugin.prototype._defaultResponseParser = function (result) {
        try {
            // First try to use native browser parsing
            if (typeof JSON === 'object' && typeof JSON.parse === 'function') {
                return JSON.parse(result);
            } else {
                return $.parseJSON(result);
            }
        } catch (e) {
            // statements to handle any exceptions
            console.warn('Could not parse expected JSON response.');
            return {};
        }
    };

    /**
     * Prepares the ajax request parameters, serializing the form according to its enctype,
     * and then using the serialized data as the ajax `data` parameter.
     *
     * @param {Element} form
     * @param {object}  ajaxParams
     * @return {object}
     */
    Plugin.prototype.prepareRequestData = function (form, ajaxParams) {
        // Set ajax type and url from form method and action, if not otherwise set.
        var ajaxDefaults = {
            type: this.$element.attr('method'),
            url: this.$element.attr('action'),
            contentType: (typeof this.$element.attr('enctype') === 'undefined') ?
                'application/x-www-form-urlencoded; charset=UTF-8' : this.$element.attr('enctype')
        };

        ajaxParams = $.extend(true, {}, ajaxDefaults, ajaxParams);

        // Get the form encoding type from the users HTML, and chose an encoding form.
        if (ajaxParams.contentType.toLowerCase() === 'multipart/form-data') {
            ajaxParams.data = this._multipartData(form);
            // Disable caching
            ajaxParams.cache = false;
            // Prevent serialization of FormData object
            ajaxParams.processData = false;
            // Allow the browser to set the Content-Type header instead,
            // so it can include the boundary value
            ajaxParams.contentType = false;
        } else {
            ajaxParams.data = this._urlencodeData(form);
        }

        return ajaxParams;
    };

    /**
     * Helper function for encoding data as urlencoded
     *
     * @param {Element} form
     * @return {string}
     */
    Plugin.prototype._urlencodeData = function (form) {
        // Serialize and post to the backend script in ajax mode
        var serializedData;
        if (this.settings.binaryCheckboxes) {
            serializedData = $(form).find(':input').not(':checkbox').serialize();
            // Get unchecked checkbox values, set them to 0
            $(form).find('input[type=checkbox]:enabled').each(function () {
                if ($(this).is(':checked')) {
                    serializedData += '&' + encodeURIComponent(this.name) + '=1';
                } else {
                    serializedData += '&' + encodeURIComponent(this.name) + '=0';
                }
            });
        } else {
            serializedData = $(form).find(':input').serialize();
        }

        return serializedData;
    };

    /**
     * Helper function for encoding data as multipart/form-data
     *
     * @param {Element} form
     * @return {FormData}
     */
    Plugin.prototype._multipartData = function (form) {
        // Use FormData to wrap form contents.
        // https://developer.mozilla.org/en/docs/Web/API/FormData
        var formData = new FormData(form);
        // Serialize and post to the backend script in ajax mode
        if (this.settings.binaryCheckboxes) {
            // Get unchecked checkbox values, set them to 0.
            var checkboxes = $(form).find('input[type=checkbox]:enabled');
            // Feature detection. Several browsers don't support `set`
            if (typeof formData.set !== 'function') {
                this.settings.msgTarget.ufAlerts('push', 'danger', "Your browser is missing a required feature. This form will still attempt to submit, but if it fails, you'll need to use Chrome for desktop or FireFox for desktop.");
            } else {
                checkboxes.each(function () {
                    if ($(this).is(':checked')) {
                        // this replaces checkbox value with 1 (as we're using binaryCheckboxes).
                        formData.set(this.name, 1);
                        // this explicitly adds unchecked boxes.
                    } else {
                        formData.set(this.name, 0);
                    }
                });
            }
        }

        return formData;
    };

    /**
     * Implements a delay for revalidating the form.
     *
     * @param {Element} form
     * @param {Event}   event
     */
    Plugin.prototype._onKeyUp = function (element, event) {
        var validator = this.validator;
        // See http://stackoverflow.com/questions/41363409/jquery-validate-add-delay-to-keyup-validation
        setTimeout(function () {
            // Avoid revalidate the field when pressing one of the following keys
            // Shift       => 16
            // Ctrl        => 17
            // Alt         => 18
            // Caps lock   => 20
            // End         => 35
            // Home        => 36
            // Left arrow  => 37
            // Up arrow    => 38
            // Right arrow => 39
            // Down arrow  => 40
            // Insert      => 45
            // Num lock    => 144
            // AltGr key   => 225
            var excludedKeys = [
                16, 17, 18, 20, 35, 36, 37,
                38, 39, 40, 45, 144, 225
            ];

            if (event.which === 9 && validator.elementValue(element) === '' || $.inArray(event.keyCode, excludedKeys) !== -1) {
                return;
            } else if (element.name in validator.submitted || element.name in validator.invalid) {
                validator.element(element);
            }
        }, this.settings.keyupDelay);
    };

    // Handles instantiation and access to non-private methods.
    $.fn[pluginName] = function (methodOrOptions) {
        // Grab plugin instance
        var instance = $(this).data(pluginName);
        // If undefined or object, initalise plugin.
        if (methodOrOptions === undefined || typeof methodOrOptions === 'object') {
            // Only initalise if not previously done.
            if (!instance) {
                $(this).data(pluginName, new Plugin(this, methodOrOptions));
            }
            return this;
        }
        // Otherwise ensure first parameter is a valid string, and is the name of an actual function.
        else if (typeof methodOrOptions === 'string' && typeof instance[methodOrOptions] === 'function') {
            // Ensure not a private function
            if (methodOrOptions.indexOf('_') !== 0) {
                return instance[methodOrOptions](Array.prototype.slice.call(arguments, 1));
            } else {
                console.warn('Method ' + methodOrOptions + ' is private!');
            }
        } else {
            console.warn('Method ' + methodOrOptions + ' does not exist.');
        }
    };
})(jQuery, window, document);