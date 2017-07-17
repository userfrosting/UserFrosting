/**
 * Page-specific Javascript file.  Should generally be included as a separate asset bundle in your page template.
 * example: {{ assets.js('js/pages/sign-in-or-register') | raw }}
 *
 * This script depends on widgets/groups.js, uf-table.js, moment.js, handlebars-helpers.js
 *
 * Target page: /groups
 */

$(document).ready(function() {

    $("#widget-groups").ufTable({
        dataUrl: site.uri.public + "/api/groups",
        useLoadingTransition: site.uf_table.use_loading_transition
    });

    // Bind creation button
    bindGroupCreationButton($("#widget-groups"));

    // Bind table buttons
    $("#widget-groups").on("pagerComplete.ufTable", function () {
        bindGroupButtons($(this));
    });
});
