/**
 * flashAlerts
 *
 * UF flash alert rendering plugin.
 * jQuery plugin template adapted from https://gist.github.com/Air-Craft/1300890
 */
 
// if (!window.L) { window.L = function () { console.log(arguments);} } // optional EZ quick logging for debugging

(function( $ ){
    
    /**
     * The plugin namespace, ie for $('.selector').ufAlerts(options)
     * 
     * Also the id for storing the object state via $('.selector').data()  
     */
    var PLUGIN_NS = 'ufAlerts';

    var Plugin = function ( target, options )  { 
        this.$T = $(target); 

        /** #### OPTIONS #### */
        this.options= $.extend(
            true,               // deep extend
            {
                url          : site.uri.public + "/alerts",
                scrollToTop  : true,
                DEBUG: false
            },
            options
        );      
        
        this._alert_message_html= "<div class=\"alert alert-{{ type }}\">{{ message }}</div>";
        this._alert_message_template = Handlebars.compile(this._alert_message_html); 
        
        this._init( target, options );   
        
        this.messages = [];
        
        var newMessagesPromise = $.Deferred();
        
        return this; 
    }

    /** #### INITIALISER #### */
    Plugin.prototype._init = function ( target, options ) { 
        var base = this;
        var $el = $(target);
        
        return base.$T;
    };

    /**
     * Fetches messages from the alert stream
     *
     */    
    Plugin.prototype.fetch = function ()
    {
        var base = this;
        
        // Set a promise, so that any chained calls after fetch can wait until the messages have been retrieved
        base.newMessagesPromise = $.getJSON( base.options.url )
        .done(function ( data ) {
            if (data) {
                base.messages = $.merge(base.messages, data);
            }
        });
        
        return base.$T;
    }
    
    /**
     * Renders the messages
     *
     */    
    Plugin.prototype.render = function ()
    {
        var base = this;
        
        $.when(base.newMessagesPromise).then( function () {
            // Display alerts
            var alertHtml = "";
            
            jQuery.each(base.messages, function(alert_idx, alert) {
                alertHtml += base._alert_message_template(alert);
            });
            
            base.$T.html(alertHtml);
            
            // Scroll back to top of page
            if (base.options.scrollToTop) {
                $("html, body").animate({
                    scrollTop: 0
                }, "fast");
            }
            
            base.$T.trigger("render.ufAlerts");
            
            return base.$T;
        });
    };
    
    /** #### PRIVATE METHODS #### */
    
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
    $.fn[ PLUGIN_NS ] = function( methodOrOptions ) {
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
            $.error( 'Plugin must be initialised before using method: ' + methodOrOptions );
        
        // CASE: invalid method
        } else if ( methodOrOptions.indexOf('_') == 0 ) {
            $.error( 'Method ' +  methodOrOptions + ' is private!' );
        } else {
            $.error( 'Method ' +  methodOrOptions + ' does not exist.' );
        }
    };
})(jQuery);
