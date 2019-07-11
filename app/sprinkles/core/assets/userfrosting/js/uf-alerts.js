/**
 * ufAlerts jQuery plugin. Fetches and renders alerts from the UF alert stream.
 * 
 * Based on template from https://github.com/jquery-boilerplate/jquery-boilerplate
 *
 * === USAGE ===

 * ufAlerts can be initialized on any container element as follows:
 *
 * $('#myDiv').ufAlerts(options);
 *
 * `options` is an object containing any of the following parameters:
 * @param {string} url The absolute URL from which to fetch flash alerts.
 * @param {bool} scrollToTop Whether to automatically scroll back to the top of the page after rendering alerts.
 * @param {string} alertMessageClass The CSS class(es) to be applied to each alert message.
 * @param {string} alertTemplateId The CSS id(es) for the Handlebar alert template.
 * @param {bool} agglomerate Set to true to render all alerts together, applying styling for the highest-priority alert being rendered.
 *
 * == EVENTS ==
 *
 * uf-form triggers the following events:
 *
 * `fetch.ufAlerts`: triggered when the alerts have been successfully fetched from the server.
 * `render.ufAlerts`: triggered when all alerts have been rendered and the call to render() has completed.
 *
 * == METHODS ==
 *
 * `fetch()`: Asynchronously gets alerts from the server.
 * `push(options)`: Adds a alert of a specified type (danger, warning, info, success) to the internal collection of alerts.
 * `clear()`: Removes all messages from the internal collection.
 * `render()`: Renders the collection of alerts to the container, awaiting results of `fetch()` if required.
 *
 * UserFrosting https://www.userfrosting.com
 * @author Alexander Weissman <https://alexanderweissman.com>
 */
;(function($, window, document, undefined) {
	'use strict';

    // Define plugin name and defaults.
    var pluginName = 'ufAlerts',
        defaults = {
            url                 : site.uri.public + '/alerts',
            scrollToTop         : true,
            scrollWhenVisible   : false,
            agglomerate         : false,
            alertMessageClass   : 'uf-alert-message',
            alertTemplateId     : 'uf-alert-template',
            DEBUG               : false
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

        // Plugin variables
        this.alerts = [];
        this._newAlertsPromise = $.Deferred().resolve();
        this._alertTemplateHtml = $('#' + this.settings.alertTemplateId).html();
        this._alertTypePriorities = {
            danger : 3,
            warning: 2,
            success: 1,
            info   : 0
        };
        this._alertTypeIcon = {
            danger : 'fa-ban',
            warning: 'fa-warning',
            success: 'fa-check',
            info   : 'fa-info'
        };

        return this;
    }

    // Functions
    $.extend(Plugin.prototype, {
        /**
         * Clear all alerts from the current uf-alerts collection.
         */
        clear: function() {
            // See http://stackoverflow.com/a/1232046/2970321
            this.alerts.length = 0;

            if (this.settings.agglomerate) {
                this.element.toggleClass('alert', false)
                    .toggleClass('alert-info', false)
                    .toggleClass('alert-success', false)
                    .toggleClass('alert-warning', false)
                    .toggleClass('alert-danger', false);
            }

            // Clear any alert HTML
            this.$element.empty();

            return this.$element;
        },
        /**
         * Fetches alerts from the alert stream
         */
        fetch: function() {
            // Set a promise, so that any chained calls after fetch can wait until the messages have been retrieved
            this._newAlertsPromise = $.ajax({
                url: this.settings.url,
                cache: false
            }).then(
                // Success
                this._fetchSuccess.bind(this),
                // Failure
                this._fetchFailure.bind(this)
            );
            
            return this.$element;
        },
        /**
         * Success callback for fetch
         */
        _fetchSuccess: function(alerts) {
            if (alerts != null) this.alerts = $.merge(this.alerts, alerts);
            this.$element.trigger('fetch.' + this._name);
        },
        /**
         * Failure callback for fetch
         */
        _fetchFailure: function(response) {
            this.$element.trigger('error.' + this._name);
            if ((typeof site !== 'undefined') && site.debug.ajax && response.responseText) {
                document.write(response.responseText);
                document.close();
            } else {
                if (this.settings.DEBUG) {
                    console.warn('Error (' + response.status + '): ' + response.responseText );
                }
            }
        },
        /**
         * Push a given message to the current uf-alerts collection.
         */
        push: function(options) {
            this.alerts.push({
                type   : options[0],
                message: options[1]
            });

            return this.$element;
        },
        /**
         * Renders the alerts.
         */
        render: function() {
            // Wait for promise completion, only if promise is unresolved.
            if (this._newAlertsPromise.state() == 'resolved' || this._newAlertsPromise.state() == 'rejected') {
                this._render();
            }
            else {
                $.when(this._newAlertsPromise).then(this._render.bind(this));
            }

            return this.$element;
        },
        /*
         * Internal private method that physically handles rendering operation.
         */
        _render: function() {
            // Holds generated HTML
            var alertHtml = '';
            // Only compile alerts if there are alerts to display
            if (this.alerts.length > 0) {
                // Prepare template
                var alertTemplate = Handlebars.compile(this._alertTemplateHtml, {noEscape: true});
                var i;
                // If agglomeration is enabled, set the container to the highest priority alert type
                if (this.settings.agglomerate) {
                    // Holds generated agglomerated alerts
                    var alertMessage = '<ul>';

                    // Determine overall alert priority
                    var alertContainerType = 'info';
                    for (i = 0; i < this.alerts.length; i++) {
                        if (this._alertTypePriorities[this.alerts[i].type] > this._alertTypePriorities[alertContainerType]) {
                            alertContainerType = this.alerts[i].type;
                        }
                    }

                    // Compile each alert
                    var aggTemplate = Handlebars.compile('<li class=' + this.settings.alertMessageClass + '>{{ message }}</li>');
                    for (i = 0; i < this.alerts.length; i++) {
                        alertMessage += aggTemplate(this.alerts[i]);
                    }

                    alertMessage += '</ul>';

                    // Generate complete alert HTML
                    alertHtml = alertTemplate({
                        type   : alertContainerType,
                        message: alertMessage,
                        icon   : this._alertTypeIcon[alertContainerType]
                    });
                }
                else {
                    // Compile each alert.
                    for (i = 0; i < this.alerts.length; i++) {
                        var alert = this.alerts[i];

                        // Inject icon
                        alert.icon = this._alertTypeIcon[alert.type];

                        // Compile alert
                        alertHtml += alertTemplate(alert);
                    }
                } 
            }
            // Show alerts
            this.$element.html(alertHtml);

            // Scroll to top of alert location is new alerts output, and auto scrolling is enabled
            if (this.settings.scrollToTop && alertHtml !== '') {
                // Don't scroll if already visible, unless scrollWhenVisible is true
                if (!this._alertsVisible() || this.settings.scrollWhenVisible) {
                    $('html, body').animate({ scrollTop: this.$element.offset().top }, 'fast');
                }
            }

            // Trigger render events
            this.$element.trigger('render.' + this._name);
        },
        /**
         * Returns true if alerts container is completely within the viewport.
         */
        _alertsVisible: function() {
            var rect = this.element.getBoundingClientRect();
            return (
                rect.top >= 0 &&
                rect.left >= 0 &&
                rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) &&     
                rect.right <= (window.innerWidth || document.documentElement.clientWidth)
            );
        },
        /**
         * Completely destroy the ufAlerts plugin on the element.
         */
        destroy: function() {
            // Unbind any bound events
            this.$element.off('.' + this._name);

            // Remove plugin from element
            this.$element.removeData(this._name);

            return this.$element;
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
                console.warn('Method ' +  methodOrOptions + ' is private!');
            }
        }
        else {
            console.warn('Method ' +  methodOrOptions + ' does not exist.');
        }
    };
})(jQuery, window, document);