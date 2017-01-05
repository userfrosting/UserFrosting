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
        // TODO: set up any widgets inside the modal
        $(".js-form-group").find("select[name='group_id']").select2();

        /*
        // Set icon when changed
        $('#' + box_id + ' input[name=icon]').on('change', function(){
            $(this).prev(".icon-preview").find("i").removeClass().addClass($(this).val());
        });
        */
        
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

var initGroupTable = function () {
    // Link create button
    $(this).find('.js-group-create').click(function() {
        $("body").ufModal({
            sourceUrl: site.uri.public + "/modals/groups/create",
            msgTarget: $("#alerts-page")
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
            msgTarget: $("#alerts-page")
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
            msgTarget: $("#alerts-page")
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
};
