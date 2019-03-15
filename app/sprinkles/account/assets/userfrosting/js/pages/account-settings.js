/**
 * Page-specific Javascript file.  Should generally be included as a separate asset bundle in your page template.
 * example: {{ assets.js('js/pages/sign-in-or-register') | raw }}
 *
 * This script depends on validation rules specified in pages/partials/page.js.twig.
 *
 * Target page: account/settings
 */
$(document).ready(function() {

    // Apply select2 to locale field
    $('.js-select2').select2();

    $("#account-settings").ufForm({
        validator: page.validators.account_settings,
        msgTarget: $("#alerts-page")
    }).on("submitSuccess.ufForm", function() {
        // Reload the page on success
        window.location.reload();
    });

    $("#profile-settings").ufForm({
        validator: page.validators.profile_settings,
        msgTarget: $("#alerts-page")
    }).on("submitSuccess.ufForm", function() {
        // Reload the page on success
        window.location.reload();
    });
});
