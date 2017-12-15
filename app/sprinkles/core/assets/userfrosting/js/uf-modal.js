/**
 * ufModal plugin.  Handles modal windows that dynamically their fetch content from a specified URL.
 *
 *
 * UserFrosting https://www.userfrosting.com
 * @author Alexander Weissman <https://alexanderweissman.com>
 */
;(function($, window, document, undefined) {
    'use strict';

    // Define plugin name and defaults.
    var pluginName = 'ufModal',
    defaults = {
        sourceUrl : '',
        ajaxParams: {},
        msgTarget : null,
        DEBUG     : false
    };

    // Constructor
    function Plugin (element, options) {
        this.element = element[0];
        this.$element = $(this.element);
        this.settings = $.extend(true, {}, defaults, options);
        this._defaults = defaults;
        this._name = pluginName;

        // True plugin initalisation commences
        this.modal = null;

        // Delete any existing modals attached to the element (should have been deleted already anyway)
        if (this.$element.find('.modal').length) this.$element.find('.modal').remove();

        // Fetch and render form
        $.ajax({
            context: this,
            type: 'GET',
            url: this.settings.sourceUrl,
            data: this.settings.ajaxParms,
            cache: false
        }).then(
            // Success
            function (data) {
                // Append the data as a modal dialog to the target element
                this.modal = $(data);
                this.$element.append(this.modal);

                // Trigger modal dialog
                this.modal.modal('show');

                // Bind destroy function to close event
                this.modal.on('hidden.bs.modal', function () { this.destroy(); }.bind(this));

                // Trigger success event
                this.$element.trigger('renderSuccess.ufModal');
            },
            // Failure
            function (data) {
                // Handle error messages
                if (site !== undefined && site.debug.ajax && data.responseText) {
                    // Trigger failure event
                    this.$element.trigger('renderError.ufModal');

                    // Replace document content with response, and handle browser quirks
                    document.write(data.responseText);
                    document.close();
                } else {
                    // Debug logging
                    if (this.settings.DEBUG) console.log('Error (' + data.status + '): ' + data.responseText);

                    // Refresh ufAlerts for errors if target defined
                    if (this.settings.msgTarget) {
                        // Check if ufAlerts is instanced and empty
                        if (!this.settings.msgTarget.data('ufAlerts')) this.settings.msgTarget.ufAlerts();
                        else this.settings.msgTarget.ufAlerts('clear');

                        // Trigger failure event on render.ufAlerts event
                        this.settings.msgTarget.on('render.ufAlerts', function () {
                            this.$element.trigger('renderError.ufModal');
                        }.bind(this));

                        // Pull alerts
                        this.settings.msgTarget.ufAlerts('fetch').ufAlerts('render');
                    } else {
                        // renderError.ufModal event should always be able to trigger
                        this.$element.trigger('renderError.ufModal');
                    }
                }
            }
        )
    }

    $.extend(Plugin.prototype, {
        /**
         * Destroys instance
         */
        destroy: function () {
            // Remove modal from selector
            if (this.modal) this.modal.remove();

            // Unbind plugin events
            this.$element.off('.' + this._name);

            // Remove plugin data from internal jQuery store (jQuery doesn't store with data-*, but can access it)
            this.$element.removeData(this._name);

            return this.$element;
        },
        /**
         * Returns underlying modal
         */
        getModal: function () {
            return this.modal;
        }
    });
    
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
                $.error('Method ' +  methodOrOptions + ' is private!');
            }
        }
        else {
            $.error('Method ' +  methodOrOptions + ' does not exist.');
        }
    };
})(jQuery, window, document);