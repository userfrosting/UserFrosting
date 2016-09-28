/**
 * Page-specific Javascript file.  Should generally be included as a separate asset bundle in your page template.
 * example: {{ assets.js('js/pages/sign-in-or-register') | raw }}
 *
 * This script depends on validation rules specified in components/validators.js.twig.
 *
 * Target page: account/sign-in-or-register
 */
$(document).ready(function() {

    /*
        Fullscreen background
    */
    $.backstretch(site.uri.images.background);
    
    /*
        Forms show / hide
    */
    
    // Show login form if registration is disabled
    if (!site.setting.can_register) {
        $('.login-form').show();
    }
    
    // Fetch and render any alerts on the login panel
    // This is needed, for example, when we are redirected from another page.
    $("#alerts-login").ufAlerts();
    $("#alerts-login").ufAlerts('fetch').ufAlerts('render');    
    
    function toggleRegistrationForm() {
    	if( ! $(this).hasClass('active') ) {
    		$('.show-login-form').removeClass('active');
    		$(this).addClass('active');
    		$('.login-form').fadeOut('fast', function() {
    			$('.register-form').fadeIn('fast');
    		});
    	}
    }
    
    function toggleLoginForm() {
    	if( ! $(this).hasClass('active') ) {
    		$('.show-register-form').removeClass('active');
    		$(this).addClass('active');
    		$('.register-form').fadeOut('fast', function() {
    			$('.login-form').fadeIn('fast');
    		});
    	}
    }
    
    $('.show-register-form').on('click', toggleRegistrationForm);
    
    $('.show-login-form').on('click', toggleLoginForm);

    // TODO: Process form 
    $("#register").ufForm({
        validators: page.validators.register,
        msgTarget: $("#alerts-register")
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
    }).on("submitSuccess.ufForm", function() {
        // Forward to settings page on success
        window.location.replace(site.uri.public + "/account/settings");
    });    
});
