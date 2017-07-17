/**
 * Page-specific Javascript file.  Should generally be included as a separate asset bundle in your page template.
 * example: {{ assets.js('js/pages/sign-in-or-register') | raw }}
 *
 * Target page: /dashboard
 */

$(document).ready(function() {
    $('.js-clear-cache').click(function() {
        $("body").ufModal({
            sourceUrl: site.uri.public + "/modals/dashboard/clear-cache",
            ajaxParams: {
                slug: $(this).data('slug')
            },
            msgTarget: $("#alerts-page")
        });

        $("body").on('renderSuccess.ufModal', function (data) {
            var modal = $(this).ufModal('getModal');
            var form = modal.find('.js-form');

            form.ufForm()
            .on("submitSuccess.ufForm", function() {
                // Reload page on success
                window.location.reload();
            });
        });
    });

    // Table of site activities
    $("#widget-activities").ufTable({
        dataUrl: site.uri.public + "/api/activities",
        useLoadingTransition: site.uf_table.use_loading_transition
    });

    // Table of users in current user's group
    $("#widget-group-users").ufTable({
        dataUrl: site.uri.public + "/api/groups/g/" + page.group_slug + "/users",
        useLoadingTransition: site.uf_table.use_loading_transition
    });

    // Bind user creation button
    bindUserCreationButton($("#widget-group-users"));

    // Bind user table buttons
    $("#widget-group-users").on("pagerComplete.ufTable", function () {
        bindUserButtons($(this));
    });
});
