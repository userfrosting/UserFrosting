/**
 * Page-specific Javascript file.  Should generally be included as a separate asset bundle in your page template.
 * example: {{ assets.js('js/pages/sign-in-or-register') | raw }}
 *
 * This script depends on validation rules specified in components/validators.js.twig.
 *
 * Target page: account/settings
 */
$(document).ready(function() {    
    // TODO: Process form 
    $("#account-settings").ufForm({
        validators: page.validators.forgot_password,
        msgTarget: $("#userfrosting-alerts")
    }).on("submitSuccess.ufForm", function() {
        // Reload the page on success
        window.location.reload();
    }); 
});
