/**
 * Page-specific Javascript file.  Should generally be included as a separate asset bundle in your page template.
 * example: {{ assets.js('js/pages/sign-in-or-register') | raw }}
 *
 * This script depends on validation rules specified in pages/partials/page.js.twig.
 *
 * Target pages: account/set-password, account/reset-password
 */
$(document).ready(function() {

    $("#set-or-reset-password").ufForm({
        validator: page.validators.set_password,
        msgTarget: $("#alerts-page")
    }).on("submitSuccess.ufForm", function() {
        // Forward to home page on success
        // TODO: forward to landing/last page
        window.location.replace(site.uri.public + "/account/sign-in");
    });
});
