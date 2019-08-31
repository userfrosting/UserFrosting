/**
 * ufModal plugin.  Handles modal windows that dynamically their fetch content from a specified URL.
 *
 *
 * UserFrosting https://www.userfrosting.com
 * @author Alexander Weissman https://alexanderweissman.com
 */
(function( $ )
{
    /**
     * The plugin namespace, ie for $('.selector').ufModal(options)
     *
     * Also the id for storing the object state via $('.selector').data()
     */
    var PLUGIN_NS = 'ufModal';

    var Plugin = function ( target, options )
    {

        this.$T = $(target);

        /** #### OPTIONS #### */
        this.options= $.extend(
            true,               // deep extend
            {
                sourceUrl : "",
                ajaxParams: {},
                msgTarget : null,
                DEBUG: false
            },
            options
        );

        this.modal = null;

        this._init( target );

        return this;
    };

    /** #### INITIALISER #### */
    Plugin.prototype._init = function ( target )
    {
        var base = this;
        var $el = $(target);

        // Delete any existing modals attached to the element (should have been deleted already anyway)
        if ($el.find(".modal").length) {
            $el.find(".modal").remove();
        }

        // Fetch and render the form
        $.ajax({
          type: "GET",
          url: base.options.sourceUrl,
          data: base.options.ajaxParams,
          cache: false
        })
        .then(
            // Fetch successful
            function (data) {
                // Append the form as a modal dialog to the body
                base.modal = $(data);
                $el.append(base.modal);

                base.modal.modal('show');

                // Bind modal to be deleted when closed
                base.modal.on("hidden.bs.modal", function () {
                    base.destroy();
                });

                base.$T.trigger('renderSuccess.ufModal');
                return data;
            },
            // Fetch failed
            function (data) {
                // Error messages
                if ((typeof site !== "undefined") && site.debug.ajax && data.responseText) {
                    base.$T.trigger('renderError.ufModal');
                    document.write(data.responseText);
                    document.close();
                } else {
                    if (base.options.DEBUG) {
                        console.log("Error (" + data.status + "): " + data.responseText );
                    }
                    // Display errors on failure
                    // TODO: ufAlerts widget should have a 'destroy' method
                    if (!base.options.msgTarget.data('ufAlerts')) {
                        base.options.msgTarget.ufAlerts();
                    } else {
                        base.options.msgTarget.ufAlerts('clear');
                    }

                    base.options.msgTarget.ufAlerts('fetch').ufAlerts('render');
                    base.options.msgTarget.on("render.ufAlerts", function () {
                        base.$T.trigger('renderError.ufModal');
                    });
                }

                base.destroy();

                return data;
            }
        );
    };

    Plugin.prototype.destroy = function () {
        var base = this;
        var $el = base.$T;

        // Delete the plugin object
        base.delete;

        // Remove the modal from the selector
        if (base.modal) {
            base.modal.remove();
        }

        // Unbind any modal events bound to the selector
        $el.off('.ufModal');

        // Remove plugin name from selector's data-* attribute
        $el.removeData(PLUGIN_NS);
    };

    Plugin.prototype.getModal = function () {
        return this.modal;
    };
    
    /**
     * EZ Logging/Warning (technically private but saving an '_' is worth it imo)
     */
    Plugin.prototype.DLOG = function ()
    {
        if (!this.DEBUG) return;
        for (var i in arguments) {
            console.log( PLUGIN_NS + ': ', arguments[i] );
        }
    }
    Plugin.prototype.DWARN = function ()
    {
        this.DEBUG && console.warn( arguments );
    }


/*###################################################################################
 * JQUERY HOOK
 ###################################################################################*/

    /**
     * Generic jQuery plugin instantiation method call logic
     *
     * Method options are stored via jQuery's data() method in the relevant element(s)
     * Notice, myActionMethod mustn't start with an underscore (_) as this is used to
     * indicate private methods on the PLUGIN class.
     */
    $.fn[ PLUGIN_NS ] = function( methodOrOptions )
    {
        if (!$(this).length) {
            return $(this);
        }
        var instance = $(this).data(PLUGIN_NS);

        // CASE: action method (public method on PLUGIN class)
        if ( instance
                && methodOrOptions.indexOf('_') != 0
                && instance[ methodOrOptions ]
                && typeof( instance[ methodOrOptions ] ) == 'function' ) {

            return instance[ methodOrOptions ]( Array.prototype.slice.call( arguments, 1 ) );


        // CASE: argument is options object or empty = initialise
        } else if ( typeof methodOrOptions === 'object' || ! methodOrOptions ) {

            instance = new Plugin( $(this), methodOrOptions );    // ok to overwrite if this is a re-init
            $(this).data( PLUGIN_NS, instance );
            return $(this);

        // CASE: method called before init
        } else if ( !instance ) {
            console.warn( 'Plugin must be initialised before using method: ' + methodOrOptions );

        // CASE: invalid method
        } else if ( methodOrOptions.indexOf('_') == 0 ) {
            console.warn( 'Method ' +  methodOrOptions + ' is private!' );
        } else {
            console.warn( 'Method ' +  methodOrOptions + ' does not exist.' );
        }
    };
})(jQuery);