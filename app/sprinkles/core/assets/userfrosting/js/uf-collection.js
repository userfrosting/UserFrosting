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
 * @param {bool}   useDropdown Set to true if rows should be added using a select2 dropdown, false for free text inputs (see https://ux.stackexchange.com/a/15637/53990).
 * @param {Object} dropdown The options to pass to the select2 plugin for the add item dropdown.
 * @param {string} dropdown.ajax.url The url from which to fetch options (as JSON data) in the dropdown selector menu.
 * @param {bool}   selectOnClose Set to true if you want the currently highlighted dropdown item to be automatically added when the dropdown is closed for any reason.
 * @param {string} dropdown.theme The select2 theme to use for the dropdown menu.  Defaults to "default".
 * @param {string} dropdown.placeholder Placeholder text to use in the dropdown menu before a selection is made.  Defaults to "Item".
 * @param {string} dropdown.width Width of the dropdown selector, when used.  Defaults to "100%".
 * @param {callback} transformDropdownSelection Custom transformation on objects from the dropdown before passing them to render in the collection table.
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
 * @author Alexander Weissman <https://alexanderweissman.com>
 */
;(function($, window, document, undefined) {
	"use strict";

    // Define plugin name and defaults.
    var pluginName = "ufCollection",
        defaults = {
            useDropdown: true,
            dropdown: {
                ajax        : {
                    url         : "",
                    dataType    : "json",
                    delay       : 250,
                    data        : function (params) {
                        return {
                            filters: {
                                info : params.term
                            }
                        };
                    },
                    processResults  : function (data, params) {
                        // Process the data into dropdown options
                        var suggestions = [];
                        if (data && data.rows) {
                            suggestions = data.rows;
                        }
                        return {
                            results: suggestions
                        };
                    },
                    cache           : true
                },
                placeholder     : "Item",
                selectOnClose   : false,  // Make a selection when they click out of the box/press the next button
                theme           : "default",
                width           : "100%",
            },
            transformDropdownSelection: function (item) {
                return item;
            },
            dropdownControl : null,
            dropdownTemplate: "",
            rowContainer    : null,
            rowTemplate     : "",
            DEBUG           : false
            };

    // Constructor
    function Plugin (element, options) {
        this.element = element[0];
        this.$element = $(this.element);
        var lateDefaults = {
            dropdownControl: this.$element.find('.js-select-new'),
            rowContainer: this.$element.find('tbody').first()
        };
        this.settings = $.extend(true, {}, defaults, lateDefaults, options);
        this._defaults = defaults;
        this._name = pluginName;

        // Detect changes to element attributes
        this.$element.attrchange({ callback: function (event) { this.element = event.target; }.bind(this) });

        // Internal counter for adding rows to the collection.  Gets updated every time `_createRow` is called.
        this._rownum = 0;

        // Keep track of last added row
        this._lastRow = null;

        // Handlebars template method
        this._dropdownTemplateCompiled = Handlebars.compile(this.settings.dropdownTemplate);

        this._rowTemplateCompiled = Handlebars.compile(this.settings.rowTemplate);

        // Add container class
        this.$element.toggleClass("uf-collection", true);

        // Add bindings for any rows already present in the DOM
        $.each(this.settings.rowContainer.find('.uf-collection-row'), $.proxy(function(idx, row) {
            this._onNewRow($(row));
            this._lastRow = row;
        }, this));

        // If we're using dropdown options, create the select2 and add bindings to add a new row when an option is selected
        if (this.settings.useDropdown) {
            this._initDropdownField(this.settings.dropdownControl);

            this.settings.dropdownControl.on("select2:select", $.proxy(function(e) {
                var item = $(e.target).select2("data")[0];
                // Apply any transformations before rendering as a row
                var transformed = this.settings.transformDropdownSelection(item);
                this._createRow(transformed);
            }, this));
        }
        else {
            // Otherwise, add a new virgin row
            this._createVirginRow();
        }

        return this;
    }

    // Functions
    $.extend(Plugin.prototype, {
        /**
         * Add a new row to the collection, optionally passing in prepopulated template data.
         */
        addRow: function(options) {
            // Grab params, if any
            var params = {};
            if (typeof options !== 'undefined') {
                params = options[0];
            }

            this._createRow(params);

            return this.$element;
        },
        /**
         * Add a new 'virgin' row to the collection, optionally passing in prepopulated template data.
         * Virgin rows are rows that have not yet been brought into focus by the user.
         * When a virgin row is brought into focus, it loses its virgin status and a new virgin row is created.
         */
        addVirginRow: function(options) {
            // Grab params, if any
            var params = {};
            if (typeof options !== 'undefined') {
                params = options[0];
            }

            this._createVirginRow(params);

            return this.$element;
        },
        /**
         * Delete a target row.
         */
        deleteRow: function(row) {
            this._deleteRow(row);

            return this.$element;
        },
        /**
         * Get the dropdown control for the collection, if one exists.
         */
        getDropdown: function() {
            return this.settings.dropdownControl;
        },
        /**
         * Get the last row added in the collection.
         */
        getLastRow: function () {
            return this._lastRow;
        },
        /**
         * Touch a target row.
         */
        touchRow: function(row) {
            this._touchRow(row);

            return this.$element;
        },
        /**
         * Create a new row and attach the handler for deletion to the js-delete-row button
         */
        _createRow: function(params) {
            params = $.extend(true,
            {
                id: "",
                rownum: this._rownum
            }, params);

            // Generate the row and append to table
            var newRowTemplate = this._rowTemplateCompiled(params),
                newRow;

            // Add the new row before any virgin rows in the table.
            var virginRows = this.settings.rowContainer.find('.uf-collection-row-virgin').length;
            if (virginRows) {
                newRow = $(newRowTemplate).insertBefore(this.settings.rowContainer.find('.uf-collection-row-virgin:first'));
            } else {
                newRow = $(newRowTemplate).appendTo(this.settings.rowContainer);
            }

            this._lastRow = newRow;

            // Add bindings and fire event
            this._onNewRow(newRow);

            return newRow;
        },
        /**
         * Create a new, blank row with the 'virgin' status.
         */
        _createVirginRow: function(params) {
            // Generate the row and append to table
            var newRow = this._createRow(params);

            // Set the row's 'virgin' status
            newRow.addClass('uf-collection-row-virgin');
            newRow.find('.js-delete-row').hide();

            return newRow;
        },
        /**
         * Delete a row from the collection.
         */
        _deleteRow: function(row) {
            row.remove();
            this.$element.trigger('rowDelete.ufCollection', row);
        },
         /**
         * Add delete and touch bindings for a row, increment the internal row counter, and fire the rowAdd event
         */
        _onNewRow: function(row) {
            // Trigger to delete row
            row.find('.js-delete-row').on('click', $.proxy(function(e) {
                this._deleteRow($(e.target).closest('.uf-collection-row'));
            }, this));

            // Once the new row comes into focus for the first time, it has been "touched"
            row.find(':input').on('focus', $.proxy(function() {
                this._touchRow(row);
            }, this));

            this._rownum += 1;

            // Fire event when row has been constructed
            this.$element.trigger('rowAdd.ufCollection', row);
        },
        /**
         * Remove a row's virgin status, show the delete button, and add a new virgin row if needed
         */
        _touchRow: function(row) {
            row.removeClass('uf-collection-row-virgin');
            row.find('.js-delete-row').show();

            this.$element.trigger('rowTouch.ufCollection', row);

            // If we're not using dropdowns, assert that the table doesn't already have a virgin row.  If not, create a new virgin row.
            if (!this.settings.useDropdown) {
                var virginRows = this.settings.rowContainer.find('.uf-collection-row-virgin').length;
                if (!virginRows) {
                    this._createVirginRow();
                }
            }
        },
        /**
         * Initialize the select2 dropdown for this collection on a specified control element.
         */
        _initDropdownField: function(field) {
            var options = this.settings.dropdown;

            if (!("templateResult" in options)) {
                options.templateResult = $.proxy(function(item) {
                    // Display loading text if the item is marked as "loading"
                    if (item.loading) return item.text;

                    // Must wrap this in a jQuery selector to render as HTML
                    return $(this._dropdownTemplateCompiled(item));
                }, this);
            }
            // Legacy options (<= v4.0.9)
            if ("dataUrl" in this.settings) {
                options.ajax.url = this.settings.dataUrl;
            }
            if ("ajaxDelay" in this.settings) {
                options.ajax.delay = this.settings.ajaxDelay;
            }
            if ("dropdownTheme" in this.settings) {
                options.theme = this.settings.dropdownTheme;
            }
            if ("placeholder" in this.settings) {
                options.placeholder = this.settings.placeholder;
            }
            if ("selectOnClose" in this.settings) {
                options.selectOnClose = this.settings.selectOnClose;
            }
            if ("width" in this.settings) {
                options.width = this.settings.width;
            }

            return field.select2(options);
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
                console.warn( 'Method ' +  methodOrOptions + ' is private!' );
            }
        }
        else {
            console.warn( 'Method ' +  methodOrOptions + ' does not exist.' );
        }
    };
})(jQuery, window, document);
