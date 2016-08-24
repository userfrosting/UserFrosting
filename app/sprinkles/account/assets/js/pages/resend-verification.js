/**
 * Page-specific Javascript file.  Should generally be included as a separate asset bundle in your page template.
 * example: {{ assets.js('js/pages/sign-in-or-register') | raw }}
 *
 * This script depends on validation rules specified in components/validators.js.twig.
 *
 * Target page: account/resend-verification
 */
$(document).ready(function() {

    /*
        Fullscreen background
    */
    $.backstretch(site.uri.images.background);
    
    // TODO: Process form 
    $("#request-verification-email").ufForm({
        validators: page.validators.resend_verification,
        msgTarget: $("#userfrosting-alerts")
    }).on("submitSuccess.ufForm", function() {
        // Forward to login page on success
        window.location.replace(site.uri.public + "/account/login");
    }); 
});
