/**
 * Set jQuery.validate settings for bootstrap integration
 */
jQuery.validator.setDefaults({
    highlight: function(element) {
        jQuery(element).closest('.form-group').addClass('has-error');
        jQuery(element).closest('.form-group').removeClass('has-success has-feedback');
        jQuery(element).closest('.form-group').find('.form-control-feedback').remove();
        
        // Hide any help block text
        jQuery(element).closest('.form-group').find('.help-block').hide();
    },
    unhighlight: function(element) {
        jQuery(element).closest('.form-group').removeClass('has-error');
        
        // Completely remove the error block, rather than just clearing the text (jqueryvalidation's default action)
        jQuery(element).closest('.form-group').find('.error-block').remove();
        
        // Re-show any help block text
        jQuery(element).closest('.form-group').find('.help-block').show();        
    },
    errorElement: 'p',
    errorClass: 'error-block',
    errorPlacement: function(error, element) {
        if(element.parent('.input-group').length) {
            error.insertAfter(element.parent());
        } else {
            error.insertAfter(element);
        }
    },
    success: function(element) {
        jQuery(element).closest('.form-group').addClass('has-success has-feedback');
        jQuery(element).after('<i class="fa fa-check form-control-feedback" aria-hidden="true"></i>');
    }
});
