/**
 * Page-specific Javascript file.  Should generally be included as a separate asset bundle in your page template.
 * example: {{ assets.js('js/pages/sign-in-or-register') | raw }}
 *
 * This script depends on uf-table.js, moment.js, handlebars-helpers.js
 *
 * Target page: /users/u/{user_name}
 */

$(document).ready(function() {
    // Control buttons
    bindUserButtons($("#view-user"), { delete_redirect: page.delete_redirect });

    // Table of activities
    $("#widget-user-activities").ufTable({
        dataUrl: site.uri.public + '/api/users/u/' + page.user_name + '/activities',
        useLoadingTransition: site.uf_table.use_loading_transition
    });

    // Table of permissions
    $("#widget-permissions").ufTable({
        dataUrl: site.uri.public + '/api/users/u/' + page.user_name + '/permissions',
        useLoadingTransition: site.uf_table.use_loading_transition
    });
});
