/**
 * Page-specific Javascript file.  Should generally be included as a separate asset bundle in your page template.
 * example: {{ assets.js('js/pages/sign-in-or-register') | raw }}
 *
 * This script depends on uf-table.js, moment.js, handlebars-helpers.js
 *
 * Target page: /users
 */

// TODO: move these to a common JS file for form widgets
$.fn.select2.defaults.set( "theme", "bootstrap" );

/**
 * Set up the form in a modal after being successfully attached to the body.
 */
function attachUserForm() {
    $("body").on('renderSuccess.ufModal', function (data) {
        // TODO: set up any widgets inside the modal
        $(".js-form-user").find("select[name='group_id']").select2();

        // Set up the form for submission
        $(".js-form-user").ufForm({
            validators: page.validators,
            msgTarget: $(".js-form-user-alerts")
        }).on("submitSuccess.ufForm", function() {
            // Reload page on success
            window.location.reload();
        });
    });
}

/**
 * Update user field(s)
 */
function updateUser(userName, data) {
	data = typeof data !== 'undefined' ? data : {};

    data[site.csrf.keys.name] = site.csrf.name;
    data[site.csrf.keys.value] = site.csrf.value;

    var url = site.uri.public + "/api/users/u/" + userName;

    return $.ajax({
        type: "POST",
        url: url,
        data: data
	});
}

$(document).ready(function() {
    // Render any alerts
    $("#alerts-users").ufAlerts();
    $("#alerts-users").ufAlerts('fetch').ufAlerts('render');

    $("#widget-users").ufTable({
        dataUrl: site.uri.public + "/api/users"
        /*
        addParams: {
            "group": "terran"
        }
        */
    });

    $("#widget-users").on("pagerComplete.ufTable", function () {

        // Link create button
        $(this).find('.js-user-create').click(function() {
            $("body").ufModal({
                sourceUrl: site.uri.public + "/modals/users/create",
                msgTarget: $("#alerts-users")
            });

            attachUserForm();
        });

        /**
         * Link row buttons after table is loaded.
         */

        /**
         * Buttons that launch a modal dialog
         */
        // Edit general user details button
        $(this).find('.js-user-edit').click(function() {
            $("body").ufModal({
                sourceUrl: site.uri.public + "/modals/users/edit",
                ajaxParams: {
                    user_name: $(this).data('user_name')
                },
                msgTarget: $("#alerts-users")
            });

            attachUserForm();
        });

        // Change user password button
        $(this).find('.js-user-password').click(function() {
            $("body").ufModal({
                sourceUrl: site.uri.public + "/modals/users/password",
                ajaxParams: {
                    user_name: $(this).data('user_name')
                },
                msgTarget: $("#alerts-users")
            });
            
            $("body").on('renderSuccess.ufModal', function (data) {
                var modal = $(this).ufModal('getModal');
            	// Enable/disable password fields when switch is toggled
                $(".controls-password").find("input[type='password']").prop('disabled', true);
                modal.find("input[name='change_password_mode']").click(function() {
                    var type = $(this).val();
                    if (type == "link") {
                        $(".controls-password").find("input[type='password']").prop('disabled', true);
                        modal.find("input[name='flag_password_reset']").prop('disabled', false);
                    } else {
                        $(".controls-password").find("input[type='password']").prop('disabled', false);
                        modal.find("input[name='flag_password_reset']").prop('disabled', true);
                    }
                });
            });
        });

        // Delete user button
        $(this).find('.js-user-delete').click(function() {
            $("body").ufModal({
                sourceUrl: site.uri.public + "/modals/users/confirm-delete",
                ajaxParams: {
                    user_name: $(this).data('user_name')
                },
                msgTarget: $("#alerts-users")
            });

            $("body").on('renderSuccess.ufModal', function (data) {
                var modal = $(this).ufModal('getModal');

                modal.find('.js-form-user-delete').ufForm({
                    msgTarget: $(".js-form-user-alerts")
                }).on("submitSuccess.ufForm", function() {
                    // Reload page on success
                    window.location.reload();
                });
            });
        });

        /**
         * Direct action buttons
         */
        $(this).find('.js-user-activate').click(function() {
            var btn = $(this);
            updateUser(btn.data('user_name'), {
                flag_verified: '1'
            })
            .always(function(response) {
                // Reload page after updating user details
                window.location.reload();
            });
        });

        $(this).find('.js-user-enable').click(function () {
            var btn = $(this);
            updateUser(btn.data('user_name'), {
                flag_enabled: '1'
            })
            .always(function(response) {
                // Reload page after updating user details
                window.location.reload();
            });
        });

        $(this).find('.js-user-disable').click(function () {
            var btn = $(this);
            updateUser(btn.data('user_name'), {
                flag_enabled: '0'
            })
            .always(function(response) {
                // Reload page after updating user details
                window.location.reload();
            });
        });
    });
});
