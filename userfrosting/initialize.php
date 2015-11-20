<?php
/**
 * UserFrosting initialization file.  Handles setup for database, site settings, JS/CSS includes, etc.
 *
 * @author Alex Weissman
 * @link http://www.userfrosting.com
 */
 
require_once 'vendor/autoload.php';
require_once 'models/auth/password.php';

// This if-block just checks that config-userfrosting.php exists
if (!file_exists(__DIR__ . "/config-userfrosting.php")){
    echo "<h2>We can't seem to find config-userfrosting.php!  You should rename the file config-userfrosting-example.php to config-userfrosting.php, and then fill in the configuration details for your database and server.  For more information, please see the <a href='http://www.userfrosting.com/installation/#install-userfrosting'>installation instructions</a>.</h2><br>";
    exit;
}

require_once 'config-userfrosting.php';

use \Slim\Extras\Middleware\CsrfGuard;

// Start session
$app->startSession();

/*===== Middleware.  Middleware gets run when $app->run is called, i.e. AFTER the code in initialize.php ====*/

/**** CSRF Middleware ****/
$app->add(new CsrfGuard());

/**** Session and User Setup ****/
$app->add(new UserFrosting\UserSession());

/**** Database Setup ****/

// Eloquent Query Builder
use Illuminate\Database\Capsule\Manager as Capsule;

$capsule = new Capsule;

$dbx = $app->config('db');

$capsule->addConnection([
    'driver'    => 'mysql',
    'host'      => $dbx['db_host'],
    'database'  => $dbx['db_name'],
    'username'  => $dbx['db_user'],
    'password'  => $dbx['db_pass'],
    'charset'   => 'utf8',
    'collation' => 'utf8_unicode_ci',
    'prefix'    => ''
]);

// Register as global connection
$capsule->setAsGlobal();

// Start Eloquent
$capsule->bootEloquent();

// Set enumerative values
defined("GROUP_NOT_DEFAULT") or define("GROUP_NOT_DEFAULT", 0);    
defined("GROUP_DEFAULT") or define("GROUP_DEFAULT", 1);
defined("GROUP_DEFAULT_PRIMARY") or define("GROUP_DEFAULT_PRIMARY", 2);

// Pass Slim app to database and core data model
\UserFrosting\Database::$app = $app;
\UserFrosting\UFModel::$app = $app;

// Initialize database properties
$table_user = new \UserFrosting\DatabaseTable($app->config('db')['db_prefix'] . "user", [
    "user_name",
    "display_name",
    "email",
    "title",
    "locale",
    "primary_group_id",       
    "secret_token",
    "flag_verified",
    "flag_enabled",
    "flag_password_reset",
    "created_at",
    "updated_at",
    "password"
]);

$table_user_event = new \UserFrosting\DatabaseTable($app->config('db')['db_prefix'] . "user_event", [
    "user_id",
    "event_type",
    "occurred_at",
    "description"
]);

$table_group = new \UserFrosting\DatabaseTable($app->config('db')['db_prefix'] . "group", [
    "name",
    "is_default",
    "can_delete",
    "theme",
    "landing_page",
    "new_user_title",
    "icon"
]);

$table_group_user = new \UserFrosting\DatabaseTable($app->config('db')['db_prefix'] . "group_user");
$table_configuration = new \UserFrosting\DatabaseTable($app->config('db')['db_prefix'] . "configuration");
$table_authorize_user = new \UserFrosting\DatabaseTable($app->config('db')['db_prefix'] . "authorize_user");
$table_authorize_group = new \UserFrosting\DatabaseTable($app->config('db')['db_prefix'] . "authorize_group");    

\UserFrosting\Database::setSchemaTable("user", $table_user);
\UserFrosting\Database::setSchemaTable("user_event", $table_user_event);
\UserFrosting\Database::setSchemaTable("group", $table_group);    
\UserFrosting\Database::setSchemaTable("group_user", $table_group_user);
\UserFrosting\Database::setSchemaTable("configuration", $table_configuration);
\UserFrosting\Database::setSchemaTable("authorize_user", $table_authorize_user);
\UserFrosting\Database::setSchemaTable("authorize_group", $table_authorize_group);  

// Info for RememberMe table
$app->remember_me_table = [
    'tableName' => $app->config('db')['db_prefix'] . "user_rememberme",
    'credentialColumn' => 'user_id',
    'tokenColumn' => 'token',
    'persistentTokenColumn' => 'persistent_token',
    'expiresColumn' => 'expires'
];

/* Event Types
    "sign_up",
    "sign_in",
    "verification_request",
    "password_reset_request",
*/

/* Load UserFrosting site settings */

// Default settings
$setting_values = [
    'userfrosting' => [
        'site_title' => 'UserFrosting', 
        'admin_email' => 'admin@userfrosting.com', 
        'email_login' => '1', 
        'can_register' => '1', 
        'enable_captcha' => '1',
        'require_activation' => '1', 
        'resend_activation_threshold' => '0', 
        'reset_password_timeout' => '10800',
        'create_password_expiration' => '86400',
        'default_locale' => 'en_US',
        'guest_theme' => 'default',
        'minify_css' => '0',
        'minify_js' => '0',
        'version' => '0.3.1.9', 
        'author' => 'Alex Weissman',
        'show_terms_on_register' => '1',
        'site_location' => 'The State of Indiana'
    ]
];
$setting_descriptions = [
    'userfrosting' => [
        "site_title" => "The title of the site.  By default, displayed in the title tag, as well as the upper left corner of every user page.", 
        "admin_email" => "The administrative email for the site.  Automated emails, such as verification emails and password reset links, will come from this address.", 
        "email_login" => "Specify whether users can login via email address or username instead of just username.", 
        "can_register" => "Specify whether public registration of new accounts is enabled.  Enable if you have a service that users can sign up for, disable if you only want accounts to be created by you or an admin.", 
        "enable_captcha" => "Specify whether new users must complete a captcha code when registering for an account.",
        "require_activation" => "Specify whether email verification is required for newly registered accounts.  Accounts created by another user never need to be verified.", 
        "resend_activation_threshold" => "The time, in seconds, that a user must wait before requesting that the account verification email be resent.", 
        "reset_password_timeout" => "The time, in seconds, before a user's password reset token expires.", 
        "create_password_expiration" => "The time, in seconds, before a new user's password creation token expires.",
        "default_locale" => "The default language for newly registered users.",
        "guest_theme" => "The template theme to use for unauthenticated (guest) users.",
        "minify_css" => "Specify whether to use concatenated, minified CSS (production) or raw CSS includes (dev).",
        "minify_js" => "Specify whether to use concatenated, minified JS (production) or raw JS includes (dev).",
        "version" => "The current version of UserFrosting.", 
        "author" => "The author of the site.  Will be used in the site's author meta tag.",
        "show_terms_on_register" => "Specify whether or not to show terms and conditions when registering.",
        "site_location" => "The nation or state in which legal jurisdiction for this site falls."
    ]
];

// Create the site settings object.  If the database cannot be accessed or has not yet been set up, use the default settings.
$app->site = new \UserFrosting\SiteSettings($setting_values, $setting_descriptions);

// Create the page schema object
$app->schema = new \UserFrosting\PageSchema($app->site->uri['css'], $app->config('css.path') , $app->site->uri['js'], $app->config('js.path') );

// Create a guest user, which lets us proceed until we can try to authenticate the user
$app->setupGuestEnvironment();

// Setup Twig custom functions
$app->setupTwig();

/** Register site settings with site settings config page */
$app->hook('settings.register', function () use ($app){
    // Register core site settings
    $app->site->register('userfrosting', 'site_title', "Site Title");
    $app->site->register('userfrosting', 'site_location', "Site Location");    
    $app->site->register('userfrosting', 'author', "Site Author");
    $app->site->register('userfrosting', 'admin_email', "Account Management Email");
    $app->site->register('userfrosting', 'default_locale', "Locale for New Users", "select", $app->site->getLocales());
    $app->site->register('userfrosting', 'guest_theme', "Guest Theme", "select", $app->site->getThemes());
    $app->site->register('userfrosting', 'can_register', "Public Registration", "toggle", [0 => "Off", 1 => "On"]);
    $app->site->register('userfrosting', 'enable_captcha', "Registration Captcha", "toggle", [0 => "Off", 1 => "On"]);
    $app->site->register('userfrosting', 'show_terms_on_register', "Show TOS", "toggle", [0 => "Off", 1 => "On"]);
    $app->site->register('userfrosting', 'require_activation', "Require Account Activation", "toggle", [0 => "Off", 1 => "On"]);
    $app->site->register('userfrosting', 'email_login', "Email Login", "toggle", [0 => "Off", 1 => "On"]);
    $app->site->register('userfrosting', 'resend_activation_threshold', "Resend Activation Email Cooloff (s)");
    $app->site->register('userfrosting', 'reset_password_timeout', "Password Recovery Timeout (s)");
    $app->site->register('userfrosting', 'create_password_expiration', "Create Password for New Users Timeout (s)");    
    $app->site->register('userfrosting', 'minify_css', "Minify CSS", "toggle", [0 => "Off", 1 => "On"]);
    $app->site->register('userfrosting', 'minify_js', "Minify JS", "toggle", [0 => "Off", 1 => "On"]);
}, 1);

// Register CSS and JS includes for the pages
$app->hook('includes.css.register', function () use ($app){
    // Register common CSS files
    $app->schema->registerCSS("common", "font-awesome-4.3.0.css");
    $app->schema->registerCSS("common", "font-starcraft.css");
    $app->schema->registerCSS("common", "bootstrap-3.3.2.css");
    $app->schema->registerCSS("common", "bootstrap-modal-bs3patch.css");   // Must be included BEFORE bootstrap-modal.css
    $app->schema->registerCSS("common", "bootstrap-modal.css");
    $app->schema->registerCSS("common", "lib/metisMenu.css");
    $app->schema->registerCSS("common", "bootstrap-custom.css");
    $app->schema->registerCSS("common", "bootstrap-switch.css");
    $app->schema->registerCSS("common", "tablesorter/theme.bootstrap.css");
    $app->schema->registerCSS("common", "tablesorter/jquery.tablesorter.pager.css");
    $app->schema->registerCSS("common", "select2/select2.css");
    $app->schema->registerCSS("common", "select2/select2-bootstrap.css");
    $app->schema->registerCSS("common", "bootstrapradio.css");
    
    // Dashboard CSS
    $app->schema->registerCSS("dashboard", "timeline.css");
    $app->schema->registerCSS("dashboard", "lib/morris.css");
    $app->schema->registerCSS("dashboard", "http://code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css");    
    
    // Logged-out CSS
    $app->schema->registerCSS("loggedout", "jumbotron-narrow.css");
    
}, 1);

$app->hook('includes.js.register', function () use ($app){
    // Register common JS files
    $app->schema->registerJS("common", "jquery-1.11.2.js");
    $app->schema->registerJS("common", "bootstrap-3.3.2.js");
    $app->schema->registerJS("common", "bootstrap-modal.js");
    $app->schema->registerJS("common", "bootstrap-modalmanager.js");    
    $app->schema->registerJS("common", "sb-admin-2.js");
    $app->schema->registerJS("common", "lib/metisMenu.js");
    $app->schema->registerJS("common", "jqueryValidation/jquery.validate.js");
    $app->schema->registerJS("common", "jqueryValidation/additional-methods.js");
    $app->schema->registerJS("common", "jqueryValidation/jqueryvalidation-methods-fortress.js");    
    $app->schema->registerJS("common", "moment.js");
    $app->schema->registerJS("common", "tablesorter/jquery.tablesorter.js");
    $app->schema->registerJS("common", "tablesorter/tables.js");
    $app->schema->registerJS("common", "tablesorter/jquery.tablesorter.pager.js");
    $app->schema->registerJS("common", "tablesorter/jquery.tablesorter.widgets.js");
    $app->schema->registerJS("common", "tablesorter/widgets/widget-sort2Hash.js");
    $app->schema->registerJS("common", "select2/select2.min.js");
    $app->schema->registerJS("common", "bootstrapradio.js");
    $app->schema->registerJS("common", "bootstrap-switch.js");
    $app->schema->registerJS("common", "handlebars-v1.2.0.js");    
    $app->schema->registerJS("common", "userfrosting.js");
    
    // Dashboard JS
    $app->schema->registerJS("dashboard", "lib/raphael.js");
    $app->schema->registerJS("dashboard", "lib/morris.js");
    
    // Users JS
    $app->schema->registerJS("user", "widget-users.js");
    
    // Groups JS
    $app->schema->registerJS("group", "widget-groups.js");

    // Auth JS
    $app->schema->registerJS("auth", "widget-auth.js");    
}, 1);  

/** Plugins */
// Run initialization scripts for plugins
$var_plugins = $app->site->getPlugins();
foreach($var_plugins as $var_plugin) {     
    require_once($app->config('plugins.path')."/".$var_plugin."/config-plugin.php");
}

// Hook for core and plugins to register includes
$app->applyHook("includes.css.register");
$app->applyHook("includes.js.register");
