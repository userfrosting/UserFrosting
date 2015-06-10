<?php
/*

UserFrosting
By Alex Weissman

UserFrosting is 100% free and open-source.

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

require_once("config-userfrosting.php");

use \Slim\Extras\Middleware\CsrfGuard;

// CSRF Middleware
$app->add(new CsrfGuard());

/**** Database Setup ****/

// Specify which database model you want to use
class_alias("UserFrosting\MySqlDatabase",       "UserFrosting\Database");
class_alias("UserFrosting\MySqlUser",           "UserFrosting\User");
class_alias("UserFrosting\MySqlUserLoader",     "UserFrosting\UserLoader");
class_alias("UserFrosting\MySqlAuthLoader",     "UserFrosting\AuthLoader");
class_alias("UserFrosting\MySqlGroup",          "UserFrosting\Group");
class_alias("UserFrosting\MySqlGroupLoader",    "UserFrosting\GroupLoader");
class_alias("UserFrosting\MySqlSiteSettings",   "UserFrosting\SiteSettings");

// Set enumerative values
defined("GROUP_NOT_DEFAULT") or define("GROUP_NOT_DEFAULT", 0);    
defined("GROUP_DEFAULT") or define("GROUP_DEFAULT", 1);
defined("GROUP_DEFAULT_PRIMARY") or define("GROUP_DEFAULT_PRIMARY", 2);

// Pass Slim app to database
\UserFrosting\Database::$app = $app;
// Initialize static loader classes
\UserFrosting\GroupLoader::init();
\UserFrosting\UserLoader::init();

/* Load UserFrosting site settings */    
$app->site = new \UserFrosting\SiteSettings();

$app->hook('settings.register', function () use ($app){
    // Register core site settings
    $app->site->register('userfrosting', 'site_title', "Site Title");
    $app->site->register('userfrosting', 'author', "Site Author");
    $app->site->register('userfrosting', 'admin_email', "Account Management Email");
    $app->site->register('userfrosting', 'default_locale', "Locale for New Users", "select", $app->site->getLocales());
    $app->site->register('userfrosting', 'can_register', "Public Registration", "toggle", [0 => "Off", 1 => "On"]);
    $app->site->register('userfrosting', 'enable_captcha', "Registration Captcha", "toggle", [0 => "Off", 1 => "On"]);
    $app->site->register('userfrosting', 'require_activation', "Require Account Activation", "toggle", [0 => "Off", 1 => "On"]);
    $app->site->register('userfrosting', 'email_login', "Email Login", "toggle", [0 => "Off", 1 => "On"]);
    $app->site->register('userfrosting', 'resend_activation_threshold', "Resend Activation Email Cooloff (s)");
    $app->site->register('userfrosting', 'reset_password_timeout', "Password Recovery Timeout (s)");
}, 1);       

/**** Session and User Setup ****/
    
$db_error = false;

// Set user, if one is logged in
if(isset($_SESSION["userfrosting"]["user"]) && is_object($_SESSION["userfrosting"]["user"])) {       
    // Test database connection
    try {
        // Refresh the user.  If they don't exist any more, then an exception will be thrown.
        $_SESSION["userfrosting"]["user"] = $_SESSION["userfrosting"]["user"]->fresh();
        $app->user = $_SESSION["userfrosting"]["user"];
    } catch (\PDOException $e) {
        error_log("Error in " . $e->getFile() . " on line " . $e->getLine() . ": " . $e->getMessage());
        error_log($e->getTraceAsString());
        $app->user = new \UserFrosting\User([], $app->config('user_id_guest'));
        $db_error = true;
    }
// Otherwise, create a dummy "guest" user
} else {
    $app->user = new \UserFrosting\User([], $app->config('user_id_guest'));
}   
   
/**** Message Stream Setup ****/

/* Set the translation path and default language path. */
\Fortress\MessageTranslator::setTranslationTable($app->config("locales.path") . "/" . $app->user->locale . ".php");
\Fortress\MessageTranslator::setDefaultTable($app->config("locales.path") . "/en_US.php");

/* Set up persistent message stream for alerts.  Do not use Slim's, it sucks. */
if (!isset($_SESSION['userfrosting']['alerts']))
    $_SESSION['userfrosting']['alerts'] = new \Fortress\MessageStream();

$app->alerts = $_SESSION['userfrosting']['alerts'];

/**** Error Handling Setup ****/

// Custom error-handler: send a generic message to the client, but put the specific error info in the error log.
// A Slim application uses its built-in error handler if its debug setting is true; otherwise, it uses the custom error handler.
$app->error(function (\Exception $e) use ($app) {
    $app->alerts->addMessageTranslated("danger", "SERVER_ERROR");
    error_log("Error in " . $e->getFile() . " on line " . $e->getLine() . ": " . $e->getMessage());
});

// Also handle fatal errors
register_shutdown_function( "fatal_handler" );

function fatal_handler() {
    global $app;
    $error = error_get_last();
  
    // Handle fatal errors
    if( $error !== NULL && $error['type'] == E_ERROR) {
      $errno   = $error["type"];
      $errfile = $error["file"];
      $errline = $error["line"];
      $errstr  = $error["message"];
      // Inform the client of a fatal error
      $app->alerts->addMessageTranslated("danger", "SERVER_ERROR");
      error_log("Error ($errno) in $errfile on line $errline: $errstr");
      header("HTTP/1.1 500 Internal Server Error");
    }
}

/**** Templating Engine Setup ****/

/* Also, import UserFrosting variables as global Twig variables */    
$twig = $app->view()->getEnvironment();   
$twig->addGlobal("site", $app->site);

// If a user is logged in, add the user object as a global Twig variable
if ($app->user)
    $twig->addGlobal("user", $app->user);

// Load default account theme and current account theme
// Thanks to https://diarmuid.ie/blog/post/multiple-twig-template-folders-with-slim-framework
$loader = $twig->getLoader();
// First look in user's theme...
$loader->addPath($app->config('themes.path') . "/" . $app->user->getTheme());
// THEN in default.
$loader->addPath($app->config('themes.path') . "/default");

// Add Twig function for checking permissions during dynamic menu rendering
$function_check_access = new Twig_SimpleFunction('checkAccess', function ($hook, $params = []) use ($app) {
    return $app->user->checkAccess($hook, $params);
});

$twig->addFunction($function_check_access);    

// Add Twig function for translating message hooks
$function_translate = new Twig_SimpleFunction('translate', function ($hook, $params = []) use ($app) {
    return \Fortress\MessageTranslator::translate($hook, $params);
});

$twig->addFunction($function_translate);

if ($db_error){
    // In case the error is because someone is trying to reinstall with new db info while still logged in, log them out
    session_destroy();
    $controller = new \UserFrosting\BaseController($app);
    $controller->pageDatabaseError();
    exit;
}

/* TODO: enable Twig caching?
$view = $app->view();
$view->parserOptions = array(
    'debug' => true,
    'cache' => dirname(__FILE__) . '/cache'
);
*/