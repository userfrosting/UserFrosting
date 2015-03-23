<?php
/*

UserFrosting Version: 0.2.2
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

// Used to force backend scripts to log errors rather than print them as output
function logAllErrors($errno, $errstr, $errfile, $errline, array $errcontext) {
	ini_set("log_errors", 1);
	ini_set("display_errors", 0);
	
    error_log("Error ($errno): $errstr in $errfile on line $errline");
	throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
}

// This will stop the installer / upgrader from running as it normally would and should always be set to false
// Options TRUE | FALSE bool
$dev_env = FALSE;

require_once("db-settings.php"); //Require DB connection
require_once("funcs.php");
require_once("error_functions.php");
require_once("template_functions.php");
require_once("password.php");
require_once("db_functions.php");
require_once("validation/Validator.php");
require_once("table_builder.php");
require_once("form_builder.php");

// Set validation parameters

Valitron\Validator::langDir(__DIR__.'/validation/lang'); // always set langDir before lang.
Valitron\Validator::lang('en');

//Retrieve basic configuration settings

$settings = fetchConfigParameters();

//Grab plugin settings, used in plugin like so:
//$pvalue = $plugin_settings['variable_name']['config_value'];
/*
 $pvalue = $plugin_settings['$pmsystem']['value'];
 if ($pvalue != 1){
    // Forward to index page
    addAlert("danger", "Whoops, looks like the private message system is not enabled");
    header("Location: ".SITE_ROOT."account/index.php");
    exit();
 }
 */
$plugin_settings = fetchConfigParametersPlugins();

//Set Settings
$emailDate = date('F j, Y');
$emailActivation = $settings['activation'];
$can_register = $settings['can_register'];
$websiteName = $settings['website_name'];
$websiteUrl = $settings['website_url'];
$emailAddress = $settings['email'];
$resend_activation_threshold = $settings['resend_activation_threshold'];
$language = $settings['language'];
$new_user_title = $settings['new_user_title'];
$email_login = $settings['email_login'];
$token_timeout = $settings['token_timeout'];
$version = $settings['version'];

// Check for upgrade, do this hear for access to $version
checkUpgrade($version, $dev_env);

// Determine if this is SSL or unsecured connection
$url_prefix = "http://";
// Determine if connection is http or https
if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') {
    // SSL connection
	$url_prefix = "https://";
}

// Define paths here
defined("SITE_ROOT")
    or define("SITE_ROOT", $url_prefix.$websiteUrl);

defined("ACCOUNT_ROOT")
    or define("ACCOUNT_ROOT", SITE_ROOT . "account/");
		
defined("LOCAL_ROOT")
	or define ("LOCAL_ROOT", realpath(dirname(__FILE__)."/.."));

defined("MENU_TEMPLATES")
    or define("MENU_TEMPLATES", dirname(__FILE__) . "/menu-templates/");

defined("MAIL_TEMPLATES")
	or define("MAIL_TEMPLATES", dirname(__FILE__) . "/mail-templates/");

// Include paths for files containing secure functions
$files_secure_functions = array(
    dirname(__FILE__) . "/secure_functions.php"
);

// Include paths for pages to add to site page management
$page_include_paths = fetchFileList();

// Other constants
defined("ACCOUNT_HEAD_FILE")
	or define("ACCOUNT_HEAD_FILE", "head-account.html");	

// Set to true if you want authorization failures to be logged to the PHP error log.
defined("LOG_AUTH_FAILURES")
	or define("LOG_AUTH_FAILURES", false);

defined("SESSION_NAME")
    or define("SESSION_NAME", "UserFrosting");

defined("SITE_TITLE")
    or define("SITE_TITLE", $websiteName);

	
// This is the user id of the master (root) account.
// The root user cannot be deleted, and automatically has permissions to everything regardless of group membership.
$master_account = 1;

$default_hooks = array("#WEBSITENAME#","#WEBSITEURL#","#DATE#");
$default_replace = array($websiteName,SITE_ROOT,$emailDate);

// The dirname(__FILE__) . "/..." construct tells PHP to look for the include file in the same directory as this (the config) file
if (!file_exists($language)) {
	$language = dirname(__FILE__) . "/languages/en.php";
}

if(!isset($language)) $language = dirname(__FILE__) . "/languages/en.php";

function getAbsoluteDocumentPath($localPath){
	return SITE_ROOT . getRelativeDocumentPath($localPath);
}

// Return the document path of a file, relative to the root directory of the site.  Takes the absolute local path of the file (such as defined by __FILE__)
function getRelativeDocumentPath($localPath){
	// Replace backslashes in local path (if we're in a windows environment)
	$localPath = str_replace('\\', '/', $localPath);
	
	// Get lowercase version of path
	$localPathLower = strtolower($localPath);

	// Replace backslashes in local root (if we're in a windows environment)
	$localRoot = str_replace('\\', '/', LOCAL_ROOT);	
	
	// Get lowercase version of path
	$localRootLower = strtolower($localRoot) . "/";
	
	// Remove local root but preserve case
	$pos = strpos($localPathLower, $localRootLower);
	if ($pos !== false) {
		return substr_replace($localPath,"",$pos,strlen($localRootLower));
	} else {
		return $localRoot;
	}
}

//Pages to require
require_once($language);
require_once("class_validator.php");
require_once("validation/validate_form.php");
require_once("authorization.php");
require_once("secure_functions.php");
require_once("class.mail.php");
require_once("class.user.php");

//ChromePhp debugger for chrome console
// http://craig.is/writing/chrome-logger
//require_once("chrome.php");

session_name(SESSION_NAME);
session_start();

//Global User Object Var
//loggedInUser can be used globally if constructed
if(isset($_SESSION["userCakeUser"]) && is_object($_SESSION["userCakeUser"]))
{
	$loggedInUser = $_SESSION["userCakeUser"];
}