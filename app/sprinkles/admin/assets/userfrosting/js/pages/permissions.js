/**
 * Page-specific Javascript file.  Should generally be included as a separate asset bundle in your page template.
 * example: {{ assets.js('js/pages/sign-in-or-register') | raw }}
 *
 * This script depends on widgets/permissions.js, uf-table.js, moment.js, handlebars-helpers.js
 *
 * Target page: /permissions
 */

$(document).ready(function() {
    // Set up table of permissions
    $("#widget-permissions").ufTable({
        dataUrl: site.uri.public + "/api/permissions",
        useLoadingTransition: site.uf_table.use_loading_transition
    });
});
