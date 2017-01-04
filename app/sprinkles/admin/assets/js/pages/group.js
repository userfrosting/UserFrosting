/**
 * Page-specific Javascript file.  Should generally be included as a separate asset bundle in your page template.
 * example: {{ assets.js('js/pages/sign-in-or-register') | raw }}
 *
 * This script depends on uf-table.js, moment.js, handlebars-helpers.js
 *
 * Target page: /groups/g/{slug}
 */

$(document).ready(function() {
    // Render any alerts
    $("#alerts-groups").ufAlerts();
    $("#alerts-groups").ufAlerts('fetch').ufAlerts('render');

    $("#widget-group-users").ufTable({
        dataUrl: site.uri.public + '/api/groups/g/' + page.group_slug + '/users',
        DEBUG: false
    });
});
