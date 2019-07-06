/**
 * Page-specific Javascript file.  Should generally be included as a separate asset bundle in your page template.
 * example: {{ assets.js('js/pages/sign-in-or-register') | raw }}
 *
 * This script depends on uf-table.js, moment.js, handlebars-helpers.js
 *
 * Target page: /roles/r/{slug}
 */

$(document).ready(function() {
    // Control buttons
    bindRoleButtons($("#view-role"), { delete_redirect: page.delete_redirect });

    $("#widget-role-permissions").ufTable({
        dataUrl: site.uri.public + '/api/roles/r/' + page.role_slug + '/permissions',
        useLoadingTransition: site.uf_table.use_loading_transition
    });

    $("#widget-role-users").ufTable({
        dataUrl: site.uri.public + '/api/roles/r/' + page.role_slug + '/users',
        useLoadingTransition: site.uf_table.use_loading_transition
    });

    // Bind table buttons
    $("#widget-role-users").on("pagerComplete.ufTable", function () {
        bindUserButtons($(this));
    });
});
