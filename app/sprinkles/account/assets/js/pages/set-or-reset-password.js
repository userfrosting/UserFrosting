/**
 * Page-specific Javascript file.  Should generally be included as a separate asset bundle in your page template.
 * example: {{ assets.js('js/pages/sign-in-or-register') | raw }}
 *
 * This script depends on validation rules specified in components/validators.js.twig.
 *
 * Target pages: account/set-password, account/reset-password
 */
$(document).ready(function() {

    /*
        Fullscreen background
    */
    $.backstretch(site.uri.images.background);
    
    // TODO: Process form 
    $("#set-or-reset-password").ufForm({
        validators: page.validators.set_password,
        msgTarget: $("#userfrosting-alerts")
    }).on("submitSuccess.ufForm", function() {
        // Forward to home page on success
        window.location.replace(site.uri.public);
    }); 
});
