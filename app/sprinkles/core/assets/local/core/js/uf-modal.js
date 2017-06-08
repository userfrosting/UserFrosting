/**
 * ufModal plugin.  Handles modal windows that dynamically their fetch content from a specified URL.
 *
 *
 * UserFrosting https://www.userfrosting.com
 * @author Alexander Weissman <https://alexanderweissman.com>
 */
;(function($, window, document, undefined) {
	"use strict";

    // Define plugin name and defaults.
    var pluginName = "ufModal",
        defaults = {
            sourceUrl : "",
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

        // Detect changes to element attributes
        this.$element.attrchange({ callback: function (event) { this.element = event.target; }.bind(this) });

        // Plugin initalisation
        this.modal = null;

        // Delete any existing modals attached to the element (should have been deleted already anyway)
        if (this.$element.find(".modal").length) {
            this.$element.find(".modal").remove();
        }

        // Fetch and render the form
        $.ajax({
          type: "GET",
          url: this.settings.sourceUrl,
          data: this.settings.ajaxParams,
          cache: false
        })
        .then($.proxy(
            // Fetch successful
            function (data) {
                // Append the form as a modal dialog to the body
                this.modal = $(data);
                this.$element.append(this.modal);

                this.modal.modal('show');

                // Bind modal to be deleted when closed
                this.modal.on("hidden.bs.modal", function () { this.destroy(); }.bind(this));

                this.$element.trigger('renderSuccess.ufModal');
                return data;
            }, this),
            $.proxy(
            // Fetch failed
            function (data) {
                // Error messages
                if ((typeof site !== "undefined") && site.debug.ajax && data.responseText) {
                    this.$element.trigger('renderError.ufModal');
                    document.write(data.responseText);
                    document.close();
                } else {
                    if (this.settings.DEBUG) {
                        console.log("Error (" + data.status + "): " + data.responseText );
                    }
                    // Display errors on failure
                    // TODO: ufAlerts widget should have a 'destroy' method (UPDATE: It does)
                    if (!this.settings.msgTarget.data('ufAlerts')) {
                        this.settings.msgTarget.ufAlerts();
                    } else {
                        this.settings.msgTarget.ufAlerts('clear');
                    }

                    this.settings.msgTarget.ufAlerts('fetch').ufAlerts('render');
                    this.settings.msgTarget.on("render.ufAlerts", $.proxy(function () {
                        this.$element.trigger('renderError.ufModal');
                    }, this));
                }

                this.destroy();

                return data;
            }, this)
        );

        return this;
    }

    // Functions
    $.extend(Plugin.prototype, {
        /**
         * Returns underlying model
         */
        getModal: function() {
            return this.modal;
        },
        /**
         * Destroys instance
         */
        destroy: function() {
            // Remove the modal from the DOM
            if (this.modal) {
                this.modal.remove();
            }

            // Unbind any bound events
            this.modal.off('.' + this.pluginName);

            // Grab jQuery wrapped element before plugin destruction
            var $element = this.$element;

            // Remove plugin from element
            this.$element.removeData(this.pluginName);

            return $element;
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
                $.error( 'Method ' +  methodOrOptions + ' is private!' );
            }
        }
        else {
            $.error( 'Method ' +  methodOrOptions + ' does not exist.' );
        }
    };
})(jQuery, window, document);

