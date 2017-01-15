/**
 * Groups widget.  Sets up dropdowns, modals, etc for a table of groups.
 */
 
// TODO: move these to a common JS file for form widgets
$.fn.select2.defaults.set( "theme", "bootstrap" );

/**
 * Set up the form in a modal after being successfully attached to the body.
 */
function attachGroupForm() {
    $("body").on('renderSuccess.ufModal', function (data) {
        var modal = $(this).ufModal('getModal');
        var form = modal.find('.js-form');

        /**
         * Set up modal widgets
         */
         
        // Auto-generate slug
        form.find('input[name=name]').on('input change', function() {
            var manualSlug = form.find('#form-group-slug-override').prop('checked');
            if (!manualSlug) {
                var slug = getSlug($(this).val());
                form.find('input[name=slug]').val(slug);
            }
        });

        form.find('#form-group-slug-override').on('change', function() {
            if ($(this).prop('checked')) {
                form.find('input[name=slug]').prop('readonly', false);
            } else {
                form.find('input[name=slug]').prop('readonly', true);
                form.find('input[name=name]').trigger('change');
            }
        });

        // Set icon when changed
        form.find('input[name=icon]').on('input change', function() {
            $(this).prev(".icon-preview").find("i").removeClass().addClass($(this).val());
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
 * Link group action buttons, for example in a table or on a specific group's page.
 */
function bindGroupButtons(el) {
    /**
     * Link row buttons after table is loaded.
     */

    /**
     * Buttons that launch a modal dialog
     */
    // Edit group details button
    el.find('.js-group-edit').click(function() {
        $("body").ufModal({
            sourceUrl: site.uri.public + "/modals/groups/edit",
            ajaxParams: {
                slug: $(this).data('slug')
            },
            msgTarget: $("#alerts-page")
        });

        attachGroupForm();
    });

    // Delete group button
    el.find('.js-group-delete').click(function() {
        $("body").ufModal({
            sourceUrl: site.uri.public + "/modals/groups/confirm-delete",
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

function bindGroupCreationButton(el) {
    // Link create button
    el.find('.js-group-create').click(function() {
        $("body").ufModal({
            sourceUrl: site.uri.public + "/modals/groups/create",
            msgTarget: $("#alerts-page")
        });

        attachGroupForm();
    });
};
