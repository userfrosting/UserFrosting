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


//  format an ISO date using Moment.js
//  http://momentjs.com/
//  moment syntax example: moment(Date("2011-07-18T15:50:52")).format("MMMM YYYY")
//  usage: {{dateFormat creation_date format="MMMM YYYY"}}
Handlebars.registerHelper('dateFormat', function(context, block) {
  if (window.moment) {
    var f = block.hash.format || "MMM Do, YYYY";
    return moment(context).format(f);
  }else{
    return context;   //  moment plugin not available. return data as is.
  };
});

// Equality helper for Handlebars
// http://stackoverflow.com/questions/8853396/logical-operator-in-a-handlebars-js-if-conditional/21915381#21915381
Handlebars.registerHelper('ifCond', function(v1, v2, options) {
  if(v1 === v2) {
    return options.fn(this);
  }
  return options.inverse(this);
});

// Process a UserFrosting form, displaying messages from the message stream and executing specified callbacks
function ufFormSubmit(formElement, validators, msgElement, successCallback, msgCallback) {
    formElement.formValidation({
        framework: 'bootstrap',
        // Feedback icons
        icon: {
            valid: 'fa fa-check',
            invalid: 'fa fa-times',
            validating: 'fa fa-refresh'
        },
        fields: validators
    }).on('success.form.fv', function(e) {
        // Prevent double form submission
        e.preventDefault();

        // Get the form instance
        var form = $(e.target);
        
        // Serialize and post to the backend script in ajax mode
        var serializedData = form.find('input, textarea, select').not(':checkbox').serialize();
        // Get unchecked checkbox values, set them to 0
        form.find('input[type=checkbox]').each(function() {
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
                    // Re-enable submit button
                    form.data('formValidation').disableSubmitButtons(false);
                    // Do any additional callbacks here after displaying messages
                    if (typeof msgCallback !== "undefined")
                        msgCallback();
                });
            }
        });
    });
}

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