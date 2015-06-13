(function( $ ) {
    $.fn.flashAlerts = function() {
        //var deferred = $.Deferred();
     
        var field = $(this);
        console.log("Displaying alerts");
        var url = site['uri']['public'] + "/alerts";
        return $.getJSON( url, {})
        .then(function( data ) {        // Pass the deferral back
            // Display alerts
            var alertHTML = "";
            if (data) {
                jQuery.each(data, function(alert_idx, alert_message) {
                    if (alert_message['type'] == "success"){
                        alertHTML += "<div class='alert alert-success'>" + alert_message['message'] + "</div>";
                    } else if (alert_message['type'] == "warning"){
                        alertHTML += "<div class='alert alert-warning'>" + alert_message['message'] + "</div>";
                    } else 	if (alert_message['type'] == "info"){
                        alertHTML += "<div class='alert alert-info'>" + alert_message['message'] + "</div>";
                    } else if (alert_message['type'] == "danger"){
                        alertHTML += "<div class='alert alert-danger'>" + alert_message['message'] + "</div>";
                    }
                });
            }
            field.html(alertHTML);
            $("html, body").animate({ scrollTop: 0 }, "fast");		// Scroll back to top of page
            
            return data;
            //deferred.resolve();
        });
        //return deferred.promise();
    };
}( jQuery ));

// define tablesorter pager options
var pagerOptions = {
  // target the pager markup - see the HTML block below
  container: $('.pager'),
  // output string - default is '{page}/{totalPages}'; possible variables: {page}, {totalPages}, {startRow}, {endRow} and {totalRows}
  output: '{startRow} - {endRow} / {filteredRows} ({totalRows})',
  // if true, the table will remain the same height no matter how many records are displayed. The space is made up by an empty
  // table row set to a height to compensate; default is false
  fixedHeight: true,
  // remove rows from the table to speed up the sort of large tables.
  // setting this to false, only hides the non-visible rows; needed if you plan to add/remove rows with the pager enabled.
  removeRows: false,
  // go to page selector - select dropdown that sets the current page
  cssGoto: '.gotoPage'
};

// Initialize tablesorters
if (jQuery().tablesorter){
    $('.tablesorter-bootstrap').tablesorter({
        debug: false,
        theme: 'bootstrap',
        widthFixed: true,
        widgets: ['filter']
    }).tablesorterPager(pagerOptions);
} else {
    console.log("The tablesorter plugin has not been added.");
}

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