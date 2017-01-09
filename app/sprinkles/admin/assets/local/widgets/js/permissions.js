/**
 * Permissions widget.  Sets up dropdowns, modals, etc for a table of permissions.
 */
 
// TODO: move these to a common JS file for form widgets
$.fn.select2.defaults.set( "theme", "bootstrap" );

/**
 * Set up the form in a modal after being successfully attached to the body.
 */
function attachPermissionForm() {
    $("body").on('renderSuccess.ufModal', function (data) {
        var modal = $(this).ufModal('getModal');
        var form = modal.find('.js-form');

        // Set up the form for submission
        form.ufForm({
            validators: page.validators
        }).on("submitSuccess.ufForm", function() {
            // Reload page on success
            window.location.reload();
        });
    });
}

/**
 * Link permission action buttons, for example in a table or on a specific permission's page.
 */
function bindPermissionButtons(el) {
    /**
     * Link row buttons after table is loaded.
     */

    /**
     * Buttons that launch a modal dialog
     */
    // Edit permission details button
    el.find('.js-permission-edit').click(function() {
        $("body").ufModal({
            sourceUrl: site.uri.public + "/modals/permissions/edit",
            ajaxParams: {
                id: $(this).data('id')
            },
            msgTarget: $("#alerts-page")
        });

        attachPermissionForm();
    });

    // Delete permission button
    el.find('.js-permission-delete').click(function() {
        $("body").ufModal({
            sourceUrl: site.uri.public + "/modals/permissions/confirm-delete",
            ajaxParams: {
                id: $(this).data('id')
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
}

var initPermissionTable = function () {
    // Link create button
    $(this).find('.js-permission-create').click(function() {
        $("body").ufModal({
            sourceUrl: site.uri.public + "/modals/permissions/create",
            msgTarget: $("#alerts-page")
        });

        attachPermissionForm();
    });

    bindPermissionButtons($(this));
};
