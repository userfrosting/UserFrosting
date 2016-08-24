/**
 * ufAlerts plugin.  Fetches and renders alerts from the UF alert stream.
 *
 * jQuery plugin template adapted from https://gist.github.com/Air-Craft/1300890
 *
 * === USAGE ===
 *
 * ufAlerts can be initialized on any container element as follows:
 *
 * $("#myDiv").ufAlerts(options);
 *
 * `options` is an object containing any of the following parameters:
 * @param {string} url The absolute URL from which to fetch flash alerts.
 * @param {bool} scrollToTop Whether to automatically scroll back to the top of the page after rendering alerts.
 * @param {string} alertContainerClass The CSS class(es) to be applied to the container for all alerts.
 * @param {string} alertMessageClass The CSS class(es) to be applied to each alert message.
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
 * `fetch()`: gets messages from the server.
 * `push(type, message)`: adds a message of a specified type (danger, warning, info, success) to the internal collection of alerts.
 * `clear()`: removes all messages from the internal collection.
 * `render()`: renders the collection of alerts to the container.
 *
 * UserFrosting https://www.userfrosting.com
 * @author Alexander Weissman https://alexanderweissman.com
 */
 
// if (!window.L) { window.L = function () { console.log(arguments);} } // optional EZ quick logging for debugging

(function( $ )
{    
    /**
     * The plugin namespace, ie for $('.selector').ufAlerts(options)
     * 
     * Also the id for storing the object state via $('.selector').data()  
     */
    var PLUGIN_NS = 'ufAlerts';

    var Plugin = function ( target, options )
    { 
        this.$T = $(target); 

        /** #### OPTIONS #### */
        this.options= $.extend(
            true,               // deep extend
            {
                url                : site.uri.public + "/alerts",
                scrollToTop        : true,
                alertContainerClass: "uf-alerts",
                agglomerate        : true,
                alertMessageClass  : "uf-alert-message",
                DEBUG: false
            },
            options
        );      
        
        this._alertMessageTemplateHtml = "<div class=\"" + this.options.alertMessageClass + " alert alert-{{ type }}\">{{ message }}</div>";
        
        this._alertTypePriorities = {
            "danger" : 3,
            "warning": 2,
            "success": 1,
            "info"   : 0
        };
        
        this._init( target, options );
        
        return this; 
    }

    /** #### INITIALISER #### */
    Plugin.prototype._init = function ( target, options )
    { 
        var base = this;
        var $el = $(target);
        
        base.$T.toggleClass(base.options.alertContainerClass);
        
        base.messages = [];
        
        base._newMessagesPromise = $.Deferred();        
        
        return base.$T;
    };

    /**
     * Clear all messages from the current uf-alerts collection.
     *
     */    
    Plugin.prototype.clear = function ()
    {
        var base = this;
        
        // See http://stackoverflow.com/a/1232046/2970321
        base.messages.length = 0;
        
        return base.$T;
    }
    
    /**
     * Fetches messages from the alert stream
     *
     */    
    Plugin.prototype.fetch = function ()
    {
        var base = this;
        
        // Set a promise, so that any chained calls after fetch can wait until the messages have been retrieved
        base._newMessagesPromise = $.getJSON( base.options.url )
        .done(function ( data ) {
            if (data) {
                base.messages = $.merge(base.messages, data);
            }
            
            base.$T.trigger("fetch.ufAlerts");
        });
        
        return base.$T;
    }
    
    /**
     * Push a given message to the current uf-alerts collection.
     *
     */    
    Plugin.prototype.push = function (type, message)
    {
        var base = this;
        
        base.messages.push({
            "type"   : type,
            "message": message
        });
        
        return base.$T;
    }
    
    /**
     * Renders the messages.
     *
     */    
    Plugin.prototype.render = function ()
    {
        var base = this;
        
        $.when(base._newMessagesPromise).then( function () {
            // Display alerts
            var alertHtml = "<ul>";
            
            // If agglomeration is enabled, set the container to the highest priority message type
            if (base.messages.length && base.options.agglomerate) {
                var alertContainerType = "info";
                jQuery.each(base.messages, function(alert_idx, alert) {
                    if (base._alertTypePriorities[alert["type"]] > base._alertTypePriorities[alertContainerType]) {
                        alertContainerType = alert["type"];
                    }
                });
            
                base.$T.toggleClass("alert alert-" + alertContainerType, true);
                
                var aggTemplate = Handlebars.compile("<li class=" + base.options.alertMessageClass + ">{{ message }}</li>");
                jQuery.each(base.messages, function(alert_idx, alert) {
                    alertHtml += aggTemplate(alert);
                });
                alertHtml += "</ul>";
            } else {
                alertHtml = "";
                
                var alertMessageTemplate = Handlebars.compile(base.alertMessageTemplateHtml); 
                
                jQuery.each(base.messages, function(alert_idx, alert) {
                    alertHtml += alertMessageTemplate(alert);
                });            
            }
            
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
            $.error( 'Plugin must be initialised before using method: ' + methodOrOptions );
        
        // CASE: invalid method
        } else if ( methodOrOptions.indexOf('_') == 0 ) {
            $.error( 'Method ' +  methodOrOptions + ' is private!' );
        } else {
            $.error( 'Method ' +  methodOrOptions + ' does not exist.' );
        }
    };
})(jQuery);
