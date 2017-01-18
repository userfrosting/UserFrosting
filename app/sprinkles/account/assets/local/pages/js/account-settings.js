/**
 * Page-specific Javascript file.  Should generally be included as a separate asset bundle in your page template.
 * example: {{ assets.js('js/pages/sign-in-or-register') | raw }}
 *
 * This script depends on validation rules specified in components/page.js.twig.
 *
 * Target page: account/settings
 */
$(document).ready(function() {

    // Fetch and render any alerts
    // This is needed, for example, when we refresh the page after the page is updated
    $("#alerts-page").ufAlerts();
    $("#alerts-page").ufAlerts('fetch').ufAlerts('render');

    $("#account-settings").ufForm({
        validators: page.validators.account_settings,
        msgTarget: $("#alerts-page")
    }).on("submitSuccess.ufForm", function() {
        // Reload the page on success
        window.location.reload();
    });
});
