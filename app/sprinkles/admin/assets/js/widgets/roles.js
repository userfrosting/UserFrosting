/**
 * Roles widget.  Sets up dropdowns, modals, etc for a table of roles.
 */
 
// TODO: move these to a common JS file for form widgets
$.fn.select2.defaults.set( "theme", "bootstrap" );

/**
 * Set up the form in a modal after being successfully attached to the body.
 */
function attachRoleForm() {
    $("body").on('renderSuccess.ufModal', function (data) {
        var modal = $(this).ufModal('getModal');
        var form = modal.find('.js-form');

        /**
         * Set up modal widgets
         */

        // Auto-generate slug
        form.find('input[name=name]').on('input change', function() {
            var manualSlug = form.find('#form-role-slug-override').prop('checked');
            if (!manualSlug) {
                var slug = getSlug($(this).val());
                form.find('input[name=slug]').val(slug);
            }
        });

        form.find('#form-role-slug-override').on('change', function() {
            if ($(this).prop('checked')) {
                form.find('input[name=slug]').prop('readonly', false);
            } else {
                form.find('input[name=slug]').prop('readonly', true);
                form.find('input[name=name]').trigger('change');
            }
        });

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
 * Link role action buttons, for example in a table or on a specific role's page.
 */
function bindRoleButtons(el) {
    /**
     * Link row buttons after table is loaded.
     */

    /**
     * Buttons that launch a modal dialog
     */
    // Edit role details button
    el.find('.js-role-edit').click(function() {
        $("body").ufModal({
            sourceUrl: site.uri.public + "/modals/roles/edit",
            ajaxParams: {
                slug: $(this).data('slug')
            },
            msgTarget: $("#alerts-page")
        });

        attachRoleForm();
    });

    // Delete role button
    el.find('.js-role-delete').click(function() {
        $("body").ufModal({
            sourceUrl: site.uri.public + "/modals/roles/confirm-delete",
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
}

var initRoleTable = function () {
    // Link create button
    $(this).find('.js-role-create').click(function() {
        $("body").ufModal({
            sourceUrl: site.uri.public + "/modals/roles/create",
            msgTarget: $("#alerts-page")
        });

        attachRoleForm();
    });

    bindRoleButtons($(this));
};
