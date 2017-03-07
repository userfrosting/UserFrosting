/**
 * uf-collection plugin.  Widget for attaching/detaching related items to a single parent item (e.g. roles for a user, etc).
 *
 * === USAGE ===
 *
 * uf-collection can be initialized on a div element as follows:
 *
 * $("#myCollection").ufCollection(options);
 *
 * `options` is an object containing any of the following parameters:
 * @param {string} dataUrl The url from which to fetch options (as JSON data) in the dropdown selector menu.
 * @param {string} dropdownTemplate A Handlebars template to use for rendering the dropdown items.
 * @param {string} rowTemplate A Handlebars template to use for rendering each row in the table.
 * @param {string} dropdownTheme The select2 theme to use for the dropdown menu.  Defaults to "bootstrap".
 * @param {string} placeholder Placeholder text to use in the dropdown menu before a selection is made.  Defaults to "Item".
 * @param {Object} dropdownControl a jQuery selector specifying the dropdown select2 control.  Defaults to looking for a .js-select-new element inside the parent object.
 * @param {Object} rowContainer a jQuery selector specifying the place where rows should be added.  Defaults to looking for the first tbody element inside the parent object.
 *
 * == EVENTS ==
 *
 * ufCollection triggers the following events:
 *
 * `rowAdd.ufCollection`: triggered when a new row is added to the collection.
 * `rowDelete.ufCollection`: triggered when a row is removed from the collection.
 *
 * UserFrosting https://www.userfrosting.com
 * @author Alexander Weissman https://alexanderweissman.com
 */
(function( $ )
{
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
                dropdown: {
                    ajax: {
                        url: "",
                        dataType: "json",
                        ajaxDelay: 250,
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
                    placeholder     : "Item",
                    selectOnClose   : false,  // Make a selection when they click out of the box/press the next button
                    theme: "default",
                    width: "100%",
                },
                dropdownControl : this.$T.find('.js-select-new'),
                dropdownTemplate: "",
                rowContainer    : this.$T.find('tbody').first(),
                rowTemplate     : "",
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

        this._init( target, this.options );

        return this;
    }

    /** #### INITIALISER #### */
    Plugin.prototype._init = function ( target, options ) {
        var base = this;
        var $el = $(target);

        // Add container class
        $el.toggleClass("uf-collection", true);

        base._initDropdownField(base.options.dropdownControl);

        base.options.dropdownControl.on("select2:select", function () {
           var item = $(this).select2("data");
           base.addRow(item);
        });

        return this;
    };

    Plugin.prototype.addRow = function (options) {
        var base = this;

        var params = {
            id : "",
            rownum: base._rownum
        };
        $.extend(true, params, options[0]);

        var newRowTemplate = base._rowTemplateCompiled(params);
        var newRow = $(newRowTemplate).appendTo(base.options.rowContainer);

        // Trigger to delete row
        $(newRow).find(".js-delete-row").on("click", function() {
            $(this).closest('.uf-collection-row').remove();
            base.$T.trigger('rowDelete.ufCollection');
            var index = base._addedIds.indexOf(5);
            if (index > -1) {
                base._addedIds.splice(index, 1);
            }
        });

        base._rownum += 1;

        // Fire event when row has been constructed
        base.$T.trigger('rowAdd.ufCollection');

        return base.$T;
    };

    /** #### PRIVATE METHODS #### */
    Plugin.prototype._initDropdownField = function (field) {
        var base = this;
        var options = base.options.dropdown;

        if (!("templateResult" in options)) {
            options.templateResult = function(item) {
                // Must wrap this in a jQuery selector to render as HTML
                return $(base._dropdownTemplateCompiled(item));
            };
        }
        // Legacy options (<= v4.0.9)
        if ("dataUrl" in base.options) {
            options.ajax.url = base.options.dataUrl;
        }
        if ("ajaxDelay" in base.options) {
            options.ajax.ajaxDelay = base.options.ajaxDelay;
        }
        if ("dropdownTheme" in base.options) {
            options.theme = base.options.dropdownTheme;
        }
        if ("placeholder" in base.options) {
            options.placeholder = base.options.placeholder;
        }
        if ("selectOnClose" in base.options) {
            options.selectOnClose = base.options.selectOnClose;
        }
        if ("width" in base.options) {
            options.width = base.options.width;
        }

        return field.select2(options);
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
