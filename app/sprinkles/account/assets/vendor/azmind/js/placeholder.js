
$(document).ready(function(){
	
	$('input[type="text"], input[type="password"], textarea').each(function() {
		$(this).val( $(this).attr('placeholder') );
    });
	
});