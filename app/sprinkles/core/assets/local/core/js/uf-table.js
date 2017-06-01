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
 * - Your table should have a unique id, and your paging controls should be wrapped in an element with the `.tablesorter-pager` class.
 * - Create a button with the `.js-download-table` class, and it will be automatically bound to trigger an AJAX request for downloading the table (CSV, etc).
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

       <div class="pager pager-lg tablesorter-pager">
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

       <button class="btn btn-sm btn-default js-download-table">Download CSV</button>
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
 * @author Alexander Weissman https://alexanderweissman.com
 */

(function( $ )
{
    /**
     * The plugin namespace, ie for $('.selector').ufTable(options)
     *
     * Also the id for storing the object state via $('.selector').data()
     */
    var PLUGIN_NS = 'ufTable';

    var Plugin = function ( target, options )
    {
        this.$T = $(target);
        var base = this;

        /** #### OPTIONS #### */
        this.options= $.extend(
            true,               // deep extend
            {
                DEBUG           : false,
                dataUrl         : "",
                selectOptionsUrl: null,
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
                        columnSelector_container : this.$T.find('.menu-table-column-selector-options'),
                        columnSelector_layout : '<label><input type="checkbox"> <span>{name}</span></label>',
                        filter_cssFilter: 'form-control',
                        filter_external : this.$T.find('.table-search input'),
                        filter_saveFilters : true,
                        filter_serversideFiltering : true,
                        filter_selectSource : {
                            '.filter-select' : function() { return null; }
                        },
                        // output default: '{page}/{totalPages}'
                        // possible variables: {size}, {page}, {totalPages}, {filteredPages}, {startRow}, {endRow}, {filteredRows} and {totalRows}
                        // also {page:input} & {startRow:input} will add a modifiable input in place of the value
                        pager_output: '{startRow} to {endRow} of {filteredRows} ({totalRows})', // '{page}/{totalPages}'

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
                        pager_fixedHeight: true,

                        // remove rows from the table to speed up the sort of large tables.
                        // setting this to false, only hides the non-visible rows; needed if you plan to add/remove rows with the pager enabled.
                        pager_removeRows: false, // removing rows in larger tables speeds up the sort

                        // target the pager markup - see the HTML block below
                        pager_css: {
                            container: this.$T.find('.tablesorter-pager'),
                            errorRow    : 'tablesorter-errorRow', // error information row
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
            },
            options
        );

        this._init( target, options );

        return this;
    };

    /**
     * Get state variables for this table, as required by the AJAX data source: sorts, filters, size, page
     */
    Plugin.prototype.getTableStateVars = function ( table ) {
        var base = this;

        // Get sort column and order
        var sortOrders = {
            '0' : 'asc',
            '1' : 'desc'
        };

        // Set sorts in URL.  Assumes each th has a data-column-name attribute that corresponds to the name in the API
        var sortList = table.config.sortList;
        var sorts = {};
        for (i = 0; i < sortList.length; i++) {
            var columnIndex = sortList[i][0];
            var columnDirection = sortOrders[sortList[i][1]];   // Converts to 'asc' or 'desc'
            if (sortList[i]) {
                var columnName = $(table.config.headerList[columnIndex]).data('column-name');
                sorts[columnName] = columnDirection;
            }
        }

        // Set filters in URL.  Assumes each th has a data-column-name attribute that corresponds to the name in the API
        var filterList = $.tablesorter.getFilters(table);
        var filters = {};
        for (i = 0; i < filterList.length; i++) {
            if (filterList[i]) {
                if (table.config.headerList[i]) {
                    var columnName = $(table.config.headerList[i]).data('column-name');
                } else {
                    var columnName = base.options.filterAllField;
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

    /** #### INITIALISER #### */
    Plugin.prototype._init = function ( target, options )
    {
        var base = this;
        var $el = $(target);

        base.options.tablesorter.widgetOptions.pager_ajaxUrl = base.options.dataUrl;

        // Generate the URL for the AJAX request, with the relevant parameters
        base.options.tablesorter.widgetOptions.pager_customAjaxUrl = function ( table, url ) {
            return base._generateUrl(base, table, url);
        };

        // Callback to process the response from the AJAX request
        base.options.tablesorter.widgetOptions.pager_ajaxProcessing = function ( data ) {
            return base._processAjax(base, data);
        };

        // Callback to display errors
        base.options.tablesorter.widgetOptions.pager_ajaxError = function ( config, xhr, settings, exception ) {
            return base._pagerAjaxError(base, config, xhr, settings, exception);
        };

        base.options.tablesorter.widgetOptions.sort2Hash_encodeHash = function (config, tableId, component, value, rawValue) {
            return base._encodeHash(base, config, tableId, component, value, rawValue);
        };

        base.options.tablesorter.widgetOptions.sort2Hash_decodeHash = function (config, tableId, component) {
            return base._decodeHash(base, config, tableId, component);
        };

        base.options.tablesorter.widgetOptions.sort2Hash_cleanHash = function (config, tableId, component, hash ) {
            return base._cleanHash(base, config, tableId, component, hash);
        };

        // Set up tablesorter and pager
        base.ts = $el.find('.tablesorter').tablesorter(base.options.tablesorter);

        // Link CSV download button
        $el.find('.js-download-table').on('click', function () {
            var tableState = base.getTableStateVars(base.ts[0]);
            tableState['format'] = 'csv';
            delete tableState['page'];
            delete tableState['size'];

            // Merge in any additional request parameters
            $.extend(tableState, base.options.addParams);

            // Causes download to begin
            window.location = base.options.dataUrl + '?' + $.param( tableState );
        });

        // Set up filter selects
        base.ts.on('filterInit', function () {
            base._buildFilterSelect(base.ts);
        });

        // Allow clicking on the labels in the table menu without closing the menu
        $(base.options.tablesorter.widgetOptions.columnSelector_container).find('label').on('click', function(e) {
            e.stopPropagation();
        });

        base.ts.on('pagerComplete', function () {
            $el.trigger('pagerComplete.ufTable');
        });
    };

    /**
     * Callback for generating the AJAX url.
     */
    Plugin.prototype._generateUrl = function ( base, table, url ) {
        var tableState = base.getTableStateVars(table);

        if (base.options.DEBUG) {
            console.log(tableState);
        }

        $.extend(table.config.pager.ajaxObject.data, tableState);

        // Merge in any additional parameters
        $.extend(table.config.pager.ajaxObject.data, base.options.addParams);

        return url;
    };

    /**
     * Callback for processing data returned from the AJAX request and rendering the table cells.
     */
    Plugin.prototype._processAjax = function ( base, data ) {
        var ts = base.ts[0];
        var col, row, txt,
            // make # column show correct value
            index = ts.config.pager.page,
            json = {},
            rows = '';

        if (data) {
            size = data.rows.length;

            // Build Handlebars templates based on column-template attribute in each column header
            var columns = ts.config.headerList;
            var templates = [];
            for (i = 0; i < columns.length; i++) {
                var columnName = $(columns[i]).data('column-template');
                templates.push(Handlebars.compile($(columnName).html()));
            }

            // Render table cells via Handlebars
            for (row = 0; row < size; row++) {
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

            json.total = data.count;  // Get total rows without pagination
            json.filteredRows = data.count_filtered; // no filtering
            json.rows = $(rows);
        } else {
            json.total = 0;
            json.filteredRows = 0;
            json.rows = "";
        }

        return json;
    };

    Plugin.prototype._pagerAjaxError = function(base, c, jqXHR, settings, exception) {
        base._ajaxError(jqXHR);

        // Let TS handle the in-table error message
        return '';
    };

    Plugin.prototype._ajaxError = function(jqXHR) {
        base = this;

        if (typeof jqXHR === 'object') {
            // Error messages
            if ((typeof site !== 'undefined') && site.debug.ajax && jqXHR.responseText) {
                document.write(jqXHR.responseText);
                document.close();
            } else {
                if (base.options.DEBUG) {
                    console.log("Error (" + jqXHR.status + "): " + jqXHR.responseText );
                }
                // Display errors on failure
                // TODO: ufAlerts widget should have a 'destroy' method
                if (!base.options.msgTarget.data('ufAlerts')) {
                    base.options.msgTarget.ufAlerts();
                } else {
                    base.options.msgTarget.ufAlerts('clear');
                }

                base.options.msgTarget.ufAlerts('fetch').ufAlerts('render');
            }
        }
    };

    /**
     * Private method used to encode the current table state variables into a URL hash.
     */
    Plugin.prototype._encodeHash = function (base, config, tableId, component, value, rawValue) {
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
                        var columnName = base.options.filterAllField;
                    }
                    encodedFilters += '&filter[' + tableId + '][' + columnName + ']=' + encodeURIComponent(rawValue[index]);
                }
            }
            return encodedFilters;
        } else if ( component === 'sort' ) {
            // rawValue is an array of sort pairs [columnNum, sortDirection]
            var encodedFilters = "";
            var len = rawValue.length;
            for ( index = 0; index < len; index++ ) {
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
     * Private method used to decode the current table state variables from the URL hash.
     */
    Plugin.prototype._decodeHash = function (base, config, tableId, component ) {
        base = this;
        var wo = config.widgetOptions;
        var result;
        // Convert hash into JSON object
        var urlObject = $.String.deparam(window.location.hash);
        delete urlObject[wo.sort2Hash_hash];  // Remove hash character
        if ( component === 'filter' ) {
            var decodedFilters = [];
            // Extract filter names and values for the specified table
            var filters = urlObject.filter ? urlObject.filter : [];
            if (filters[tableId]) {
                var filters = filters[tableId];
                // Build a numerically indexed array of filter values
                var len = config.$headerIndexed.length;
                for ( index = 0; index < len; index++ ) {
                    var columnName = $(config.$headerIndexed[index][0]).attr(wo.sort2Hash_headerTextAttr);
                    if (filters[columnName] && filters[columnName] != base.options.filterAllField) {
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
    };

    /**
     * Private method used to clean up URL hash.
     */
    Plugin.prototype._cleanHash = function (base, config, tableId, component, hash ) {
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
        result = jQuery.param(urlObject);
        return result.length ? result : '';
    };

    /**
     * Private method used to build the filter select using data attributes for custom options
     * Based on tablesorter.filter.getOptions
     */
    Plugin.prototype._buildFilterSelect = function (table) {
        var base = this;

        if (base.options.selectOptionsUrl) {
            // Find columns with `.filter-select` and match them to column numbers based on their data-column-name
            var columns = table[0].config.headerList;
            var selectColumnNames = [];
            var selectColumnNumbers = {};
            for (i = 0; i < columns.length; i++) {
                var column = $(columns[i]);
                // If the column is designated for filter-select, add it to the list of listables and map the column number
                if (column.hasClass('filter-select')) {
                    var columnName = column.data('column-name');
                    selectColumnNames.push(columnName);
                    selectColumnNumbers[columnName] = i;
                }
            }

            // Make AJAX request for column select options
            $.getJSON(base.options.selectOptionsUrl, {
                lists: selectColumnNames
            }).done(function(data, textStatus, jqXHR) {
                // For each filter-select column, try to build the select menu from the corresponding entry in the AJAX response
                $.each(selectColumnNumbers, function (columnName, columnNumber) {
                    if (data[columnName]) {
                        $.tablesorter.filter.buildSelect(table, columnNumber, data[columnName], true);
                    }
                });
            }).fail(function (jqXHR, textStatus, errorThrown) {
                base._ajaxError(jqXHR);
            });
        }
        return false;
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
    };
    Plugin.prototype.DWARN = function ()
    {
        this.DEBUG && console.warn( arguments );
    };


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
