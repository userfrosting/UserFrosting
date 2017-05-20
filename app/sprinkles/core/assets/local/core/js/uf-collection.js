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
 * @param {bool}   useDropDown Set to true if rows should be added using a select2 dropdown, false for free text inputs (see https://ux.stackexchange.com/a/15637/53990).
 * @param {Object} dropdown The options to pass to the select2 plugin for the add item dropdown.
 * @param {string} dropdown.ajax.url The url from which to fetch options (as JSON data) in the dropdown selector menu.
 * @param {bool}   selectOnClose Set to true if you want the currently highlighted dropdown item to be automatically added when the dropdown is closed for any reason.
 * @param {string} dropdown.theme The select2 theme to use for the dropdown menu.  Defaults to "bootstrap".
 * @param {string} dropdown.placeholder Placeholder text to use in the dropdown menu before a selection is made.  Defaults to "Item".
 * @param {string} dropdown.width Width of the dropdown selector, when used.  Defaults to "100%".
 * @param {Object} dropdownControl a jQuery selector specifying the dropdown select2 control.  Defaults to looking for a .js-select-new element inside the parent object.
 * @param {string} dropdownTemplate A Handlebars template to use for rendering the dropdown items.
 * @param {Object} rowContainer a jQuery selector specifying the place where rows should be added.  Defaults to looking for the first tbody element inside the parent object.
 * @param {string} rowTemplate A Handlebars template to use for rendering each row in the table.
 *
 * == EVENTS ==
 *
 * ufCollection triggers the following events:
 *
 * `rowAdd.ufCollection`: triggered when a new row is added to the collection.
 * `rowDelete.ufCollection`: triggered when a row is removed from the collection.
 * `rowTouch.ufCollection`: triggered when any inputs in a row are brought into focus.
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
                useDropdown: true,
                dropdown: {
                    ajax: {
                        url: "",
                        dataType: "json",
                        delay: 250,
                        data: function (params) {
                            return {
                                filters: {
                                    info : params.term
                                }
                            };
                        },
                        processResults: function (data, params) {
                            // Process the data into dropdown options
                            var suggestions = [];
                            if (data && data.rows) {
                                suggestions = data.rows;
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

        // Handlebars template method
        this._dropdownTemplateCompiled = Handlebars.compile(this.options.dropdownTemplate);

        this._rowTemplateCompiled = Handlebars.compile(this.options.rowTemplate);

        this._init( target, this.options );

        return this;
    }

    /**
     * Add a new row to the collection, optionally passing in prepopulated template data.
     */
    Plugin.prototype.addRow = function (options) {
        var base = this;

        base._createRow(options);

        return base.$T;
    };

    /**
     * Add a new 'virgin' row to the collection, optionally passing in prepopulated template data.
     * Virgin rows are rows that have not yet been brought into focus by the user.
     * When a virgin row is brought into focus, it loses its virgin status and a new virgin row is created.
     */
    Plugin.prototype.addVirginRow = function (options) {
        var base = this;

        base._createVirginRow(options);

        return base.$T;
    };

    /**
     * Delete a target row.
     */
    Plugin.prototype.deleteRow = function (row) {
        var base = this;

        base._deleteRow(row);

        return base.$T;
    };

    /**
     * Get the dropdown control for the collection, if one exists.
     */
    Plugin.prototype.getDropdown = function () {
        return this.options.dropdownControl;
    };

    /**
     * Touch a target row.
     */
    Plugin.prototype.touchRow = function (row) {
        var base = this;

        base._touchRow(row);

        return base.$T;
    };

    /** #### PRIVATE METHODS #### */

    /**
     * Initialize the ufCollection widget.
     */
    Plugin.prototype._init = function ( target, options ) {
        var base = this;
        var $el = $(target);

        // Add container class
        $el.toggleClass("uf-collection", true);

        // Add bindings for any rows already present in the DOM
        $.each(base.options.rowContainer.find('.uf-collection-row'), function (idx, row) {
            base._onNewRow($(row));
        });

        // If we're using dropdown options, create the select2 and add bindings to add a new row when an option is selected
        if (base.options.useDropdown) {
            base._initDropdownField(base.options.dropdownControl);
    
            base.options.dropdownControl.on("select2:select", function () {
               var item = $(this).select2("data");
               base.addRow(item);
            });
        } else {
            // Otherwise, add a new virgin row
            base.addVirginRow();
        }

        return this;
    };

    /**
     * Create a new row and attach the handler for deletion to the js-delete-row button
     */
    Plugin.prototype._createRow = function (options) {
        var base = this;

        var params = {
            id : "",
            rownum: base._rownum
        };

        // Merge in any prepopulated values for the row
        if (typeof options !== 'undefined') {
            $.extend(true, params, options[0]);
        }

        // Generate the row and append to table
        var newRowTemplate = base._rowTemplateCompiled(params);
        var newRow;

        // Add the new row before any virgin rows in the table.
        var virginRows = base.options.rowContainer.find('.uf-collection-row-virgin').length;
        if (virginRows) {
            newRow = $(newRowTemplate).insertBefore(base.options.rowContainer.find('.uf-collection-row-virgin:first'));
        } else {
            newRow = $(newRowTemplate).appendTo(base.options.rowContainer);
        }

        // Add bindings and fire event
        base._onNewRow(newRow);

        return newRow;
    };

    /**
     * Create a new, blank row with the 'virgin' status.
     */
    Plugin.prototype._createVirginRow = function (options) {
        var base = this;

        // Generate the row and append to table
        var newRow = base._createRow(options);

        // Set the row's 'virgin' status
        newRow.addClass('uf-collection-row-virgin');
        newRow.find('.js-delete-row').hide();

        return newRow;
    };

    /**
     * Delete a row from the collection.
     */
    Plugin.prototype._deleteRow = function (row) {
        var base = this;
        row.remove();
        base.$T.trigger('rowDelete.ufCollection');
    };

    /**
     * Add delete and touch bindings for a row, increment the internal row counter, and fire the rowAdd event
     */
    Plugin.prototype._onNewRow = function (row) {
        var base = this;

        // Trigger to delete row
        row.find('.js-delete-row').on('click', function() {
            base._deleteRow($(this).closest('.uf-collection-row'));
        });

        // Once the new row comes into focus for the first time, it has been "touched"
        row.find(':input').on('focus', function () {
            base._touchRow(row);
        });

        base._rownum += 1;

        // Fire event when row has been constructed
        base.$T.trigger('rowAdd.ufCollection', row);
    };

    /**
     * Remove a row's virgin status, show the delete button, and add a new virgin row if needed
     */
    Plugin.prototype._touchRow = function (row) {
        var base = this;

        row.removeClass('uf-collection-row-virgin');
        row.find('.js-delete-row').show();

        base.$T.trigger('rowTouch.ufCollection', row);

        // If we're not using dropdowns, assert that the table doesn't already have a virgin row.  If not, create a new virgin row.
        if (!base.options.useDropdown) {
            var virginRows = base.options.rowContainer.find('.uf-collection-row-virgin').length;
            if (!virginRows) {    
                base._createVirginRow();
            }
        }
    };

    /**
     * Initialize the select2 dropdown for this collection on a specified control element.
     */
    Plugin.prototype._initDropdownField = function (field) {
        var base = this;
        var options = base.options.dropdown;

        if (!("templateResult" in options)) {
            options.templateResult = function(item) {
                // Display loading text if the item is marked as "loading"
                if (item.loading) return item.text;

                // Must wrap this in a jQuery selector to render as HTML
                return $(base._dropdownTemplateCompiled(item));
            };
        }
        // Legacy options (<= v4.0.9)
        if ("dataUrl" in base.options) {
            options.ajax.url = base.options.dataUrl;
        }
        if ("ajaxDelay" in base.options) {
            options.ajax.delay = base.options.ajaxDelay;
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
