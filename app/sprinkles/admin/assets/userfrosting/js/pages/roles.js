/**
 * Page-specific Javascript file.  Should generally be included as a separate asset bundle in your page template.
 * example: {{ assets.js('js/pages/sign-in-or-register') | raw }}
 *
 * This script depends on widgets/roles.js, uf-table.js, moment.js, handlebars-helpers.js
 *
 * Target page: /roles
 */

$(document).ready(function() {
    // Set up table of roles
    $("#widget-roles").ufTable({
        dataUrl: site.uri.public + "/api/roles",
        useLoadingTransition: site.uf_table.use_loading_transition
    });

    // Bind creation button
    bindRoleCreationButton($("#widget-roles"));

    // Bind table buttons
    $("#widget-roles").on("pagerComplete.ufTable", function () {
        bindRoleButtons($(this));
    });
});
