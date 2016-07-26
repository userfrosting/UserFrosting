/**
 * Page-specific Javascript file.  Should generally be included as a separate asset bundle in your page template.
 * example: {{ assets.js('js/pages/sign-in-or-register') | raw }}
 *
 * This script depends on validation rules specified in components/validators.js.twig.
 *
 * Target page: account/forgot-password
 */
$(document).ready(function() {

    /*
        Fullscreen background
    */
    $.backstretch(site.uri.images.background);
    
    // TODO: Process form 
    $("#request-password-reset").ufForm({
        validators: page.validators.forgot_password,
        msgTarget: $("#userfrosting-alerts")
    }).on("submitSuccess", function() {
        // Forward to login page on success
        window.location.replace(site.uri.public + "/account/login");
    }); 
});
