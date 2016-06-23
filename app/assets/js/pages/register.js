/**
 * Page-specific Javascript file.  Should generally be included as a separate asset bundle in your page template.
 * example: {{ assets.js('js/pages/register') | raw }}
 *
 * This script depends on validation rules specified in components/validators.js.twig.
 *
 * Target page: account/register
 */
$(document).ready(function() {           
    // Process form 
    $("#register").ufForm({
        validators: page.validators,
        msgTarget: $("#userfrosting-alerts")
    }).on("submitSuccess", function() {
        // Forward to login page on success
        window.location.replace(site.uri.public + "/account/login");
    }).on("submitError", function() {
        // Reload captcha
        //$("#captcha").captcha();
    });
});
