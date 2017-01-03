/**
 * Page-specific Javascript file.  Should generally be included as a separate asset bundle in your page template.
 * example: {{ assets.js('js/pages/sign-in-or-register') | raw }}
 *
 * This script depends on uf-table.js, moment.js, handlebars-helpers.js
 *
 * Target page: /groups
 */

// TODO: move these to a common JS file for form widgets
$.fn.select2.defaults.set( "theme", "bootstrap" );

/**
 * Set up the form in a modal after being successfully attached to the body.
 */
function attachGroupForm() {
    $("body").on('renderSuccess.ufModal', function (data) {
        // TODO: set up any widgets inside the modal
        $(".js-form-group").find("select[name='group_id']").select2();

        // Set up the form for submission
        $(".js-form-group").ufForm({
            validators: page.validators,
            msgTarget: $(".js-form-group-alerts")
        }).on("submitSuccess.ufForm", function() {
            // Reload page on success
            window.location.reload();
        });
    });
}

$(document).ready(function() {
    // Render any alerts
    $("#alerts-groups").ufAlerts();
    $("#alerts-groups").ufAlerts('fetch').ufAlerts('render');

    $("#widget-groups").ufTable({
        dataUrl: site.uri.public + "/api/groups"
    });

    $("#widget-groups").on("pagerComplete.ufTable", function () {

        // Link create button
        $(this).find('.js-group-create').click(function() {
            $("body").ufModal({
                sourceUrl: site.uri.public + "/modals/groups/create",
                msgTarget: $("#alerts-groups")
            });

            attachGroupForm();
        });

        /**
         * Link row buttons after table is loaded.
         */

        /**
         * Buttons that launch a modal dialog
         */
        // Edit group details button
        $(this).find('.js-group-edit').click(function() {
            $("body").ufModal({
                sourceUrl: site.uri.public + "/modals/groups/edit",
                ajaxParams: {
                    slug: $(this).data('slug')
                },
                msgTarget: $("#alerts-groups")
            });

            attachGroupForm();
        });

        // Delete group button
        $(this).find('.js-group-delete').click(function() {
            $("body").ufModal({
                sourceUrl: site.uri.public + "/modals/groups/confirm-delete",
                ajaxParams: {
                    slug: $(this).data('slug')
                },
                msgTarget: $("#alerts-groups")
            });

            $("body").on('renderSuccess.ufModal', function (data) {
                var modal = $(this).ufModal('getModal');

                modal.find('.js-form-group-delete').ufForm({
                    msgTarget: $(".js-form-group-alerts")
                }).on("submitSuccess.ufForm", function() {
                    // Reload page on success
                    window.location.reload();
                });
            });
        });
    });
});
