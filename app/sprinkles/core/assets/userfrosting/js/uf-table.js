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
    "use strict";

    // Define plugin name and defaults.
    var pluginName = "ufTable",
        defaults = {
            DEBUG           : false,
            dataUrl         : "",
            msgTarget       : $('#alerts-page'),
            addParams       : {},
            filterAllField: '_all',
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
                    // jQuery selectors
                    pager_selectors: {
                      container   : '.pager',       // target the pager markup (wrapper)
                      first       : '.first',       // go to first page arrow
                      prev        : '.prev',        // previous page arrow
                      next        : '.next',        // next page arrow
                      last        : '.last',        // go to last page arrow
                      gotoPage    : '.gotoPage',    // go to page selector - select dropdown that sets the current page
                      pageDisplay : '.pagedisplay', // location of where the "output" is displayed
                      pageSize    : '.pagesize'     // page size selector - select dropdown that sets the "size" option
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
            downloadButton: this.$element.find('.js-uf-table-download'),
            info: {
                container: this.$element.find('.js-uf-table-info')
            },
            tablesorter: {
                widgetOptions: {
                    columnSelector_container : this.$element.find('.js-uf-table-cs-options'),
                    filter_external          : this.$element.find('.js-uf-table-search input'),
                    pager_css: {
                        container: this.$element.find('.js-uf-table-pager')
                    }
                }
            }
        };
        this.settings = $.extend(true, {}, defaults, lateDefaults, options);
        this._defaults = defaults;
        this._name = pluginName;

        // Fall back to attributes from data-*, default values if not specified in options
        var pagerContainer = this.settings.tablesorter.widgetOptions.pager_css.container;
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

        this.settings.tablesorter.widgetOptions.pager_ajaxUrl = this.settings.dataUrl;

        // Generate the URL for the AJAX request, with the relevant parameters
        this.settings.tablesorter.widgetOptions.pager_customAjaxUrl = $.proxy(function (table, url) {
            return this._generateUrl(this, table, url);
        }, this);

        // Callback to process the response from the AJAX request
        this.settings.tablesorter.widgetOptions.pager_ajaxProcessing = $.proxy(function (data) {
            return this._processAjax(this, data);
        }, this);

        // Callback to display errors
        this.settings.tablesorter.widgetOptions.pager_ajaxError = $.proxy(function (config, xhr, settings, exception) {
            return this._pagerAjaxError(this, config, xhr, settings, exception);
        }, this);

        this.settings.tablesorter.widgetOptions.sort2Hash_encodeHash = $.proxy(function (config, tableId, component, value, rawValue) {
            return this._encodeHash(this, config, tableId, component, value, rawValue);
        }, this);

        this.settings.tablesorter.widgetOptions.sort2Hash_decodeHash = $.proxy(function (config, tableId, component) {
            return this._decodeHash(this, config, tableId, component);
        }, this);

        this.settings.tablesorter.widgetOptions.sort2Hash_cleanHash = $.proxy(function (config, tableId, component, hash) {
            return this._cleanHash(this, config, tableId, component, hash);
        }, this);

        // Set up tablesorter and pager
        this.ts = this.$element.find('.tablesorter').tablesorter(this.settings.tablesorter);

        // Link CSV download button
        this.settings.downloadButton.on('click', $.proxy(function () {
            var tableState = this.getTableStateVars(this.ts[0]);
            tableState['format'] = 'csv';
            delete tableState['page'];
            delete tableState['size'];

            // Merge in any additional request parameters
            $.extend(tableState, this.settings.addParams);

            // Causes download to begin
            window.location = this.settings.dataUrl + '?' + $.param(tableState);
        }, this));

        // Allow clicking on the labels in the table menu without closing the menu
        $(this.settings.tablesorter.widgetOptions.columnSelector_container).find('label').on('click', function(e) {
            e.stopPropagation();
        });

        this.ts.on('pagerComplete', $.proxy(function () {
            this.$element.trigger('pagerComplete.ufTable');
        }, this));

        // Show info messages when there are no rows/no results
        this.ts.on('filterEnd filterReset pagerComplete', $.proxy(function () {
            var table = this.ts[0];
            var infoMessages = this.settings.info.container;
            if (table.config.pager) {
                infoMessages.html('');
                var fr = table.config.pager.filteredRows;
                if (fr === 0) {
                    infoMessages.html(this.settings.info.messageEmptyRows);
                }
            }
        }, this));

        // Detect changes to element attributes
        this.$element.attrchange({
            callback: function (event) {
                this.element = event.target;
            }.bind(this)
        });

        return this;
    }

    // Functions
    $.extend(Plugin.prototype, {
        /**
         * Get state variables for this table, as required by the AJAX data source: sorts, filters, size, page
         */
        getTableStateVars: function(table) {
            var base = this;

            // Get sort column and order
            var sortOrders = {
                '0' : 'asc',
                '1' : 'desc'
            };

            // Set sorts in URL.  Assumes each th has a data-column-name attribute that corresponds to the name in the API
            var sortList = table.config.sortList;
            var sorts = {};
            for (var i = 0; i < sortList.length; i++) {
                var columnIndex = sortList[i][0];
                var columnDirection = sortOrders[sortList[i][1]];   // Converts to 'asc' or 'desc'
                if (sortList[i]) {
                    var columnName = $(table.config.headerList[columnIndex]).data('column-name');
                    sorts[columnName] = columnDirection;
                }
            }

            // Set filters in URL.  Assumes each th has a data-column-name attribute that corresponds to the name in the API
            var filterList = base.getSavedFilters(table);
            var filters = {};
            for (i = 0; i < filterList.length; i++) {
                if (filterList[i]) {
                    if (table.config.headerList[i]) {
                        var columnName = $(table.config.headerList[i]).data('column-name');
                    } else {
                        var columnName = base.settings.filterAllField;
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
        },
        /**
         * Get saved filters from the browser local storage. Those should always be up to date
         */
        getSavedFilters: function(table) {

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
        },
        /**
         * Callback for generating the AJAX url.
         */
        _generateUrl: function(base, table, url) {
            var tableState = base.getTableStateVars(table);

            if (base.settings.DEBUG) {
                console.log(tableState);
            }

            $.extend(table.config.pager.ajaxObject.data, tableState);

            // Merge in any additional parameters
            $.extend(table.config.pager.ajaxObject.data, base.settings.addParams);

            return url;
        },
        /**
         * Callback for processing data returned from the AJAX request and rendering the table cells.
         */
        _processAjax: function(base, data) {
            var ts = base.ts[0];
            var col, row, txt,
                // make # column show correct value
                index = ts.config.pager.page,
                json = {},
                rows = '';

            if (data) {
                var size = data.rows.length;

                // Build Handlebars templates based on column-template attribute in each column header
                var columns = ts.config.headerList;
                var templates = [];
                for (var i = 0; i < columns.length; i++) {
                    var columnName = $(columns[i]).data('column-template');
                    templates.push(Handlebars.compile($(columnName).html()));
                }

                // Render table cells via Handlebars
                for (var row = 0; row < size; row++) {
                    rows += '<tr>';
                    var cellData = {
                        'row'  : data.rows[ row ],       // It is safe to use the data from the API because Handlebars escapes HTML
                        'site' : site
                    };

                    for (i = 0; i < columns.length; i++) {
                        rows += templates[i](cellData);
                    }

                    rows += '</tr>';
                }

                // Find columns with `.filter-select` and match them to column numbers based on their data-column-name
                var columns = ts.config.headerList;
                var selectColumnNames = [];
                var selectColumnNumbers = {};
                for (var i = 0; i < columns.length; i++) {
                    var column = $(columns[i]);
                    // If the column is designated for filter-select, get the listables from the data and recreate it
                    if (column.hasClass('filter-select')) {
                        var columnName = column.data('column-name');
                        if (data.listable[columnName]) {
                            $.tablesorter.filter.buildSelect(ts, i, data.listable[columnName], true);
                        }
                    }
                }

                json.total = data.count;  // Get total rows without pagination
                json.filteredRows = data.count_filtered; // no filtering
                json.rows = $(rows);
                json.output = data.output;
            } else {
                json.total = 0;
                json.filteredRows = 0;
                json.rows = "";
            }

            return json;
        },
        /**
         * Handle pager ajax error
         */
        _pagerAjaxError: function(base, c, jqXHR, settings, exception) {
            base._ajaxError(jqXHR);

            // Let TS handle the in-table error message
            return '';
        },
        /**
         * Handle ajax error
         */
        _ajaxError: function(jqXHR) {
            if (typeof jqXHR === 'object') {
                // Error messages
                if ((typeof site !== 'undefined') && site.debug.ajax && jqXHR.responseText) {
                    document.write(jqXHR.responseText);
                    document.close();
                } else {
                    if (this.settings.DEBUG) {
                        console.log("Error (" + jqXHR.status + "): " + jqXHR.responseText );
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
        },
        /**
         * Private method used to encode the current table state variables into a URL hash.
         */
        _encodeHash: function(base, config, tableId, component, value, rawValue) {
            var wo = config.widgetOptions;
            if ( component === 'filter' ) {
                // rawValue is an array of filter values, numerically indexed
                var encodedFilters = "";
                var len = rawValue.length;
                for ( index = 0; index < len; index++ ) {
                    if (rawValue[index]) {
                        if (config.$headerIndexed[index]) {
                            var columnName = $(config.$headerIndexed[index][0]).attr(wo.sort2Hash_headerTextAttr);
                        } else {
                            var columnName = base.settings.filterAllField;
                        }
                        encodedFilters += '&filter[' + tableId + '][' + columnName + ']=' + encodeURIComponent(rawValue[index]);
                    }
                }
                return encodedFilters;
            } else if ( component === 'sort' ) {
                // rawValue is an array of sort pairs [columnNum, sortDirection]
                var encodedFilters = "";
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
        },
        /**
         * Private method used to decode the current table state variables from the URL hash.
         */
        _decodeHash: function(base, config, tableId, component) {
            base = this;
            var wo = config.widgetOptions;
            var result;
            // Convert hash into JSON object
            var urlObject = $.String.deparam(window.location.hash);
            delete urlObject[wo.sort2Hash_hash];  // Remove hash character
            if (component === 'filter') {
                var decodedFilters = [];
                // Extract filter names and values for the specified table
                var filters = urlObject.filter ? urlObject.filter : [];
                if (filters[tableId]) {
                    var filters = filters[tableId];
                    // Build a numerically indexed array of filter values
                    var len = config.$headerIndexed.length;
                    for (var index = 0; index < len; index++) {
                        var columnName = $(config.$headerIndexed[index][0]).attr(wo.sort2Hash_headerTextAttr);
                        if (filters[columnName] && filters[columnName] != base.settings.filterAllField) {
                            decodedFilters.push(filters[columnName]);
                        } else {
                            decodedFilters.push('');
                        }
                    }
                    // Convert array of filter values to a delimited string
                    result = decodedFilters.join(wo.sort2Hash_separator);
                    // make sure to use decodeURIComponent on the result
                    return decodeURIComponent( result );
                } else {
                    return '';
                }
            }
            return false;
        },
        /**
         * Private method used to clean up URL hash.
         */
        _cleanHash: function(base, config, tableId, component, hash) {
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
            var result = jQuery.param(urlObject);
            return result.length ? result : '';
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
                $.error( 'Method ' +  methodOrOptions + ' is private!' );
            }
        }
        else {
            $.error( 'Method ' +  methodOrOptions + ' does not exist.' );
        }
    };
})(jQuery, window, document);