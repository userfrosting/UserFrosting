/**
 * Page-specific Javascript file.  Should generally be included as a separate asset bundle in your page template.
 * example: {{ assets.js('js/pages/sign-in-or-register') | raw }}
 *
 * This script depends on validation rules specified in pages/partials/page.js.twig.
 *
 * Target page: account/register
 */
$(document).ready(function() {
    // TOS modal
    $(this).find('.js-show-tos').click(function(e) {
        e.preventDefault();

        $("body").ufModal({
            sourceUrl: site.uri.public + "/modals/account/tos",
            msgTarget: $("#alerts-page")
        });
    });

    // Auto-generate username when name is filled in
    var autoGenerate = true;
    $("#register").find('input[name=first_name], input[name=last_name]').on('input change', function() {
        if (!autoGenerate) {
            return;
        }

        var form = $("#register");

        var firstName = form.find('input[name=first_name]').val().trim();
        var lastName = form.find('input[name=last_name]').val().trim();

        if (!firstName && !lastName) {
            return;
        }

        var userName = getSlug(firstName + ' ' + lastName, {
            separator: '.'
        });
        // Set slug
        form.find('input[name=user_name]').val(userName);
    });

    // Autovalidate username field on a delay
    var timer;
    $("#register").find('input[name=first_name], input[name=last_name], input[name=user_name]').on('input change', function() {
        clearTimeout(timer); // Clear the timer so we don't end up with dupes.
        timer = setTimeout(function() { // assign timer a new timeout 
            $("#register").find('input[name=user_name]').valid();
        }, 500);
    });

    // Enable/disable username suggestions in registration page
    $("#register").find('#form-register-username-suggest').on('click', function(e) {
        e.preventDefault();
        var form = $("#register");
        $.getJSON(site.uri.public + '/account/suggest-username')
        .done(function (data) {
            // Set suggestion
            form.find('input[name=user_name]').val(data.user_name);
        });
    });

    // Turn off autogenerate when someone enters stuff manually in user_name
    $("#register").find('input[name=user_name]').on('input', function() {
        autoGenerate = false;
    });

    // Add remote rule for checking usernames on the fly
    var registrationValidators = $.extend(
        true,               // deep extend
        page.validators.register,
        {
            rules: {
                user_name: {
                    remote: {
                        url: site.uri.public + '/account/check-username',
                        dataType: 'text'
                    }
                }
            }
        }
    );

    // Handles form submission
    $("#register").ufForm({
        validator: registrationValidators,
        msgTarget: $("#alerts-page"),
        keyupDelay: 500
    }).on("submitSuccess.ufForm", function() {
        // Reload to clear form and show alerts
        window.location.reload();
    }).on("submitError.ufForm", function() {
        // Reload captcha
        $("#captcha").captcha();
    });
});
