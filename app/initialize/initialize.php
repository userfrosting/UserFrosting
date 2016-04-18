<?php
/**
 * UserFrosting site initialization file.  Handles setup for database, site settings, JS/CSS includes, etc.
 *
 * @author Alex Weissman
 * @link http://www.userfrosting.com
 */

    // Set enumerative values
    defined("GROUP_NOT_DEFAULT") or define("GROUP_NOT_DEFAULT", 0);
    defined("GROUP_DEFAULT") or define("GROUP_DEFAULT", 1);
    defined("GROUP_DEFAULT_PRIMARY") or define("GROUP_DEFAULT_PRIMARY", 2);
 
    $app = \UserFrosting\UserFrosting::getInstance();
 
    $app->hook('includes.model.register', function () use ($app) {
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
    }, 1);
 
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

    $app->hook('routes.common.notfound', function () use ($app) {
        /**
         * Renders the 404 error page.
         *
         * This page shows the 404 "page not found" page.
         * Request type: GET
         */
        $app->notFound(function () use ($app) {
            if ($app->request->isGet()) {
                $app->render('errors/404.twig');
            } else {
                $app->alerts->addMessageTranslated("danger", "SERVER_ERROR");
            }
        });
    });
    