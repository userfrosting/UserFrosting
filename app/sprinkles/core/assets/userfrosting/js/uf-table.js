/**
 * uf-table plugin.  Sets up a Tablesorter table with sorting, pagination, and search, and fetches data from a JSON API.
 *
 * This plugin depends on query-string.js, which is used to convert a query string into a JSON object.
 *
 * jQuery plugin template adapted from https://gist.github.com/Air-Craft/1300890
 *
 * === USAGE ===
 *
 * Create a container element, and within it place your table, paging controls, and Handlebars templates for rendering the cells.
 *
 * - Your table should have a unique id, and your paging controls should be wrapped in an element with the `.js-uf-table-pager` class.
 * - Create a button with the `.js-uf-table-download` class, and it will be automatically bound to trigger an AJAX request for downloading the table (CSV, etc).
 *
    <div id="widget-users">
       <table id="table-users" class="tablesorter table table-bordered table-hover table-striped" data-sortlist="[[0, 0]]">
           <thead>
               <tr>
                   <th class="sorter-metatext" data-column-name="name" data-column-template="#user-table-column-info">User <i class="fa fa-sort"></i></th>
                   <th class="sorter-metanum" data-column-name="last_activity" data-column-template="#user-table-column-last-activity">Last Activity <i class="fa fa-sort"></i></th>
               </tr>
           </thead>
           <tbody>
           </tbody>
       </table>

       <script id="user-table-column-info" type="text/x-handlebars-template">
           <td data-text="{{row.last_name}}">
               <strong>
                   <a href="{{site.uri.public}}/users/u/{{row.user_name}}">{{row.first_name}} {{row.last_name}} ({{row.user_name}})</a>
               </strong>
               <div>
                   <i class="fa fa-envelope"></i> <a href="mailto:{{row.email}}">{{row.email}}</a>
               </div>
           </td>
       </script>

       <script id="user-table-column-last-activity" type="text/x-handlebars-template">
           {{#if row.last_activity_at }}
           <td data-num="{{dateFormat row.last_activity_at format='x'}}">
               {{dateFormat row.last_activity_at format="dddd"}}<br>{{dateFormat row.last_activity_at format="MMM Do, YYYY h:mm a"}}
               <br>
               <i>{{row.last_activity.description}}</i>
           </td>
           {{ else }}
           <td data-num="0">
                   <i>Unknown</i>
           </td>
           {{/if }}
       </script>

       <div class="pager pager-lg tablesorter-pager js-uf-table-pager">
           <span class="pager-control first" title="First page"><i class="fa fa-angle-double-left"></i></span>
           <span class="pager-control prev" title="Previous page"><i class="fa fa-angle-left"></i></span>
           <span class="pagedisplay"></span>
           <span class="pager-control next" title="Next page"><i class="fa fa-angle-right"></i></span>
           <span class="pager-control last" title= "Last page"><i class="fa fa-angle-double-right"></i></span>
           <br><br>
           Jump to Page: <select class="gotoPage"></select> &bull; Show:
           <select class="pagesize">
               <option value="5">5</option>
               <option value="10">10</option>
           </select>
       </div>

       <button class="btn btn-sm btn-default js-uf-table-download">Download CSV</button>
   </div>
 *
 * Initialize ufTable on your container object:
 *
 * $("#widget-users").ufTable(options);
 *
 * `options` is an object containing any of the following parameters:
 * @param {string} dataUrl The absolute URL from which to fetch table data.
 * @param {mixed} addParams An object containing any additional key-value pairs that you want appended to the AJAX requests.
 * @param {mixed} tablesorter An object containing tablesorter's configuration options (https://mottie.github.io/tablesorter/docs/#Configuration)
 * @param {mixed} pager An object containing tablesorter's paging options (https://mottie.github.io/tablesorter/docs/#pager)
 *
 * == EVENTS ==
 *
 * ufTable triggers the following events:
 *
 * `pagerComplete.ufTable`: triggered when the tablesorter pager plugin has completed rendering of the table.
 *
 * == METHODS ==
 *
 * `getTableStateVars( table )`: fetches the current page size, page number, sort order, sort field, and column filters.
 *
 * UserFrosting https://www.userfrosting.com
 * @author Alexander Weissman <https://alexanderweissman.com>
 */
;(function($, window, document, undefined) {
    'use strict';

    // Define plugin name and defaults.
    var pluginName = 'ufTable',
        defaults = {
            DEBUG                : false,
            site                 : site, // global site variables
            dataUrl              : '',
            msgTarget            : $('#alerts-page'),
            addParams            : {},
            filterAllField       : '_all',
            useLoadingTransition : true,
            rowTemplate          : null,
            columnTemplates      : {},
            tablesorter     : {
                debug: false,
                theme     : 'bootstrap',
                widthFixed: true,
                // Set up pagination of data via an AJAX source
                // See http://jsfiddle.net/Mottie/uwZc2/
                // Also see https://mottie.github.io/tablesorter/docs/example-pager-ajax.html
                widgets: ['saveSort', 'sort2Hash', 'filter', 'pager', 'columnSelector', 'reflow2'],
                widgetOptions : {
                    columnSelector_layout : '<label><input type="checkbox"> <span>{name}</span></label>',
                    filter_cssFilter: 'form-control',
                    filter_saveFilters : true,
                    filter_serversideFiltering : true,
                    filter_selectSource : {
                        '.filter-select' : function() { return null; }
                    },

                    // apply disabled classname to the pager arrows when the rows at either extreme is visible
                    pager_updateArrows: true,

                    // starting page of the pager (zero based index)
                    pager_startPage: 0,

                    // Number of visible rows
                    pager_size: 10,

                    // Save pager page & size if the storage script is loaded (requires $.tablesorter.storage in jquery.tablesorter.widgets.js)
                    pager_savePages: true,

                    // if true, the table will remain the same height no matter how many records are displayed. The space is made up by an empty
                    // table row set to a height to compensate; default is false
                    pager_fixedHeight: false,

                    // remove rows from the table to speed up the sort of large tables.
                    // setting this to false, only hides the non-visible rows; needed if you plan to add/remove rows with the pager enabled.
                    pager_removeRows: false, // removing rows in larger tables speeds up the sort

                    // target the pager markup - see the HTML block below
                    pager_css: {
                        errorRow    : 'uf-table-error-row', // error information row
                        disabled    : 'disabled' // Note there is no period "." in front of this class name
                    },

                    // Must be initialized with a 'data' key
                    pager_ajaxObject: {
                        data: {},
                        dataType: 'json'
                    },

                    // hash prefix
                    sort2Hash_hash              : '#',
                    // don't '#' or '=' here
                    sort2Hash_separator         : '|',
                    // this option > table ID > table index on page
                    sort2Hash_tableId           : null,
                    // if true, show header cell text instead of a zero-based column index
                    sort2Hash_headerTextAttr    : 'data-column-name',
                    // direction text shown in the URL e.g. [ 'asc', 'desc' ]
                    sort2Hash_directionText     : [ 'asc', 'desc' ], // default values
                    // if true, override saveSort widget sort, if used & stored sort is available
                    sort2Hash_overrideSaveSort  : true, // default = false
                }
            }
        };

    // Constructor
    function Plugin (element, options) {
        this.element = element[0];
        this.$element = $(this.element);

        var lateDefaults = {
            download: {
                button: this.$element.find('.js-uf-table-download'),
                callback: $.proxy(this._onDownload, this)
            },
            info: {
                container: this.$element.find('.js-uf-table-info'),
                callback: $.proxy(this._renderInfoMessages, this)
            },
            overlay: {
                container: this.$element.find('.js-uf-table-overlay')
            },
            tableElement: this.$element.find('.tablesorter'),
            tablesorter: {
                widgetOptions: {
                    columnSelector_container : this.$element.find('.js-uf-table-cs-options'),
                    filter_external          : this.$element.find('.js-uf-table-search input'),

                    // Pager selectors
                    pager_selectors: {
                        container   : this.$element.find('.js-uf-table-pager'),
                        first       : '.first',       // go to first page arrow
                        prev        : '.prev',        // previous page arrow
                        next        : '.next',        // next page arrow
                        last        : '.last',        // go to last page arrow
                        gotoPage    : '.gotoPage',    // go to page selector - select dropdown that sets the current page
                        pageDisplay : '.pagedisplay', // location of where the "output" is displayed
                        pageSize    : '.pagesize'     // page size selector - select dropdown that sets the "size" option
                    },
                    // We need to use $.proxy to properly bind the context for callbacks that will be called by Tablesorter

                    // Generate the URL for the AJAX request, with the relevant parameters
                    pager_customAjaxUrl: $.proxy(this._generateUrl, this),

                    // Callback to process the response from the AJAX request
                    pager_ajaxProcessing: $.proxy(this._processAjax, this),

                    // Callback to display errors
                    pager_ajaxError: $.proxy(this._pagerAjaxError, this),

                    sort2Hash_encodeHash: $.proxy(this._encodeHash, this),

                    sort2Hash_decodeHash: $.proxy(this._decodeHash, this),

                    sort2Hash_cleanHash: $.proxy(this._cleanHash, this)
                }
            }
        };
        this.settings = $.extend(true, {}, defaults, lateDefaults, options);
        this._defaults = defaults;
        this._name = pluginName;
        this._debugAjax = (typeof this.settings.site !== 'undefined') && this.settings.site.debug.ajax;

        // Fall back to attributes from data-*, default values if not specified in options
        var pagerContainer = this.settings.tablesorter.widgetOptions.pager_selectors.container;
        var infoContainer = this.settings.info.container;
        var dataAttributeDefaults = {
            info: {
                messageEmptyRows: infoContainer.data('message-empty-rows') ?
                                  infoContainer.data('message-empty-rows') :
                                  "Sorry, we've got nothing here."
            },
            tablesorter: {
                widgetOptions: {
                    // possible variables: {size}, {page}, {totalPages}, {filteredPages}, {startRow}, {endRow}, {filteredRows} and {totalRows}
                    // also {page:input} & {startRow:input} will add a modifiable input in place of the value
                    pager_output: pagerContainer.data('output-template') ?
                                  pagerContainer.data('output-template') :
                                  '{startRow} to {endRow} of {filteredRows} ({totalRows})' // default if not set on data-* attribute
                }
            }
        };

        this.settings = $.extend(true, {}, dataAttributeDefaults, this.settings);

        // Check that tableElement exists
        var tableElement = this.settings.tableElement;
        if (!tableElement.length) {
            if (window.console && console.error) {
                console.error('ufTable could not be initialized: wrapper element does not exist, or does not contain a matched tableElement (see https://learn.userfrosting.com/client-side-code/components/tables )');
            }
            return;
        }

        // Copy over dataUrl to pager_ajaxUrl
        this.settings.tablesorter.widgetOptions.pager_ajaxUrl = this.settings.dataUrl;

        // Set up 'loading' overlays
        if (this.settings.useLoadingTransition) {
            var overlay = this.settings.overlay.container;
            tableElement.bind('sortStart filterStart pageMoved', function() {
                overlay.removeClass('hidden');
            }).bind('pagerComplete updateComplete', function() {
                overlay.addClass('hidden');
            });
        }

        // Set up tablesorter and pager
        this.ts = tableElement.tablesorter(this.settings.tablesorter);

        // Map default column template selectors based on data-column-template attribute in each column header
        var columns = this.ts[0].config.$headerIndexed;
        var columnTemplates = {};
        for (var col = 0; col < columns.length; col++) {
            var columnName = columns[col].data('column-name');
            if (!columnName && this.settings.DEBUG) {
                console.error('Column number ' + col + ' is missing a data-column-name attribute.');
            }
            columnTemplates[columnName] = columns[col].data('column-template');
        }

        // Merge in any column template selectors that were set in the ctor options
        columnTemplates = $.extend(true, columnTemplates, this.settings.columnTemplates);

        // Locate and compile templates for any string-identified column renderers
        // At the same time, build out a numerically indexed array of templates
        this.columnTemplatesIndexed = [];
        for (var col = 0; col < columns.length; col++) {
            var columnName = columns[col].data('column-name');
            if (!columnTemplates[columnName] && this.settings.DEBUG) {
                console.error("No template found for column '" + columnName + "'.");
            }
            var columnTemplate = columnTemplates[columnName];
            if (typeof columnTemplate === 'string') {
                this.columnTemplatesIndexed.push(Handlebars.compile($(columnTemplate).html()));
            } else {
                this.columnTemplatesIndexed.push(columnTemplate);
            }
        }

        // Locate and compile row template
        this.rowTemplate = Handlebars.compile('<tr>');
        // If rowTemplateSelector is set, then find the DOM element that it references, which contains the template
        if (this.settings.rowTemplate) {
            var rowTemplate = this.settings.rowTemplate;
            if (typeof rowTemplate === 'string') {
                this.rowTemplate = Handlebars.compile($(this.settings.rowTemplate).html());
            } else {
                this.rowTemplate = rowTemplate;
            }
        }

        // Link CSV download button
        this.settings.download.button.on('click', this.settings.download.callback);

        // Allow clicking on the labels in the table menu without closing the menu
        $(this.settings.tablesorter.widgetOptions.columnSelector_container).find('label').on('click', function(e) {
            e.stopPropagation();
        });

        // Propagate our own pagerComplete event
        this.ts.on('pagerComplete', $.proxy(function () {
            this.$element.trigger('pagerComplete.ufTable');
        }, this));

        // Show info messages when there are no rows/no results
        this.ts.on('filterEnd filterReset pagerComplete', this.settings.info.callback);

        // Detect changes to element attributes
        this.$element.attrchange({
            callback: function (event) {
                this.element = event.target;
            }.bind(this)
        });

        return this;
    }

    /**
     * Get state variables for this table, as required by the AJAX data source: sorts, filters, size, page
     */
    Plugin.prototype.getTableStateVars = function(table) {
        var base = this;

        // Get sort column and order
        var sortOrders = {
            '0': 'asc',
            '1': 'desc'
        };

        // Set sorts in URL.  Assumes each th has a data-column-name attribute that corresponds to the name in the API
        var sortList = table.config.sortList;
        var sorts = {};
        for (var i = 0; i < sortList.length; i++) {
            var columnIndex = sortList[i][0];
            var columnDirection = sortOrders[sortList[i][1]];   // Converts to 'asc' or 'desc'
            if (sortList[i]) {
                var columnName = table.config.$headerIndexed[columnIndex].data('column-name');
                sorts[columnName] = columnDirection;
            }
        }

        // Set filters in URL.  Assumes each th has a data-column-name attribute that corresponds to the name in the API
        var filterList = base.getSavedFilters(table);
        var filters = {};
        for (i = 0; i < filterList.length; i++) {
            if (filterList[i]) {
                var columnName = base.settings.filterAllField;

                if (table.config.$headerIndexed[i]) {
                    columnName = table.config.$headerIndexed[i].data('column-name');
                }

                filters[columnName] = filterList[i];
            }
        }

        var state = {
            size: table.config.pager.size,
            page: table.config.pager.page,
            sorts: sorts,
            filters: filters
        };

        return state;
    };

    /**
     * Get saved filters from the browser local storage. Those should always be up to date
     */
    Plugin.prototype.getSavedFilters = function(table) {

        // Fallback to `getFilters` or empty in case of failure
        var filterList = $.tablesorter.getFilters(table) || [];

        // Overwrite list with saved filter for filter-select not setup by ts
        var isArray, saved,
            wo = table.config.widgetOptions;
        if ( wo.filter_saveFilters && $.tablesorter.storage ) {
            saved = $.tablesorter.storage( table, 'tablesorter-filters' ) || [];
            isArray = $.isArray( saved );
            // make sure we're not just getting an empty array
            if ( !( isArray && saved.join( '' ) === '' || !isArray ) ) {
                filterList = $.tablesorter.filter.processFilters( saved );
            }
        }

        return filterList;
    };

    /**
     * Generate the AJAX url.
     * Used as the default callback for pager_customAjaxUrl
     * @private
     */
    Plugin.prototype._generateUrl = function(table, url) {
        var tableState = this.getTableStateVars(table);

        if (this.settings.DEBUG) {
            console.log(tableState);
        }

        $.extend(table.config.pager.ajaxObject.data, tableState);

        // Merge in any additional parameters
        $.extend(table.config.pager.ajaxObject.data, this.settings.addParams);

        return url;
    };
    /**
     * Process data returned from the AJAX request and rendering the table cells.
     * Used as the default callback for pager_ajaxProcessing
     * @private
     */
    Plugin.prototype._processAjax = function(data) {
        var ts = this.ts[0];
        var json = {},
            rows = '';

        if (data) {
            var size = data.rows.length;

            // Render table rows and cells via Handlebars
            for (var row = 0; row < size; row++) {
                var cellData = {
                    rownum: row,
                    row   : data.rows[row],       // It is safe to use the data from the API because Handlebars escapes HTML
                    site  : this.settings.site
                };

                rows += this.rowTemplate(cellData);

                for (var col = 0; col < this.columnTemplatesIndexed.length; col++) {
                    rows += this.columnTemplatesIndexed[col](cellData);
                }

                rows += '</tr>';
            }

            // Initialize any dropdown filters
            var columns = ts.config.$headerIndexed;
            this._ajaxInitFilterSelects(columns, data.listable);

            json.total = data.count;  // Get total rows without pagination
            json.filteredRows = data.count_filtered; // no filtering
            json.rows = $(rows);
            json.output = data.output;
        } else {
            json.total = 0;
            json.filteredRows = 0;
            json.rows = '';
        }

        return json;
    };

    /**
     * Initialize filter select menus using the ajax `listable` values
     * @private
     */
    Plugin.prototype._ajaxInitFilterSelects = function(columns, listable) {
        var ts = this.ts[0];
        var filters = this.getSavedFilters(ts);
        // Find columns with `.filter-select` and match them to column numbers based on their data-column-name
        for (var col = 0; col < columns.length; col++) {
            var column = columns[col];
            // If the column is designated for filter-select, get the listables from the data and recreate it
            if (column.hasClass('filter-select')) {
                var columnName = column.data('column-name');
                if (listable[columnName]) {
                    $.tablesorter.filter.buildSelect(ts, col, listable[columnName], true);
                    // If there is a filter actually set for this column, update the selected option.
                    if (filters[col]) {
                        var selectControl = $(ts).find(".tablesorter-filter[data-column='" + col + "']");
                        selectControl.val(filters[col]);
                    }
                }
            }
        }
    };

    /**
     * Implements handler for the "download CSV" button.
     * Default callback for download.callback
     * @private
     */
    Plugin.prototype._onDownload = function () {
        var tableState = this.getTableStateVars(this.ts[0]);
        tableState.format = 'csv';
        delete tableState.page;
        delete tableState.size;

        // Merge in any additional request parameters
        $.extend(tableState, this.settings.addParams);

        // Causes download to begin
        window.location = this.settings.dataUrl + '?' + $.param(tableState);
    };

    /**
     * Handle pager ajax errors.
     * @private
     */
    Plugin.prototype._pagerAjaxError = function(c, jqXHR, settings, exception) {
        this._ajaxError(jqXHR);

        // Let TS handle the in-table error message
        return '';
    };

    /**
     * Handle ajax error
     * @private
     */
    Plugin.prototype._ajaxError = function(jqXHR) {
        if (typeof jqXHR === 'object') {
            // Error messages
            if (this._debugAjax && jqXHR.responseText) {
                document.write(jqXHR.responseText);
                document.close();
            } else {
                if (this.settings.DEBUG) {
                    console.log('Error (' + jqXHR.status + '): ' + jqXHR.responseText );
                }
                // Display errors on failure
                // TODO: ufAlerts widget should have a 'destroy' method
                if (!this.settings.msgTarget.data('ufAlerts')) {
                    this.settings.msgTarget.ufAlerts();
                } else {
                    this.settings.msgTarget.ufAlerts('clear');
                }

                this.settings.msgTarget.ufAlerts('fetch').ufAlerts('render');
            }
        }
    };

    /**
     * Render info messages, such as when there are no results.
     * Default callback for info.callback
     * @private
     */
    Plugin.prototype._renderInfoMessages = function () {
        var table = this.ts[0];
        var infoMessages = this.settings.info.container;
        if (table.config.pager) {
            infoMessages.html('');
            var fr = table.config.pager.filteredRows;
            if (fr === 0) {
                infoMessages.html(this.settings.info.messageEmptyRows);
            }
        }
    };

    /**
     * Encode the current table state variables into a URL hash.
     * Default callback for sort2Hash_encodeHash
     * @private
     */
    Plugin.prototype._encodeHash = function(config, tableId, component, value, rawValue) {
        var wo = config.widgetOptions;
        if ( component === 'filter' ) {
            // rawValue is an array of filter values, numerically indexed
            var encodedFilters = '';
            var len = rawValue.length;
            for (var index = 0; index < len; index++) {
                if (rawValue[index]) {
                    var columnName = this.settings.filterAllField;
                    if (config.$headerIndexed[index]) {
                        columnName = $(config.$headerIndexed[index][0]).attr(wo.sort2Hash_headerTextAttr);
                    }
                    encodedFilters += '&filter[' + tableId + '][' + columnName + ']=' + encodeURIComponent(rawValue[index]);
                }
            }
            return encodedFilters;
        } else if ( component === 'sort' ) {
            // rawValue is an array of sort pairs [columnNum, sortDirection]
            var encodedFilters = '';
            var len = rawValue.length;
            for (var index = 0; index < len; index++) {
                var columnNum = rawValue[index][0];
                var sortDirection = rawValue[index][1];
                var columnName = $(config.$headerIndexed[columnNum][0]).attr(wo.sort2Hash_headerTextAttr);
                encodedFilters += '&sort[' + tableId + '][' + columnName + ']=' + wo.sort2Hash_directionText[sortDirection];
            }
            return encodedFilters;
        }
        return false;
    };

    /**
     * Decode the current table state variables from the URL hash.
     * Default callback for sort2Hash_decodeHash
     * @private
     */
    Plugin.prototype._decodeHash = function(config, tableId, component) {
        var wo = config.widgetOptions;
        var result;
        // Convert hash into JSON object
        var urlObject = $.String.deparam(window.location.hash);
        delete urlObject[wo.sort2Hash_hash];  // Remove hash character
        if (component === 'filter') {
            var decodedFilters = [];
            // Extract filter names and values for the specified table
            var pageFilters = urlObject.filter ? urlObject.filter : [];
            if (pageFilters[tableId]) {
                var tableFilters = pageFilters[tableId];
                // Build a numerically indexed array of filter values
                var len = config.$headerIndexed.length;
                for (var index = 0; index < len; index++) {
                    var columnName = $(config.$headerIndexed[index][0]).attr(wo.sort2Hash_headerTextAttr);
                    if (tableFilters[columnName] && tableFilters[columnName] != this.settings.filterAllField) {
                        decodedFilters.push(tableFilters[columnName]);
                    } else {
                        decodedFilters.push('');
                    }
                }
                // Convert array of filter values to a delimited string
                result = decodedFilters.join(wo.sort2Hash_separator);
                // make sure to use decodeURIComponent on the result
                return decodeURIComponent(result);
            } else {
                return '';
            }
        }
        return false;
    };

    /**
     * Clean up URL hash.
     * Default callback for sort2Hash_cleanHash
     * @private
     */
    Plugin.prototype._cleanHash = function(config, tableId, component, hash) {
        var wo = config.widgetOptions;
        // Convert hash to JSON object
        var urlObject = $.String.deparam(hash);
        delete urlObject[wo.sort2Hash_hash];  // Remove hash character
        // Remove specified component for specified table
        if (urlObject[component]) {
            if (urlObject[component][tableId]) {
                delete urlObject[component][tableId];
            }
            // Delete entire component if no other tables remaining
            if (jQuery.isEmptyObject(urlObject[component])) {
                delete urlObject[component];
            }
        }
        // Convert modified JSON object back into serialized representation
        var result = decodeURIComponent(jQuery.param(urlObject));
        return result.length ? result : '';
    };

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
        // Otherwise ensure first parameter is a valid string, and is the name of an actual function.
        } else if (typeof methodOrOptions === 'string' && typeof instance[methodOrOptions] === 'function') {
            // Ensure not a private function
            if (methodOrOptions.indexOf('_') !== 0) {
                return instance[methodOrOptions]( Array.prototype.slice.call(arguments, 1));
            }
            else {
                console.warn( 'Method ' +  methodOrOptions + ' is private!' );
            }
        } else {
            console.warn( 'Method ' +  methodOrOptions + ' does not exist.' );
        }
    };
})(jQuery, window, document);
