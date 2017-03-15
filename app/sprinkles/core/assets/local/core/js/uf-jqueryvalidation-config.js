/**
 * Set jQuery.validate settings for bootstrap integration
 */
jQuery.validator.setDefaults({
    highlight: function(element) {
        var formGroup = jQuery(element).closest('.form-group');
        formGroup.addClass('has-error has-feedback');
        formGroup.removeClass('has-success');
        formGroup.find('.form-control-feedback').remove();
        formGroup.find('.form-control-icon').show();

        // Hide any help block text
        formGroup.find('.help-block').hide();
    },
    unhighlight: function(element) {
        var formGroup = jQuery(element).closest('.form-group');

        formGroup.removeClass('has-error');

        // Completely remove the error block, rather than just clearing the text (jqueryvalidation's default action)
        formGroup.find('.error-block').remove();

        // Re-show any help block text
        formGroup.find('.help-block').show();

        // Reshow any non-feedback icons if there is an error
        if (formGroup.hasClass('has-error')) {
            formGroup.find('.form-control-icon').show();
        }
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
        var formGroup = jQuery(element).closest('.form-group');
        formGroup.addClass('has-success has-feedback');
        formGroup.find('.form-control-feedback').remove();
        // Hide any non-feedback icons
        formGroup.find('.form-control-icon').hide();
        // Add a new check mark
        jQuery(element).after('<i class="fa fa-check form-control-feedback" aria-hidden="true"></i>');
    }
});
