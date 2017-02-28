/**
 * Page-specific Javascript file.  Should generally be included as a separate asset bundle in your page template.
 * example: {{ assets.js('js/pages/sign-in-or-register') | raw }}
 *
 * This script depends on validation rules specified in components/page.js.twig.
 *
 * Target page: account/sign-in-or-register
 */
$(document).ready(function() {

    // Fetch and render any alerts on the login panel
    // This is needed, for example, when we are redirected from another page.
    $("#alerts-login").ufAlerts();
    $("#alerts-login").ufAlerts('fetch').ufAlerts('render');

    function toggleRegistrationForm() {
		$('.login-form').fadeOut('fast', function() {
			$('.register-form').fadeIn('fast');
			$("#captcha").captcha();
		});
    }

    function toggleLoginForm() {
    	$('.register-form').fadeOut('fast', function() {
			$('.login-form').fadeIn('fast');
		});
    }

    /**
     * If there is a redirect parameter in the query string, redirect to that page.
     * Otherwise, if there is a UF-Redirect header, redirect to that page.
     * Otherwise, redirect to the home page.
     */
    function redirectOnLogin(jqXHR) {
        var components = URI.parse(window.location.href);
        var query = URI.parseQuery(components['query']);

        if (query && query['redirect']) {
            window.location.replace(site.uri.public + '/' + query['redirect']);
        } else if (jqXHR.getResponseHeader('UF-Redirect')) {
            window.location.replace(jqXHR.getResponseHeader('UF-Redirect'));
        } else {
            window.location.replace(site.uri.public);
        }
    }

    $('.show-register-form').on('click', toggleRegistrationForm);

    $('.show-login-form').on('click', toggleLoginForm);

    // TOS modal
    $(this).find('.js-show-tos').click(function() {
        $("body").ufModal({
            sourceUrl: site.uri.public + "/modals/account/tos",
            msgTarget: $("#alerts-register")
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
    $("#register").find('#form-register-username-suggest').on('click', function() {
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
        validators: registrationValidators,
        msgTarget: $("#alerts-register"),
        keyupDelay: 500
    }).on("submitSuccess.ufForm", function() {
        // Show login on success
        toggleLoginForm();
        // Show success messages
        // TODO: destroy method for simpler initialization
        if (!$("#alerts-login").data('ufAlerts')) {
            $("#alerts-login").ufAlerts();
        } else {
            $("#alerts-login").ufAlerts('clear');
        }

        $("#alerts-login").ufAlerts('fetch').ufAlerts('render');
    }).on("submitError.ufForm", function() {
        // Reload captcha
        $("#captcha").captcha();
    });

    $("#sign-in").ufForm({
        validators: page.validators.login,
        msgTarget: $("#alerts-login")
    }).on("submitSuccess.ufForm", function(event, data, textStatus, jqXHR) {
        redirectOnLogin(jqXHR);
    });
});
