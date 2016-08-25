<?php

/**
 * en_US
 *
 * US English message token translations for the core sprinkle.
 *
 * @package UserFrosting
 * @link http://www.userfrosting.com/components/#i18n
 * @author Alexander Weissman
 */

$lang = [];

// Locale settings
$lang = array_merge($lang, [
	"PLURAL_RULE" => 1
]);

return array_merge($lang, [
    "ABOUT" => "About",
	
    "CAPTCHA_FAIL" => "You did not enter the captcha code correctly.",
	"CONFIRM" => "Confirm",
	
    "DENY" => "Deny",
	
    "ERROR" => "Error",
    
	"FEATURE_DISABLED" => "This feature is currently disabled",
	
    "MAIL_ERROR" => "Fatal error attempting mail, contact your server administrator.  If you are the admin, please check the UF mail log.",
    
    "NO_DATA" => "No data/bad data sent",
	"NOTHING_TO_UPDATE" => "Nothing to update",
    
    "SERVER_ERROR" => "Oops, looks like our server might have goofed. If you're an admin, please check the PHP or UF error logs.",
    "SPECIFY_CAPTCHA" => "Please enter the captcha code.",
	"SQL_ERROR" => "Fatal SQL error",
    "SUCCESS" => "Success"
]);

return $lang;
