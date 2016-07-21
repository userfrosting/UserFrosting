(function( $ ) {
    $.fn.flashAlerts = function() {
        var field = $(this);
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
        });
    };   
    
}( jQuery ));