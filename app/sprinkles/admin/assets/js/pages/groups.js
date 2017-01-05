/**
 * Page-specific Javascript file.  Should generally be included as a separate asset bundle in your page template.
 * example: {{ assets.js('js/pages/sign-in-or-register') | raw }}
 *
 * This script depends on widgets/groups.js, uf-table.js, moment.js, handlebars-helpers.js
 *
 * Target page: /groups
 */

$(document).ready(function() {
    // Render any alerts
    $("#alerts-page").ufAlerts();
    $("#alerts-page").ufAlerts('fetch').ufAlerts('render');

    $("#widget-groups").ufTable({
        dataUrl: site.uri.public + "/api/groups"
    });

    $("#widget-groups").on("pagerComplete.ufTable", initGroupTable);
});
