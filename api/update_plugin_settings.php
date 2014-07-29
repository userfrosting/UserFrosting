<?php

require_once("../models/config.php");

set_error_handler('logAllErrors');

// User must be logged in
if (!isUserLoggedIn()){
    addAlert("danger", "You must be logged in to access this resource.");
    echo json_encode(array("errors" => 1, "successes" => 0));
    exit();
}

$validator = new Validator();
//Forms posted
if (isset($_POST)){
    $posted = $_POST;
}

if(!empty($posted))
{
	$newSettings = $posted;
	$newWebsiteName = $validator->requiredPostVar('website_name');
	$newWebsiteUrl = $validator->requiredPostVar('website_url');
	// Append a slash to the end, if not present
	if (substr($newWebsiteUrl, -1) != "/"){
	  $newWebsiteUrl = $newWebsiteUrl . "/";
	  $newSettings['website_url'] = $newWebsiteUrl;
	}
	
	$newEmail = $validator->requiredPostVar('email');
	$newTitle = $validator->requiredPostVar('new_user_title');
	if (isset($newSettings['activation'])){
		$newActivation = $newSettings['activation'];
	} else {
		$newSettings['activation'] = $newActivation = "0";
	}
	
	if (isset($newSettings['can_register'])){
		$newRegistration = $newSettings['can_register'];
	} else {
		$newSettings['can_register'] = $newRegistration = "0";
	}
    if (isset($newSettings['email_login'])){
        $emailLogin = $newSettings['email_login'];
    } else {
        $newSettings['email_login'] = $emailLogin = "0";
    }
	$newResend_activation_threshold = $validator->requiredPostVar('resend_activation_threshold');

    //grab the new value and multiply by 60 to get minutes and again by 60 to get the hours
    //value returned is hours in seconds
    //$newTokenTimeout = $validator->requiredPostVar('token_timeout') * 60 * 60;
    $newSettings['token_timeout'] = $newSettings['token_timeout'] *60 *60;
    if (isset($newSettings['token_timeout'])){
        $newTokenTimeout = $newSettings['token_timeout'];
    } else {
        $newSettings['token_timeout'] = $newTokenTimeout = "10800";
    }

    $newLanguage = $validator->requiredPostVar('language');
	$newTemplate = $validator->requiredPostVar('template');
	
	//Validate new site name
	if ($newWebsiteName != $websiteName) {
		if(minMaxRange(1,150,$newWebsiteName))
		{
			$errors[] = lang("CONFIG_NAME_CHAR_LIMIT",array(1,150));
		}
		else if (count($errors) == 0) {
			$websiteName = $newWebsiteName;
		}
	}
	
	//Validate new URL
	if ($newWebsiteUrl != $websiteUrl) {		
		if(minMaxRange(1,150,$newWebsiteUrl))
		{
			$errors[] = lang("CONFIG_URL_CHAR_LIMIT",array(1,150));
		}
		else if (count($errors) == 0) {
			$websiteUrl = $newWebsiteUrl;
		}
	}
	
	//Validate new site email address
	if ($newEmail != $emailAddress) {
		if(minMaxRange(1,150,$newEmail))
		{
			$errors[] = lang("CONFIG_EMAIL_CHAR_LIMIT",array(1,150));
		}
		elseif(!isValidEmail($newEmail))
		{
			$errors[] = lang("CONFIG_EMAIL_INVALID");
		}
		else if (count($errors) == 0) {
			$emailAddress = $newEmail;
		}
	}

	//Validate new default title
	if ($newTitle != $new_user_title) {
		if(minMaxRange(1,150,$newTitle))
		{
			$errors[] = lang("CONFIG_TITLE_CHAR_LIMIT",array(1,150));
		}
		else if (count($errors) == 0) {
			$new_user_title = $newTitle;
		}
	}
		
	//Validate registration enable/disable selection
	if ($newRegistration != $can_register) {
		if($newRegistration != "0" AND $newRegistration != "1")
		{
			$errors[] = lang("CONFIG_REGISTRATION_TRUE_FALSE");
		}
		else if (count($errors) == 0) {
			$can_register = $newRegistration;
		}
	}

    //Validate email login enable/disable selection
    if ($emailLogin != $email_login) {
        if($emailLogin != "0" AND $emailLogin != "1")
        {
            $errors[] = lang("CONFIG_REGISTRATION_TRUE_FALSE");
        }
        else if (count($errors) == 0) {
            $email_login = $emailLogin;
        }
    }

	//Validate email activation selection
	if ($newActivation != $emailActivation) {
		if($newActivation != "0" AND $newActivation != "1")
		{
			$errors[] = lang("CONFIG_ACTIVATION_TRUE_FALSE");
		}
		else if (count($errors) == 0) {
			$emailActivation = $newActivation;
		}
	}	
		
	//Validate new email activation resend threshold
	if ($newResend_activation_threshold != $resend_activation_threshold) {
		if($newResend_activation_threshold > 72 OR $newResend_activation_threshold < 0)
		{
			$errors[] = lang("CONFIG_ACTIVATION_RESEND_RANGE",array(0,72));
		}
		else if (count($errors) == 0) {
			$resend_activation_threshold = $newResend_activation_threshold;
		}
	}

    //Validate token time
    if ($newTokenTimeout != $token_timeout) {
        if($newTokenTimeout >= 0)
        {
            $errors[] = 'error on timeout token';
        }
        else if (count($errors) == 0) {
            $token_timeout = $newTokenTimeout;
        }
    }

    //Validate new language selection
	if ($newLanguage != $language) {
		if(minMaxRange(1,150,$newLanguage))
		{
			$errors[] = lang("CONFIG_LANGUAGE_CHAR_LIMIT",array(1,150));
		}
		elseif (!file_exists($newLanguage)) {
			$errors[] = lang("CONFIG_LANGUAGE_INVALID",array($newLanguage));				
		}
		else if (count($errors) == 0) {
			$language = $newLanguage;
		}
	}
	
	//Validate new template selection
	if ($newTemplate != $template) {
		if(minMaxRange(1,150,$newTemplate))
		{
			$errors[] = lang("CONFIG_TEMPLATE_CHAR_LIMIT",array(1,150));
		}
		elseif (!file_exists($newTemplate)) {
			$errors[] = lang("CONFIG_TEMPLATE_INVALID",array($newTemplate));				
		}
		else if (count($errors) == 0) {
			$template = $newTemplate;
		}
	}
	
	//Update configuration table with new settings
	if (count($errors) == 0) {
	  if (updateSiteSettings($newSettings)){
		$successes[] = lang("CONFIG_UPDATE_SUCCESSFUL");
	  }
	}
} else {
	$errors[] = lang("NO_DATA");
}

restore_error_handler();

foreach ($errors as $error){
  addAlert("danger", $error);
}
foreach ($successes as $success){
  addAlert("success", $success);
}

if (isset($_POST['ajaxMode']) and $_POST['ajaxMode'] == "true" ){
  echo json_encode(array(
	"errors" => count($errors),
	"successes" => count($successes)));
} else {
  header('Location: ' . getReferralPage());
  exit();
}

?>
