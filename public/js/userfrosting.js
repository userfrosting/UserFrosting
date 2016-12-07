//does the fetching and printing of the server-side stored alerts
//every object contained content or alert(s) removed
(function( $ ) {
    $.fn.flashAlerts = function() {
        var field = $(this);
        var url = site['uri']['public'] + "/alerts";
        return $.getJSON( url, {})
        .then(function( data ) {        // Pass the deferral back
            // Display alerts
            var alertHTML = "";
            if (data) {
                jQuery.each(data, function(alert_idx, alert_message) {
                    alertHTML += getAlertHtml(alert_message['type'], alert_message['message']);
                });
            }
            field.html(alertHTML);
            $("html, body").animate({ scrollTop: 0 }, "fast");		// Scroll back to top of page
            
            return data;
        });
    };
        
}( jQuery ));

//does the creation of client-side = "pushed"-alerts 
//any existing alert preserved 
(function( $ ) {
    $.fn.pushAlert = function( alert_type, alert_message ){
        var field = $(this);
        if (alert_type && alert_message){
            var newAlertHTML = "";            
            var oldAlertHtml = field.html();
            if (typeof oldAlertHtml !== 'undefined' && oldAlertHtml !== null) 
                newAlertHTML += oldAlertHtml;
           
            newAlertHTML += getAlertHtml(alert_type, alert_message);
            field.html(newAlertHTML);
             $("html, body").animate({ scrollTop: 0 }, "fast");		// Scroll back to top of page
        }
        
        return field;
    };
    
}( jQuery ));

//every object contained content or alert(s) removed
(function( $ ) {
    $.fn.clearAlerts = function(){
        return this.html("");
    }; 
}( jQuery ));
 
//helper function to generate the alert html tags
function getAlertHtml(alert_type, alert_message){
    if (alert_type == "success"){
        return "<div class='alert alert-success'>" + alert_message + "</div>";
    } else if (alert_type == "warning"){
        return "<div class='alert alert-warning'>" + alert_message + "</div>";
    } else 	if (alert_type == "info"){
        return "<div class='alert alert-info'>" + alert_message + "</div>";
    } else if (alert_type == "danger"){
        return "<div class='alert alert-danger'>" +  alert_message + "</div>";
    }
}

/**  format an ISO date using Moment.js
 *  http://momentjs.com/
 *  moment syntax example: moment(Date("2011-07-18T15:50:52")).format("MMMM YYYY")
 *  usage: {{dateFormat creation_date format="MMMM YYYY"}}
 */
Handlebars.registerHelper('dateFormat', function(context, block) {
  if (window.moment) {
    var f = block.hash.format || "MMM Do, YYYY";
    return moment(context).format(f);
  }else{
    return context;   //  moment plugin not available. return data as is.
  }
});

// Equality helper for Handlebars
// http://stackoverflow.com/questions/8853396/logical-operator-in-a-handlebars-js-if-conditional/21915381#21915381
Handlebars.registerHelper('ifCond', function(v1, v2, options) {
  if(v1 == v2) {
    return options.fn(this);
  }
  return options.inverse(this);
});

// Set jQuery.validate settings for bootstrap integration
jQuery.validator.setDefaults({
    highlight: function(element) {
        jQuery(element).closest('.form-group').addClass('has-error');
        jQuery(element).closest('.form-group').removeClass('has-success has-feedback');
        jQuery(element).closest('.form-group').find('.form-control-feedback').remove();
    },
    unhighlight: function(element) {
        jQuery(element).closest('.form-group').removeClass('has-error');
    },
    errorElement: 'span',
    errorClass: 'text-danger',
    errorPlacement: function(error, element) {
        if(element.parent('.input-group').length) {
            error.insertAfter(element.parent());
        } else {
            error.insertAfter(element);
        }
    },
    success: function(element) {
        jQuery(element).closest('.form-group').addClass('has-success has-feedback');
        jQuery(element).after('<i class="fa fa-check form-control-feedback" aria-hidden="true"></i>');
    }
});

// Process a UserFrosting form, displaying messages from the message stream and executing specified callbacks
function ufFormSubmit(formElement, validators, msgElement, successCallback, msgCallback, beforeSubmitCallback) {
    formElement.validate({
        rules:          validators['rules'],
        messages :      validators['messages'],
        submitHandler:  function (f, event) {
            // Execute any "before submit" callback
            if (typeof beforeSubmitCallback !== "undefined")
                beforeSubmitCallback();        
            var form = $(f);
            // Set "loading" text for submit button, if it exists, and disable button
            var submit_button = form.find("button[type=submit]");
            if (submit_button) {
                var submit_button_text = submit_button.html();
                submit_button.prop( "disabled", true );
                submit_button.html("<i class='fa fa-spinner fa-spin'></i>"); 
            }
            // Serialize and post to the backend script in ajax mode
            var serializedData = form.find('input, textarea, select').not(':checkbox').serialize();
            // Get unchecked checkbox values, set them to 0
            form.find('input[type=checkbox]:enabled').each(function() {
                if ($(this).is(':checked'))
                    serializedData += "&" + encodeURIComponent(this.name) + "=1";
                else
                    serializedData += "&" + encodeURIComponent(this.name) + "=0";
            });        
            
            // Append page CSRF token
            var csrf_token = $("meta[name=csrf_token]").attr("content");
            serializedData += "&csrf_token=" + encodeURIComponent(csrf_token);            
            
            var url = form.attr('action');
            $.ajax({  
              type: "POST",  
              url: url,  
              data: serializedData       
            })
            .done(successCallback)
            .fail(function(jqXHR) {
                if ((typeof site !== "undefined") && site['debug'] == true && jqXHR.status == "500") {
                    document.body.innerHTML = jqXHR.responseText;
                } else {
                    console.log("Error (" + jqXHR.status + "): " + jqXHR.responseText );
                    // Display errors on failure
                    msgElement.flashAlerts().done(function() {
                        // Do any additional callbacks here after displaying messages
                        if (typeof msgCallback !== "undefined")
                            msgCallback();
                    });
                }
            }).always(function () {
                // Restore button text and re-enable submit button
                if (submit_button) {
                    submit_button.prop( "disabled", false );
                    submit_button.html(submit_button_text);
                }
            });
        }
    });
}

function ufTable(table_id, ajaxSetupCallback, pagerCompleteCallback){
    // Set up server-side pagination
    // See http://jsfiddle.net/Mottie/uwZc2/
    // Also see https://mottie.github.io/tablesorter/docs/example-pager-ajax.html
    
    var pager_options = {                    
        // target the pager markup - see the HTML block below
        container: $('#' + table_id + '-pager'),
        
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
    };
    
    // Options for dynamically loading table data, if we are using server-side pagination
    if (typeof ajaxSetupCallback !== "undefined")
        var ajax_pager_options = ajaxSetupCallback();
        $.extend(pager_options, ajax_pager_options);
    
    $('#' + table_id).tablesorter({
        debug: false,
        theme: 'bootstrap',
        widthFixed: true,
        widgets: ['saveSort','sort2Hash','filter'],
        widgetOptions: {
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
            sort2Hash_encodeHash : function( config, tableId, component, value, rawValue ) {
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
            },            
            sort2Hash_decodeHash : function( config, tableId, component ) {
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
                            decodedFilters.push("");
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
            sort2Hash_cleanHash : function( config, tableId, component, hash ) {
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
            },           
            // direction text shown in the URL e.g. [ 'asc', 'desc' ]
            sort2Hash_directionText     : [ 'asc', 'desc' ], // default values
            // if true, override saveSort widget sort, if used & stored sort is available
            sort2Hash_overrideSaveSort  : true // default = false                  
        }
    })
    .tablesorterPager(pager_options);
    
    if (typeof pagerCompleteCallback !== "undefined") {
        $('#' + table_id).bind('pagerComplete', function(e, table) {
            pagerCompleteCallback();
        });
    }
}

/**
 * Get state variables for a tablesorter table: sort_field, sort_order, filters, size, page
 */
function getTableStateVars(table){
    // Get sort column and order                    
    var sortOrders = {
        "0" : "asc",
        "1" : "desc"
    };
    
    var sort_field_index = table.config.sortList[0][0];
    var sort_field = $(table.config.headerList[sort_field_index]).data("column-name");
    
    // Set filters in URL.  Assumes each th has a data-column-name attribute that corresponds to the name in the API
    var filterList = $.tablesorter.getFilters(table);
    var filters = {};
    for (i = 0; i < filterList.length; i++){
        if (filterList[i]) {
            var column_name = $(table.config.headerList[i]).data("column-name");
            filters[column_name] = filterList[i];
        }
    }                    
    
    var state = {
        size: table.config.pager.size,
        page: table.config.pager.page,
        sort_field: sort_field,
        sort_order: sortOrders[table.config.sortList[0][1]],
        filters: filters
    };
    return state;
}
	
/**
 * @add jQuery.String
 */
$.String = $.extend($.String || {}, { 
    /**
     * @function deparam
     * 
     * Takes a string of name value pairs and returns a Object literal that represents those params.
     * 
     * @param {String} params a string like <code>"foo=bar&person[age]=3"</code>
     * @return {Object} A JavaScript Object that represents the params:
     * 
     *     {
     *       foo: "bar",
     *       person: {
     *         age: "3"
     *       }
     *     }
     */
    deparam: function(params){
        var digitTest = /^\d+$/,
            keyBreaker = /([^\[\]]+)|(\[\])/g,
            plus = /\+/g,
            paramTest = /([^?#]*)(#.*)?$/;
    
        if(! params || ! paramTest.test(params) ) {
            return {};
        } 
       
    
        var data = {},
            pairs = params.split('&'),
            current;
            
        for(var i=0; i < pairs.length; i++){
            current = data;
            var pair = pairs[i].split('=');
            
            // if we find foo=1+1=2
            if(pair.length != 2) { 
                pair = [pair[0], pair.slice(1).join("=")]
            }
              
    var key = decodeURIComponent(pair[0].replace(plus, " ")), 
      value = decodeURIComponent(pair[1].replace(plus, " ")),
                parts = key.match(keyBreaker);
    
            for ( var j = 0; j < parts.length - 1; j++ ) {
                var part = parts[j];
                if (!current[part] ) {
                    // if what we are pointing to looks like an array
                    current[part] = digitTest.test(parts[j+1]) || parts[j+1] == "[]" ? [] : {}
                }
                current = current[part];
            }
            lastPart = parts[parts.length - 1];
            if(lastPart == "[]"){
                current.push(value)
            }else{
                current[lastPart] = value;
            }
        }
        return data;
    }
});	

// Initialize bootstrap switches, if enabled
if (jQuery().bootstrapSwitch){
    $('.bootstrapswitch').bootstrapSwitch();
} else {
    console.log("The bootstrap-switch plugin has not been added.");
}

// Initialize select2 dropdowns, if enabled
if (jQuery().select2){
    $('.select2').select2();
} else {
    console.log("The select2 plugin has not been added.");
}
