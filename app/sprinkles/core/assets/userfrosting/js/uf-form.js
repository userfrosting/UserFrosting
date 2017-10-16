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
 * @param {JSON} validators An object containing two keys, "rules" and "messages", which specify the jQueryvalidation rules to use.
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
;(function($, window, document, undefined) {
	'use strict';

    // Define plugin name and defaults.
    var pluginName = 'ufForm',
        defaults = {
            validators: {
                'rules'   : {},
                'messages': {}
            },
            submittingText      : "<i class='fa fa-spinner fa-spin'></i>",
            beforeSubmitCallback: null,
            binaryCheckboxes    : true,     // submit checked/unchecked checkboxes as 0/1 values
            keyupDelay          : 0,
            DEBUG: false
        };

    // Constructor
    function Plugin (element, options) {
        this.element = element[0];
        this.$element = $(this.element);
        var lateDefaults = {
            encType  : (typeof this.$element.attr('enctype') !== 'undefined') ? this.$element.attr('enctype') : '',
            msgTarget: this.$element.find('.js-form-alerts:first')
        };
        this.settings = $.extend(true, {}, defaults, lateDefaults, options);
        this._defaults = $.extend(true, {}, defaults, lateDefaults);
        this._name = pluginName;
        this._debugAjax = (typeof site !== 'undefined') && site.debug.ajax;

        // Detect changes to element attributes
        this.$element.attrchange({
            callback: function (event) {
                this.element = event.target;
            }.bind(this)
        });

        // Setup validator
        this.validator = this.$element.validate({
            rules        : this.settings.validators.rules,
            messages     : this.settings.validators.messages,
            submitHandler: $.proxy(this._submitHandler, this),
            onkeyup      : $.proxy(this._onKeyUp, this)
        });

        return this;
    }

    // Functions

    /**
     * Handles the form submission after successful client-side validation.
     */
    Plugin.prototype._submitHandler = function(form, event) {
        // Execute any "before submit" callback
        if (this.settings.beforeSubmitCallback) {
            this.settings.beforeSubmitCallback();
        }

        var $form = $(form);

        // Set "loading" text for submit button, if it exists, and disable button
        var submitButton = $form.find('button[type=submit]');
        if (submitButton) {
            var submitButtonText = submitButton.html();
            submitButton.prop('disabled', true);
            submitButton.html(this.settings.submittingText);
        }

        // Get basic request parameters.
        var reqParams = {
            converters: {
                // Override jQuery's strict JSON parsing
                'text json': function(result) {
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
                }
            },

            dataType: this._debugAjax ? 'html' : 'json',
            type: this.$element.attr('method'),
            url: this.$element.attr('action')
        };

        // Get the form encoding type from the users HTML, and chose an encoding form.
        if (this.settings.encType.toLowerCase() === 'multipart/form-data') {
            reqParams.data = this._multipartData($form);
            // add additional params to fix jquery errors
            reqParams.cache = false;
            reqParams.contentType = false;
            reqParams.processData = false;
        } else {
            reqParams.data = this._urlencodeData($form);
        }

        // Submit the form via AJAX
        $.ajax(reqParams).then(
            // Submission successful
            $.proxy(function(data, textStatus, jqXHR) {
                // Restore button text and re-enable submit button
                if (submitButton) {
                    submitButton.prop('disabled', false );
                    submitButton.html(submitButtonText);
                }

                this.$element.trigger('submitSuccess.ufForm', [data, textStatus, jqXHR]);
                return jqXHR;
            }, this),
            // Submission failed
            $.proxy(function(jqXHR, textStatus, errorThrown) {
                // Restore button text and re-enable submit button
                if (submitButton) {
                    submitButton.prop('disabled', false );
                    submitButton.html(submitButtonText);
                }
                // Error messages
                if (this._debugAjax && jqXHR.responseText) {
                    this.$element.trigger('submitError.ufForm', [jqXHR, textStatus, errorThrown]);
                    document.write(jqXHR.responseText);
                    document.close();
                } else {
                    if (this.settings.DEBUG) {
                        console.log('Error (' + jqXHR.status + '): ' + jqXHR.responseText );
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
            }, this)
        );
    };

    /**
     * Helper function for encoding data as urlencoded
     */
    Plugin.prototype._urlencodeData = function(form) {
        // Serialize and post to the backend script in ajax mode
        var serializedData;
        if (this.settings.binaryCheckboxes) {
            serializedData = form.find(':input').not(':checkbox').serialize();
            // Get unchecked checkbox values, set them to 0
            form.find('input[type=checkbox]:enabled').each(function() {
                if ($(this).is(':checked')) {
                    serializedData += '&' + encodeURIComponent(this.name) + '=1';
                } else {
                    serializedData += '&' + encodeURIComponent(this.name) + '=0';
                }
            });
        }
        else {
            serializedData = form.find(':input').serialize();
        }

        return serializedData;
    };

    /**
     * Helper function for encoding data as multipart/form-data
     */
    Plugin.prototype._multipartData = function(form) {
        // Use FormData to wrap form contents.
        // https://developer.mozilla.org/en/docs/Web/API/FormData
        var formData = new FormData(form[0]);
        // Serialize and post to the backend script in ajax mode
        if (this.settings.binaryCheckboxes) {
            // Get unchecked checkbox values, set them to 0.
            var checkboxes = form.find('input[type=checkbox]:enabled');
            // Feature detection. Several browsers don't support `set`
            if (typeof formData.set !== 'function') {
                this.settings.msgTarget.ufAlerts('push', 'danger', "Your browser is missing a required feature. This form will still attempt to submit, but if it fails, you'll need to use Chrome for desktop or FireFox for desktop.");
            } else {
                checkboxes.each(function() {
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

    Plugin.prototype._onKeyUp = function(element, event) {
        var validator = this.validator;
        // See http://stackoverflow.com/questions/41363409/jquery-validate-add-delay-to-keyup-validation
        setTimeout(function() {
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

            if ( event.which === 9 && validator.elementValue( element ) === '' || $.inArray( event.keyCode, excludedKeys ) !== -1 ) {
                return;
            } else if ( element.name in validator.submitted || element.name in validator.invalid ) {
                validator.element( element );
            }
        }, this.settings.keyupDelay);
    };

    // Handles instantiation and access to non-private methods.
    $.fn[pluginName] = function(methodOrOptions) {
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
                return instance[methodOrOptions]( Array.prototype.slice.call(arguments, 1));
            }
            else {
                console.warn( 'Method ' +  methodOrOptions + ' is private!' );
            }
        }
        else {
            console.warn( 'Method ' +  methodOrOptions + ' does not exist.' );
        }
    };
})(jQuery, window, document);
