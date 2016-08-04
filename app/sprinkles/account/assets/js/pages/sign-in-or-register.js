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
    $('.show-register-form').on('click', function(){
    	if( ! $(this).hasClass('active') ) {
    		$('.show-login-form').removeClass('active');
    		$(this).addClass('active');
    		$('.login-form').fadeOut('fast', function(){
    			$('.register-form').fadeIn('fast');
    		});
    	}
    });
    // ---
    $('.show-login-form').on('click', function(){
    	if( ! $(this).hasClass('active') ) {
    		$('.show-register-form').removeClass('active');
    		$(this).addClass('active');
    		$('.register-form').fadeOut('fast', function(){
    			$('.login-form').fadeIn('fast');
    		});
    	}
    });

    // TODO: Process form 
    $("#register").ufForm({
        validators: page.validators.register,
        msgTarget: $("#alerts-register")
    }).on("submitSuccess", function() {
        // Forward to login page on success
        window.location.replace(site.uri.public + "/account/login");
    }).on("submitError", function() {
        // Reload captcha
        //$("#captcha").captcha();
    });
    
    $("#sign-in").ufForm({
        validators: page.validators.login,
        msgTarget: $("#alerts-login")
    }).on("submitSuccess", function() {
        // Forward to settings page on success
        window.location.replace(site.uri.public + "/account/settings");
    });    
});
