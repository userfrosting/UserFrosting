(function( $ ){

    /**
     * The plugin namespace, ie for $('.selector').ufCollection(options)
     *
     * Also the id for storing the object state via $('.selector').data()
     */
    var PLUGIN_NS = 'ufCollection';

    var Plugin = function ( target, options )  {
        this.$T = $(target);

        /** #### OPTIONS #### */
        this.options= $.extend(
            true,               // deep extend
            {
                dataUrl         : "",
                dropdownTemplate: "",
                rowTemplate     : "",
                dropdownTheme   : "bootstrap",
                placeholder     : "Item",
                DEBUG: false
            },
            options
        );

        // Internal counter for adding rows to the collection.  Gets updated every time `addRow` is called.
        this._rownum = 0;

        // Keeps track of which ids already exist in the collection
        this._addedIds = [];

        // Handlebars template method
        this._dropdownTemplateCompiled = Handlebars.compile(this.options.dropdownTemplate);

        this._rowTemplateCompiled = Handlebars.compile(this.options.rowTemplate);

        this._init( target, options );

        return this;
    }

    /** #### INITIALISER #### */
    Plugin.prototype._init = function ( target, options ) {
        var base = this;
        var $el = $(target);

        // Add container class
        $el.toggleClass("uf-collection", true);

        // Go through each select field inside this object, and initialize it as a select2 dropdown
        var selects = $el.find("select");
        $.each(selects, function(idx, field) {
            base._initDropdownField(field);
        });

        return this;
    };

    Plugin.prototype.addRow = function (options) {
        var base = this;

        var params = {
            id : ""
        };
        $.extend(true, params, options[0]);
        
        var newRowTemplate = base._rowTemplateCompiled({
            rownum     : base._rownum
        });
        var newRow = $(newRowTemplate).appendTo(base.$T);

        // Setup the new row with a select2
        var selectField = base._initDropdownField(newRow.find("select"));

        if (params.id != "") {
            var preSelect = new Option(params.text, params.id, true, true);
            // Append it to the select
            selectField.append(preSelect).trigger("change");    
            base._addedIds.push(params.id);
        }

        // Trigger to delete row
        $(newRow).find(".js-delete-row").on("click", function() {
            $(this).closest('.uf-collection-row').remove();
            base.$T.trigger("rowdelete");
            var index = base._addedIds.indexOf(5);
            if (index > -1) {
                base._addedIds.splice(index, 1);
            }
        });

        // When value is changed, fire event 'rowchange'
        selectField.on("select2:select", function() {
            base.$T.trigger("rowchange");
        });

        base._rownum += 1;

        // Fire event when row has been constructed
        base.$T.trigger("rowadd");

        return base.$T;
    };

    /** #### PRIVATE METHODS #### */
    Plugin.prototype._initDropdownField = function (field) {
        var base = this;

        return $(field).select2({
            // Fetch data source options and construct the dropdown options
            ajax: {
                url: base.options.dataUrl,
                dataType: 'json',
                delay: 250,
                data: function (params) {
                    return {
                        filters: {
                            info : params.term
                        }
                    };
                },
                processResults: function (data, params) {
                    var suggestions = [];
                    // Process the data into dropdown options
                    if (data && data['rows']) {
                        jQuery.each(data['rows'], function(idx, row) {
                            //if (jQuery.inArray(row.id, base._addedIds)) {
                                row.text = row.name;
                                suggestions.push(row);
                            //}
                        });
                    }
                    return {
                        results: suggestions
                    };
                },
                cache: true
            },
            selectOnClose: true,       // Make a selection when they click out of the box/press the next button
            width: '100%',
            theme: base.options.dropdownTheme,
            dropdownCssClass: "pillbox-options",
            placeholder: base.options.placeholder,
            templateResult: function(item) {
                // Must wrap this in a jQuery selector to render as HTML
                return $(base._dropdownTemplateCompiled(item));
            }
        });
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
