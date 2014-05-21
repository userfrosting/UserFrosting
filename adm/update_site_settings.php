<?php
/*

UserFrosting Version: 0.1
By Alex Weissman
Copyright (c) 2014

Based on the UserCake user management system, v2.0.2.
Copyright (c) 2009-2012

UserFrosting, like UserCake, is 100% free and open-source.

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the 'Software'), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:
The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED 'AS IS', WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.

*/
require_once("models/config.php");

set_error_handler('logAllErrors');

// Recommended admin-only access
if (!securePage($_SERVER['PHP_SELF'])){
  addAlert("danger", "Whoops, looks like you don't have permission to update the site settings.");
  if (isset($_POST['ajaxMode']) and $_POST['ajaxMode'] == "true" ){
	echo json_encode(array("errors" => 1, "successes" => 0));
  } else {
	header("Location: " . getReferralPage());
  }
  exit();
}

//Forms posted
if(!empty($_POST))
{
	$newSettings = $_POST;
	$newWebsiteName = requiredPostVar('website_name');
	$newWebsiteUrl = requiredPostVar('website_url');
	// Append a slash to the end, if not present
	if (substr($newWebsiteUrl, -1) != "/"){
	  $newWebsiteUrl = $newWebsiteUrl . "/";
	  $newSettings['website_url'] = $newWebsiteUrl;
	}
	
	$newEmail = requiredPostVar('email');
	$newTitle = requiredPostVar('new_user_title');
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
	$newResend_activation_threshold = requiredPostVar('resend_activation_threshold');
	$newLanguage = requiredPostVar('language');
	$newTemplate = requiredPostVar('template');
	
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
	  global $mysqli,$db_table_prefix;
	  $stmt = $mysqli->prepare("UPDATE ".$db_table_prefix."configuration
		  SET 
		  value = ?
		  WHERE
		  name = ?");
	  foreach ($newSettings as $name => $value){
		$stmt->bind_param("ss", $value, $name);
		$stmt->execute();
	  }
	  $stmt->close();	
	  $successes[] = lang("CONFIG_UPDATE_SUCCESSFUL");		
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
