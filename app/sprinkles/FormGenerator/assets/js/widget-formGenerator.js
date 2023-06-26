/*!
 * FormGenerator Plugin
 *
 * JQuery plugin for the UserFrosting FormGenerator Sprinkle
 * Based on UserFrosting v3
 *
 * @package UF_FormGenerator
 * @author Louis Charette
 * @link https://github.com/lcharette/UF_FormGenerator
 * @license MIT
 */

;(function($, window, document, undefined) {
	"use strict";

    // Define plugin name and defaults.
    var pluginName = "formGenerator",
        defaults = {
            DEBUG                   : false,
            mainAlertElement        : $('#alerts-page'),
            redirectAfterSuccess    : true,
            autofocusModalElement   : true,
            successCallback         : function(data) {}
        };

    // Constructor
    function Plugin (element, options) {
        this.elements = element;
        this.$elements = $(this.elements);
        this.settings = $.extend(true, {}, defaults, options);
        this._defaults = defaults;
        this._name = pluginName;

        // Detect changes to element attributes
        this.$elements.attrchange({ callback: function (event) { this.elements = event.target; }.bind(this) });

        // Initialise ufAlerts
        if (!this.settings.mainAlertElement.data('ufAlerts')) {
            this.settings.mainAlertElement.ufAlerts();
        }

        return this;
    }

    // Functions
    $.extend(Plugin.prototype, {
        /**
         * Bind the display action for a form to the button
         */
        display: function() {
            this.$elements.on('click', $.proxy(this._fetchForm, this));
            return this.$elements;
        },
        /**
         * Bind the confirm action to the button
         */
        confirm: function() {
            this.$elements.on('click', $.proxy(this._fetchConfirmModal, this));
            return this.$elements;
        },
        /**
         * Fetch the form HTML
         */
        _fetchForm: function(event) {

            // Get the button element
            var button = event.currentTarget;

            // Get the box_id. Define one if none is defined
            var box_id = $(button).data('target');
            if (box_id == undefined) {
                box_id = "formGeneratorModal";
            }

            // Delete any existing instance of the form with the same name
            if($('#' + box_id).length) {
                $('#' + box_id).remove();
            }

            // Prepare the ajax payload
            var payload = $.extend({
                box_id: box_id}
            , button.dataset);

            // Fetch and render the form
            $.ajax({
                type: "GET",
                url: $(button).data('formurl'),
                data: payload,
                cache: false
            })
            .done($.proxy(this._displayForm, this, box_id, button))
            .fail($.proxy(this._displayFailure, this, button));
        },
        /**
         * Displays the form modal and set up ufForm
         */
        _displayForm: function(box_id, button, data) {

            // Trigger pre-display event
            $(button).trigger("displayForm." + this._name);

            // Append the form as a modal dialog to the body
            $( "body" ).append(data);
            $('#' + box_id).modal('show');

            // Set focus on first element
            if (this.settings.autofocusModalElement) {
                $('#' + box_id).on('shown.bs.modal', function () {
                    $(this).find(".modal-body").find(':input:enabled:visible:first').focus();
                });
            }

            // Setup ufAlerts
            var boxMsgTarget = $("#"+box_id+" #form-alerts");

            // Show the alert. We could have info alert coming in
            if (!boxMsgTarget.data('ufAlerts')) {
                boxMsgTarget.ufAlerts();
            }
            boxMsgTarget.ufAlerts('clear').ufAlerts('fetch').ufAlerts('render');

            // Setup the loaded form with ufForm
            $('#' + box_id).find("form").ufForm({
                validators: validators,
                msgTarget: $("#"+box_id+" #form-alerts")
            })
            .on("submitSuccess.ufForm", $.proxy(this._formPostSuccess, this, box_id, button))
            .on("submitError.ufForm", $.proxy(this._displayFormFaillure, this, box_id, button));
        },
        /**
         * Action done when a form is successful
         */
        _formPostSuccess: function(box_id, button, event, data) {

            // Trigger success event
            $(button).trigger("formSuccess." + this._name, data);

            // Use the callback
            this.settings.successCallback(data);

            // Refresh page or close modal
            if (this.settings.redirectAfterSuccess) {
                window.location.reload(true);
            } else {
                $('#' + box_id).modal('hide');
                this.settings.mainAlertElement.ufAlerts('clear').ufAlerts('fetch').ufAlerts('render');
            }
        },
        /**
         * Fetch confirmation modal
         */
        _fetchConfirmModal: function(event) {

            // Get the button element
            var button = event.currentTarget;

            // Get the box_id. Define one if none is defined
            var box_id = $(button).data('target');
            if (box_id == undefined) {
                box_id = "formGeneratorModal";
            }

            // Delete any existing instance of the form with the same name
            if($('#' + box_id).length) {
                $('#' + box_id).remove();
            }

            // Prepare the ajax payload
            var payload = $.extend({
                box_id: box_id,
                box_title: $(button).data('confirmTitle') ? $(button).data('confirmTitle') : null,
                confirm_message: $(button).data('confirmMessage') ? $(button).data('confirmMessage') : null,
                confirm_warning: $(button).data('confirmWarning') ? $(button).data('confirmWarning') : null,
                confirm_button: $(button).data('confirmButton') ? $(button).data('confirmButton') : null,
                cancel_button: $(button).data('cancelButton') ? $(button).data('cancelButton') : null
            }, button.dataset);

            // Fetch and render the form
            $.ajax({
                type: "GET",
                url: $(button).data('formurl') ? $(button).data('formurl') : site['uri']['public'] + "/forms/confirm",
                data: payload,
                cache: false
            })
            .done($.proxy(this._displayConfirmation, this, box_id, button))
            .fail($.proxy(this._displayFailure, this, button));
        },
        /**
         * Display confirmation modal
         */
        _displayConfirmation: function(box_id, button, data) {

            // Trigger pre-display event
            $(button).trigger("displayConfirmation." + this._name);

            // Append the form as a modal dialog to the body
            $( "body" ).append(data);
            $('#' + box_id).modal('show');

            $('#' + box_id + ' .js-confirm').on('click', $.proxy(this._sendConfirmation, this, box_id, button));
        },
        /**
         * Send confirmation  query
         */
        _sendConfirmation: function(box_id, button) {

            // Prepare payload
            var url = $(button).data('postUrl');
            var method = ($(button).data('postMethod')) ? $(button).data('postMethod') : "POST";
            var data = {
                bData: button.dataset,
                csrf_name: $('#' + box_id).find("input[name='csrf_name']").val(),
                csrf_value: $('#' + box_id).find("input[name='csrf_value']").val()
            };

            // Send ajax
            $.ajax({
              type: method,
              url: url,
              data: data
            })
            .done($.proxy(this._confirmationSuccess, this, box_id, button))
            .fail($.proxy(this._displayConfirmationFaillure, this, box_id, button));
        },
         /**
         * Action done when a confirmation request is successful
         */
        _confirmationSuccess: function(box_id, button, data) {

            // Trigger success event
            $(button).trigger("confirmSuccess." + this._name, data);

            // Use the callback
            this.settings.successCallback(data);

            // Refresh page or close modal
            if (this.settings.redirectAfterSuccess) {

                // Redirect if result contains intrusctions to
                if (data.redirect) {
                    window.location.replace(data.redirect);
                } else {
                    window.location.reload(true);
                }
            } else {
                $('#' + box_id).modal('hide');
                this.settings.mainAlertElement.ufAlerts('clear').ufAlerts('fetch').ufAlerts('render');
            }
        },
        /**
         * Failure callback for ajax requests. Displays the error in the main alertElement
         */
        _displayFailure: function(button, response) {
            $(button).trigger("error." + this._name);
            if ((typeof site !== "undefined") && site.debug.ajax && response.responseText) {
                document.write(response.responseText);
                document.close();
            } else {
                if (this.settings.DEBUG) {
                    $.error("Error (" + response.status + "): " + response.responseText );
                }
                this.settings.mainAlertElement.ufAlerts('clear').ufAlerts('fetch').ufAlerts('render');
            }
        },
        /**
         * Faillure callback for ajax requests to be displayed in a modal form
         */
        _displayFormFaillure: function(box_id, button) {
            $(button).trigger("error." + this._name);
            $("#"+box_id+" #form-alerts").show();
        },
        /**
         * Faillure callback for ajax requests to be displayed in a confirmation form
         */
        _displayConfirmationFaillure: function(box_id, button) {
            $(button).trigger("error." + this._name);

            // Setup ufAlerts
            var boxMsgTarget = $("#"+box_id+" #confirmation-alerts");

            // Show the alert. We could have info alert coming in
            if (!boxMsgTarget.data('ufAlerts')) {
                boxMsgTarget.ufAlerts();
            }
            boxMsgTarget.ufAlerts('clear').ufAlerts('fetch').ufAlerts('render');
        },
        /**
         * Completely destroy the ufAlerts plugin on the element.
         */
        destroy: function() {
            // Unbind any bound events
            this.$elements.off('.' + this._name);

            // Grab jQuery wrapped element before plugin destruction
            var $elements = this.$elements;

            // Remove plugin from element
            this.$elements.removeData(this._name);

            return $elements;
        }
    });

    // Handles instantiation and access to non-private methods.
    $.fn[pluginName] = function(methodOrOptions) {

        // If the plugin is called on a non existing element, return nothing
        if (this.length == 0) {
            return this;
        }

        // Grab plugin instance
        var instance = $(this).data(pluginName);

        // If undefined or object, uses the default `display` method.
        if (methodOrOptions === undefined || typeof methodOrOptions === 'object') {
            var method = "display";
            var options = methodOrOptions;
        }
        // Otherwise ensure first parameter is a valid string
        else if (typeof methodOrOptions === 'string') {
            // Ensure not a private function
            if (methodOrOptions.indexOf('_') !== 0) {
                var method = methodOrOptions;
                var options = Array.prototype.slice.call(arguments, 1)[0];
            }
            else {
                $.error( 'Method ' +  methodOrOptions + ' is private!' );
            }
        }
        else {
            $.error( 'Method ' +  methodOrOptions + ' is invalid.' );
        }

        // Only initalise if not previously done.
        if (!instance) {
            $(this).data(pluginName, new Plugin(this, options));
            instance = $(this).data(pluginName);
        }

        // Make sure method exist
        if (typeof instance[method] === 'function') {
            // Run the required method
            return instance[method](options);
        } else {
            $.error( 'Method ' +  method + ' does not exist.' );
        }
    };

    // Apply on default selector
    $(".js-displayForm").formGenerator();
    $(".js-displayConfirm").formGenerator('confirm');

})(jQuery, window, document);