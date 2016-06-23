// This plugin reloads the captcha in the specified field
(function( $ ) {
    $.fn.captcha = function() {
        var field = $(this);
        console.log("Reloading captcha");
        
        var img_src = site['uri']['public'] + "/account/captcha?" + new Date().getTime();
        
        return $.ajax({  
          type: "GET",  
          url: img_src,  
          dataType: "text"
        }).then(function(data, statusText, jqXHR) {  // Pass the deferral back
            field.attr('src', data);
            var target = field.data('target');
            $(target).val("");
            return data;
        });
    };
}( jQuery ));  