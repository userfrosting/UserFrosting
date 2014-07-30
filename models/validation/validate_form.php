<?php

require_once("Validator.php");

Valitron\Validator::langDir(__DIR__.'/lang'); // always set langDir before lang.
Valitron\Validator::lang('en');

// Convert checkbox field to '0' or '1'
function realizeCheckbox($field_name, &$post){
	if (isset($post[$field_name])){
		$post[$field_name] = "1";
        return true;
	} else {
		$post[$field_name] = "0";
        return false;
	}
    
}

// Field names must match the corresponding column names in the DB
function validateSiteSettings(&$post){
    // Preprocess checkboxes
    realizeCheckbox('can_register', $post);
    realizeCheckbox('email_login', $post);
    realizeCheckbox('activation', $post);
    
    // Sanitize fields
    foreach ($post as $key => $value){
        $post[$key] = htmlentities($value);
    }
    
    // Set up Valitron validator
    $v = new Valitron\Validator($post);
    
    // Add field rules
    $v->rule('required', 'website_name');
    $v->rule('lengthBetween', 'website_name', 1, 150)->message(lang("CONFIG_NAME_CHAR_LIMIT",array(1,150)));
    
    $v->rule('required', 'website_url');
    $v->rule('lengthBetween', 'website_url', 1, 150)->message(lang("CONFIG_URL_CHAR_LIMIT",array(1,150)));
    
    $v->rule('required', 'email');
    $v->rule('lengthBetween', 'email', 1, 150)->message(lang("CONFIG_EMAIL_CHAR_LIMIT",array(1,150)));
    $v->rule('email', 'email')->message(lang("CONFIG_EMAIL_INVALID"));
    
    $v->rule('required', 'new_user_title');
    $v->rule('lengthBetween', 'new_user_title', 1, 150)->message(lang("CONFIG_TITLE_CHAR_LIMIT",array(1,150)));
    
    $v->rule('required', 'resend_activation_threshold');
    $v->rule('min', 'resend_activation_threshold', 0)->message(lang("CONFIG_ACTIVATION_RESEND_RANGE",array(0,72)));
    $v->rule('max', 'resend_activation_threshold', 72)->message(lang("CONFIG_ACTIVATION_RESEND_RANGE",array(0,72)));

    $v->rule('required', 'token_timeout');
    $v->rule('min', 'token_timeout', 0);
    $v->rule('max', 'token_timeout', 72);

    $v->rule('required', 'language');
    $v->rule('lengthBetween', 'language', 1, 150)->message(lang("CONFIG_LANGUAGE_CHAR_LIMIT",array(1,150)));
    
    // Validate!
    $v->validate();
    
    return $v->errors();
}

?>