/// <reference types="jquery" />
/**
 * ufModal plugin.  Handles modal windows that dynamically their fetch content from a specified URL.
 *
 * UserFrosting https://www.userfrosting.com
 * @author Alexander Weissman <https://alexanderweissman.com>
 */
;(function($, document, undefined) {
    'use strict';

    // Define plugin name and defaults.
    var pluginName = 'ufModal';
    /**
     * @typedef {{
     *     sourceUrl: string,
     *     ajaxParams: JQuery.PlainObject | string,
     *     msgTarget?: JQuery<HTMLElement>|null
     *     DEBUG?: boolean,
     * }} Options
     * @type {Options}
     */
    var defaults = {
        sourceUrl : '',
        ajaxParams: {},
        msgTarget : null,
        DEBUG     : false
    };

    /**
     * @param {ArrayLike<HTMLElement>} inElement 
     * @param {Options|undefined} options 
     */
    function createPlugin(inElement, options) {
        var element = inElement[0];
        var $element = $(element);
        var settings = $.extend(true, defaults, options);

        // True plugin initialization commences
        /** @type {JQuery<HTMLElement>|null} */
        var modal = null;

        // Delete any existing modals attached to the element (should have been deleted already anyway)
        if ($element.find('.modal').length) $element.find('.modal').remove();

        function destroy() {
            // Remove modal from selector
            if (modal) modal.remove();

            // Unbind plugin events
            $element.off('.' + pluginName);

            // Remove plugin data from internal jQuery store (jQuery doesn't store with data-*, but can access it)
            $element.removeData(pluginName);

            return $element;
        }

        // Fetch and render
        $.ajax({
            type: 'GET',
            url: settings.sourceUrl,
            data: settings.ajaxParams,
            cache: false,
        }).then(
            // Success
            function (data) {
                // Append the data as a modal dialog to the target element
                modal = $(data);
                $element.append(modal);

                // Trigger modal dialog
                modal.modal('show');

                // Bind destroy function to close event
                modal.on('hidden.bs.modal', function () { destroy(); });

                // Trigger success event
                $element.trigger('renderSuccess.ufModal');
            },
            // Failure
            function (data) {
                // Handle error messages
                if (site !== undefined && site.debug.ajax && data.responseText) {
                    // Trigger failure event
                    $element.trigger('renderError.ufModal');

                    // Replace document content with response, and handle browser quirks
                    document.write(data.responseText);
                    document.close();
                } else {
                    // Debug logging
                    if (settings.DEBUG) console.log('Error (' + data.status + '): ' + data.responseText);

                    // Refresh ufAlerts for errors if target defined
                    if (settings.msgTarget) {
                        // Check if ufAlerts is instanced and empty
                        if (!settings.msgTarget.data('ufAlerts')) settings.msgTarget.ufAlerts();
                        else settings.msgTarget.ufAlerts('clear');

                        // Trigger failure event on render.ufAlerts event
                        settings.msgTarget.on('render.ufAlerts', function () {
                            $element.trigger('renderError.ufModal');
                        });

                        // Pull alerts
                        settings.msgTarget.ufAlerts('fetch').ufAlerts('render');
                    } else {
                        // renderError.ufModal event should always be able to trigger
                        $element.trigger('renderError.ufModal');
                    }
                }
            }
        )

        /**
         * Returns underlying modal
         */
        function getModal() {
            return modal;
        }

        return {
            destroy,
            getModal,
        };
    }

    /**
     * Handles instantiation and access to non-private methods.
     * @param {Options|keyof ReturnType<createPlugin>|undefined} methodOrOptions
     */
    function interop(methodOrOptions) {
        // Grab plugin instance
        /** @type {ReturnType<createPlugin>|undefined} */
        var instance = $(this).data(pluginName);
        
        // If undefined or object, initialize plugin.
        if (typeof methodOrOptions === 'undefined' || typeof methodOrOptions === 'object') {
            // Only initialize if not previously done.
            if (!instance) {
                $(this).data(pluginName, createPlugin(this, methodOrOptions));
            }
            return this;
        }
        // Otherwise ensure first parameter is a valid string, and is the name of an actual function.
        else if (typeof methodOrOptions === 'string' && typeof instance[methodOrOptions] === 'function') {
            return instance[methodOrOptions]();
        }
        else {
            console.error('Method ' +  methodOrOptions + ' does not exist.');
        }
    };

    $.fn[pluginName] = interop;
})(jQuery, document);