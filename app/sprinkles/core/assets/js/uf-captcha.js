/**
 * This plugin reloads the captcha in the specified element.
 */
(function( $ ) {
    $.fn.captcha = function() {
        // Set the new captcha image
        $(this).attr('src', site.uri.public + "/account/captcha?" + new Date().getTime());

        // Clear whatever the user entered for the captcha value last time
        var target = $(this).data('target');
        $(target).val("");
    };
}( jQuery ));
