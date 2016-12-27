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
           <span class='pager-control first' title='First page'><i class='fa fa-angle-double-left'></i></span>
           <span class='pager-control prev' title='Previous page'><i class='fa fa-angle-left'></i></span>
           <span class='pagedisplay'></span>
           <span class='pager-control next' title='Next page'><i class='fa fa-angle-right'></i></span>
           <span class='pager-control last' title= 'Last page'><i class='fa fa-angle-double-right'></i></span>
           <br><br>
           Jump to Page: <select class='gotoPage'></select> &bull; Show:
           <select class='pagesize'>
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
                DEBUG       : false,
                dataUrl     : "",
                addParams   : {},
                tablesorter : {
                    debug: false,
                    theme     : 'bootstrap',
                    widthFixed: true,
                    // Set up pagination of data via an AJAX source
                    // See http://jsfiddle.net/Mottie/uwZc2/
                    // Also see https://mottie.github.io/tablesorter/docs/example-pager-ajax.html
                    widgets: ['saveSort','sort2Hash','filter'],
                    widgetOptions : {
                        filter_saveFilters : true,
                        // hash prefix
                        sort2Hash_hash              : '#',
                        // don't '#' or '=' here
                        sort2Hash_separator         : '|',
                        // this option > table ID > table index on page
                        sort2Hash_tableId           : null,
                        // if true, show header cell text instead of a zero-based column index
                        sort2Hash_headerTextAttr    : 'data-column-name',
                        // allow processing of text if sort2Hash_useHeaderText: true
                        sort2Hash_processHeaderText : function( text, config, columnIndex ) {
                            var column_name = $(config.headerList[columnIndex]).data("column-name");
                            if (column_name) {
                                return column_name;
                            } else {
                                return columnIndex;
                            }
                        },
                        sort2Hash_encodeHash : base._encodeHash,
                        sort2Hash_decodeHash : base._decodeHash,
                        sort2Hash_cleanHash : base._cleanHash,
                        // direction text shown in the URL e.g. [ 'asc', 'desc' ]
                        sort2Hash_directionText     : [ 'asc', 'desc' ], // default values
                        // if true, override saveSort widget sort, if used & stored sort is available
                        sort2Hash_overrideSaveSort  : true // default = false
                    }
                },
                pager : {
                    // target the pager markup - see the HTML block below
                    container: this.$T.find(".tablesorter-pager"),

                    // Must be initialized with a 'data' key
                    ajaxObject: {
                        data: {}
                    },

                    // Saves the current pager page size and number (requires storage widget)
                    savePages: true,

                    output: '{startRow} to {endRow} of {filteredRows} ({totalRows})',

                    // apply disabled classname (cssDisabled option) to the pager arrows when the rows
                    // are at either extreme is visible; default is true
                    updateArrows: true,

                    // starting page of the pager (zero based index)
                    page: 0,

                    // Number of visible rows - default is 10
                    size: 10,

                    // Reset pager to this page after filtering; set to desired page number (zero-based index),
                    // or false to not change page at filter start
                    pageReset: 0,

                    // if true, the table will remain the same height no matter how many records are displayed.
                    // The space is made up by an empty table row set to a height to compensate; default is false
                    fixedHeight: false,

                    // remove rows from the table to speed up the sort of large tables.
                    // setting this to false, only hides the non-visible rows; needed if you plan to
                    // add/remove rows with the pager enabled.
                    removeRows: false,

                    // If true, child rows will be counted towards the pager set size
                    countChildRows: false,

                    // css class names of pager arrows
                    cssNext        : '.next',  // next page arrow
                    cssPrev        : '.prev',  // previous page arrow
                    cssFirst       : '.first', // go to first page arrow
                    cssLast        : '.last',  // go to last page arrow
                    cssGoto        : '.gotoPage', // page select dropdown - select dropdown that set the "page" option

                    cssPageDisplay : '.pagedisplay', // location of where the "output" is displayed
                    cssPageSize    : '.pagesize', // page size selector - select dropdown that sets the "size" option

                    // class added to arrows when at the extremes; see the "updateArrows" option
                    // (i.e. prev/first arrows are "disabled" when on the first page)
                    cssDisabled    : 'disabled', // Note there is no period "." in front of this class name
                    cssErrorRow    : 'tablesorter-errorRow' // error information row
                }
            },
            options
        );

        this._init( target, options );

        return this;
    }

    /**
     * Get state variables for this table, as required by the AJAX data source: sorts, filters, size, page
     */
    Plugin.prototype.getTableStateVars = function ( table ) {
        var base = this;

        // Get sort column and order
        var sortOrders = {
            "0" : "asc",
            "1" : "desc"
        };

        // Set sorts in URL.  Assumes each th has a data-column-name attribute that corresponds to the name in the API
        var sortList = table.config.sortList;
        var sorts = {};
        for (i = 0; i < sortList.length; i++) {
            var columnIndex = sortList[i][0];
            var columnDirection = sortOrders[sortList[i][1]];   // Converts to 'asc' or 'desc'
            if (sortList[i]) {
                var columnName = $(table.config.headerList[columnIndex]).data("column-name");
                sorts[columnName] = columnDirection;
            }
        }

        // Set filters in URL.  Assumes each th has a data-column-name attribute that corresponds to the name in the API
        var filterList = $.tablesorter.getFilters(table);
        var filters = {};
        for (i = 0; i < filterList.length; i++) {
            if (filterList[i]) {
                var columnName = $(table.config.headerList[i]).data("column-name");
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
    }

    /** #### INITIALISER #### */
    Plugin.prototype._init = function ( target, options )
    {
        var base = this;
        var $el = $(target);

        base.options.pager.ajaxUrl = base.options.dataUrl;

        // Generate the URL for the AJAX request, with the relevant parameters
        base.options.pager.customAjaxUrl = function ( table, url ) {
            return base._generateUrl(base, table, url);
        };

        // Callback to process the response from the AJAX request
        base.options.pager.ajaxProcessing = function ( data ) {
            return base._processAjax(base, data);
        };

        // Set up tablesorter and pager
        base.ts = $el.find(".tablesorter").tablesorter(base.options.tablesorter);
        base.ts.tablesorterPager(base.options.pager);

        // Link CSV download button
        $el.find(".js-download-table").on("click", function () {
            var tableState = base.getTableStateVars(base.ts[0]);
            tableState['format'] = "csv";
            delete tableState['page'];
            delete tableState['size'];

            // Merge in any additional request parameters
            $.extend(tableState, base.options.addParams);

            // Causes download to begin
            window.location = base.options.dataUrl + $.param( tableState );
        });

        base.ts.on("pagerComplete.ufTable", function () {
            $el.trigger("pagerComplete");
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
    }

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
            size = data['rows'].length;

            // Build Handlebars templates based on column-template attribute in each column header
            var columns = ts.config.headerList;
            var templates = [];
            for (i = 0; i < columns.length; i++) {
                var columnName = $(columns[i]).data("column-template");
                templates.push(Handlebars.compile($(columnName).html()));
            }

            // Render table cells via Handlebars
            for (row = 0; row < size; row++) {
                rows += '<tr>';
                var cellData = {
                    "row"  : data['rows'][ row ],       // It is safe to use the data from the API because Handlebars escapes HTML
                    "site" : site
                };

                for (i = 0; i < columns.length; i++) {
                    rows += templates[i](cellData);
                }

                rows += '</tr>';
            }

            json.total = data['count'];  // Get total rows without pagination
            json.filteredRows = data['count_filtered']; // no filtering
            json.rows = $(rows);
        } else {
            json.total = 0;
            json.filteredRows = 0;
            json.rows = "";
        }

        return json;
    }

    /**
     * Private method used to encode the current table state variables into a URL hash.
     */
    Plugin.prototype._encodeHash = function ( config, tableId, component, value, rawValue ) {
        var wo = config.widgetOptions;
        if ( component === 'filter' ) {
            // rawValue is an array of filter values, numerically indexed
            var encodedFilters = "";
            var len = rawValue.length;
            for ( index = 0; index < len; index++ ) {
                if (rawValue[index]) {
                    var columnName = $(config.$headerIndexed[ index ][0]).attr(wo.sort2Hash_headerTextAttr);
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
    }

    /**
     * Private method used to decode the current table state variables from the URL hash.
     */
    Plugin.prototype._decodeHash = function ( config, tableId, component ) {
        var wo = config.widgetOptions;
        var result;
        // Convert hash into JSON object
        var urlObject = $.String.deparam(window.location.hash);
        delete urlObject[wo.sort2Hash_hash];  // Remove hash character
        if ( component === 'filter' ) {
            var decodedFilters = [];
            // Extract filter names and values for the specified table
            var filters = urlObject['filter'] ? urlObject['filter'] : [];
            if (filters[tableId]) {
                var filters = filters[tableId];
                // Build a numerically indexed array of filter values
                var len = config.$headerIndexed.length;
                for ( index = 0; index < len; index++ ) {
                    var column_name = $(config.$headerIndexed[ index ][0]).attr(wo.sort2Hash_headerTextAttr);
                    if (filters[column_name]) {
                        decodedFilters.push(filters[column_name]);
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
    }

    /**
     * Private method used to clean up URL hash.
     */
    Plugin.prototype._cleanHash = function ( config, tableId, component, hash ) {
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
    }

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
