/**
 * Page-specific Javascript file.  Should generally be included as a separate asset bundle in your page template.
 * example: {{ assets.js('js/pages/sign-in-or-register') | raw }}
 *
 * This script depends on uf-table.js, moment.js, handlebars-helpers.js
 *
 * Target page: /groups/g/{slug}
 */

$(document).ready(function() {
    // Control buttons
    bindGroupButtons($("#view-group"), { delete_redirect: page.delete_redirect });

    // Table of users in this group
    $("#widget-group-users").ufTable({
        dataUrl: site.uri.public + '/api/groups/g/' + page.group_slug + '/users',
        useLoadingTransition: site.uf_table.use_loading_transition
    });

    // Bind user table buttons
    $("#widget-group-users").on("pagerComplete.ufTable", function () {
        bindUserButtons($(this));
    });

$('.icp').iconpicker();

});
