<?php
//===============================================
// Debug
//===============================================
ini_set('display_errors','On');
error_reporting(E_ALL);

function logAllErrors($errno, $errstr, $errfile, $errline, array $errcontext) {
    ini_set("log_errors", 1);
    ini_set("display_errors", 0);

    error_log("Error ($errno): $errstr in $errfile on line $errline");
    throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
}

//===============================================
// mod_rewrite
//===============================================
//Please configure via .htaccess or httpd.conf

//===============================================
// Madatory UFMVC Settings (please configure)
//===============================================
define('APP_PATH','app/'); //with trailing slash pls
define('WEB_FOLDER','/UserFrosting/'); //with trailing slash pls
define('VIEW_PATH', 'app/views/');
define('myUrl', 'localhost');

//===============================================
// Other Settings
//===============================================
$GLOBALS['sitename']='UserFrosting - MVC';
//$GLOBALS['dbh'] = NULL;

$db_host = "localhost"; //Host address (most likely localhost)
$db_name = "dbname"; //Name of Database
$db_user = "username"; //Name of database user
$db_pass = "password"; //Password for database user
$db_table_prefix = "uc_";


function pdoConnect(){
    global $db_host, $db_name, $db_user, $db_pass;
    try {
        $db = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_pass);
        $db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $db;
    } catch(PDOException $e) {
        return $e->getMessage();
    }
}

GLOBAL $errors;
GLOBAL $successes;

$errors = array();
$successes = array();

/* Create a new mysqli object with database connection parameters */
$mysqli = new mysqli($db_host, $db_user, $db_pass, $db_name);
GLOBAL $mysqli;
//mysqli_set_charset($mysqli, "utf8");

if(mysqli_connect_errno()) {
    echo "Connection Failed: " . mysqli_connect_errno();
    exit();
}

//Direct to install directory, if it exists
if(is_dir("install/"))
{
    header("Location: install/");
    die();

}

$stmt = $mysqli->prepare("SELECT id, name, value
	FROM ".$db_table_prefix."configuration");
$stmt->execute();
$stmt->bind_result($id, $name, $value);
$settings = [];
while ($stmt->fetch()){
    $settings[$name] = array('id' => $id, 'name' => $name, 'value' => $value);
}
$stmt->close();

//Set Settings
$emailActivation = $settings['activation']['value'];
$can_register = $settings['can_register']['value'];
$mail_templates_dir = "models/mail-templates/";
$websiteName = $settings['website_name']['value'];
$websiteUrl = $settings['website_url']['value'];
$emailAddress = $settings['email']['value'];
$resend_activation_threshold = $settings['resend_activation_threshold']['value'];
$emailDate = date('dmy');
$language = $settings['language']['value'];
$template = $settings['template']['value'];
$new_user_title = $settings['new_user_title']['value'];
$email_login = 1;//$settings['email_login']['value'];

//$bnet_string = 'us.battle.net';
//set like ?locale=fr_FR
//$locale_string = '';

// This is the user id of the master (root) account.
// The root user cannot be deleted, and automatically has permissions to everything regardless of permission group membership.
$master_account = 1;

$default_hooks = array("#WEBSITENAME#","#WEBSITEURL#","#DATE#");
$default_replace = array($websiteName,$websiteUrl,$emailDate);

if (!file_exists($language)) {
    $language = "./app/inc/languages/en.php";
}

if(!isset($language)) $language = "./app/inc/languages/en.php";

//Pages to require
require_once($language);
require_once("./app/inc/class.mail.php");
require_once("./app/inc/class.user.php");
require_once("./app/inc/class.newuser.php");
//require_once("./app/inc/class.newcharacter.php");
require_once("./app/inc/chrome.php");
require_once("./app/inc/funcs.php");
//require_once("./app/inc/wow_funcs.php");
//require_once("./app/inc/db-settings.php");

session_start();

//Global User Object Var
//loggedInUser can be used globally if constructed
if(isset($_SESSION["userCakeUser"]) && is_object($_SESSION["userCakeUser"]))
{
    $loggedInUser = $_SESSION["userCakeUser"];
}
//===============================================
// Includes
//===============================================
require('ufmvc.php');

//===============================================
// Session
//===============================================
/*
session_start();
*/
//===============================================
// Uncaught Exception Handling
//===============================================s
/*
set_exception_handler('uncaught_exception_handler');

function uncaught_exception_handler($e) {
  ob_end_clean(); //dump out remaining buffered text
  $vars['message']=$e;
  die(View::do_fetch(APP_PATH.'errors/exception_uncaught.php',$vars));
}

function custom_error($msg='') {
  $vars['msg']=$msg;
  die(View::do_fetch(APP_PATH.'errors/custom_error.php',$vars));
}
*/

//===============================================
// Database
//===============================================

/*function getdbh() {
    if (!isset($GLOBALS['dbh']))
    {
        try {
        // Independent configuration
        $GLOBALS['dbh'] = new medoo(['database_type' => 'mysql', 'database_name' => 'uf', 'server' => 'localhost', 'username' => 'root', 'password' => '420smoking',
        // optional
        //'port' => 3306, 'charset' => 'utf8mb4_unicode_ci',
        'option' => [PDO::ATTR_CASE => PDO::CASE_NATURAL]]);
        $errors = $GLOBALS['dbh']->error();
        } catch (Execption $errors) {
            die('Error:'.$GLOBALS['dbh']->error());
        }
    }
}*/
/*
function getdbh() {
  if (!isset($GLOBALS['dbh']))
    try {
      //$GLOBALS['dbh'] = new PDO('sqlite:'.APP_PATH.'db/ufmvc.sqlite');
      //$GLOBALS['dbh'] = new PDO('mysql:host=localhost;dbname=dbname', 'username', 'password');
        $GLOBALS['dbh'] = $this->database;
        //$GLOBALS['dbh_error'] = $this->database->error();

    } catch (PDOException $e) {
      die('Connection failed: '.$this->database->error()); //$e->getMessage());
    }
  return $GLOBALS['dbh'];
}
*/

//===============================================
// Autoloading for Business Classes
//===============================================
// Assumes Model Classes start with capital letters and Helpers start with lower case letters

function __autoload($classname) {
  $a=$classname[0];
  if ($a >= 'A' && $a <='Z')
    require_once(APP_PATH.'models/'.$classname.'.php');
  else
    require_once(APP_PATH.'helpers/'.$classname.'.php');  
}


//===============================================
// Start the controller
//===============================================
$controller = new Controller(APP_PATH.'controllers/',WEB_FOLDER,'main','index');
