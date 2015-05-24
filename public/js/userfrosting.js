(function( $ ) {
    $.fn.flashAlerts = function() {
        //var deferred = $.Deferred();
     
        var field = $(this);
        console.log("Displaying alerts");
        var url = site.uri.public + "/alerts";
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

// Initialize bootstrap switches, if enabled
if (jQuery().bootstrapSwitch){
    $('.bootstrapswitch').bootstrapSwitch();
} else {
    console.error("The bootstrap-switch plugin has not been added.");
}

// Initialize select2 dropdowns, if enabled
if (jQuery().select2){
    $('.select2').select2();
} else {
    console.error("The select2 plugin has not been added.");
}