// Set jQuery.validate settings for bootstrap integration
jQuery.validator.setDefaults({
    highlight: function(element) {
        jQuery(element).closest('.form-group').addClass('has-error');
        jQuery(element).closest('.form-group').removeClass('has-success has-feedback');
        jQuery(element).closest('.form-group').find('.form-control-feedback').remove();
    },
    unhighlight: function(element) {
        jQuery(element).closest('.form-group').removeClass('has-error');
    },
    errorElement: 'span',
    errorClass: 'text-danger',
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
