/**
 * Page-specific Javascript file.  Should generally be included as a separate asset bundle in your page template.
 * example: {{ assets.js('js/pages/sign-in-or-register') | raw }}
 *
 * This script depends on uf-table.js, moment.js, handlebars-helpers.js
 *
 * Target page: /activities
 */

$(document).ready(function() {
    // Render any alerts
    $("#alerts-page").ufAlerts();
    $("#alerts-page").ufAlerts('fetch').ufAlerts('render');

    $("#widget-activities").ufTable({
        dataUrl: site.uri.public + "/api/activities"
    });
});
