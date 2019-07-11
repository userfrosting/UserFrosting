/**
 * Page-specific Javascript file.  Should generally be included as a separate asset bundle in your page template.
 * example: {{ assets.js('js/pages/sign-in-or-register') | raw }}
 *
 * This script depends on widgets/users.js, uf-table.js, moment.js, handlebars-helpers.js
 *
 * Target page: /users
 */

$(document).ready(function() {
    // Set up table of users
    $("#widget-users").ufTable({
        dataUrl: site.uri.public + "/api/users",
        useLoadingTransition: site.uf_table.use_loading_transition
    });

    // Bind creation button
    bindUserCreationButton($("#widget-users"));

    // Bind table buttons
    $("#widget-users").on("pagerComplete.ufTable", function () {
        bindUserButtons($(this));
    });
});
