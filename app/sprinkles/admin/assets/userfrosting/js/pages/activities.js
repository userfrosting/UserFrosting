/**
 * Page-specific Javascript file.  Should generally be included as a separate asset bundle in your page template.
 * example: {{ assets.js('js/pages/sign-in-or-register') | raw }}
 *
 * This script depends on uf-table.js, moment.js, handlebars-helpers.js
 *
 * Target page: /activities
 */

$(document).ready(function() {
    // Set up table of activities
    $("#widget-activities").ufTable({
        dataUrl: site.uri.public + "/api/activities",
        useLoadingTransition: site.uf_table.use_loading_transition
    });
});
