<?php

namespace UserFrosting;

/**
 * InstallController Class
 *
 * Controller class for /install/* URLs.  Handles activities for installing UserFrosting.  Not needed after installation is complete.
 *
 * @package UserFrosting
 * @author Alex Weissman
 * @link http://www.userfrosting.com/navigating/#structure
 */
class InstallController extends \UserFrosting\BaseController {

    /**
     * Renders the initial page that comes up when you first install UserFrosting.
     *
     * This page performs the following steps:
     * 1. Check that the current version of PHP is adequate.
     * 2. Check that PDO is installed and enabled.
     * 3. Check that we can connect to the database, as configured in `config-userfrosting.php`.
     * 4. Check that the database tables have not already been created.
     * 5. If all of these checks are passed, set up the initial tables by calling `Database::install()`.
     * This page is "public access".
     * Request type: GET
     * @see MySqlDatabase::install()
     */
    public function pageSetupDB(){
        $messages = [];
        // 1. Check PHP version

        error_log("Checking php version");
        // PHP_VERSION_ID is available as of PHP 5.2.7, if our version is lower than that, then emulate it
        if (!defined('PHP_VERSION_ID')) {
            $version = explode('.', PHP_VERSION);
            define('PHP_VERSION_ID', ($version[0] * 10000 + $version[1] * 100 + $version[2]));
        }
        if (PHP_VERSION_ID < 50400){
            $messages[] = [
                "title" => "You need to upgrade your PHP installation.",
                "message" => "I'm sorry, UserFrosting relies on numerous features of PHP that are only available in PHP 5.4 or later.  Please upgrade your version of PHP, or contact your web hosting service and ask them to upgrade it for you."
            ];
        }

        // 2. Check that PDO is installed and enabled
        if (!class_exists('PDO')){
            $messages[] = [
                "title" => "PDO is not installed.",
                "message" => "I'm sorry, you must have PDO installed and enabled in order for UserFrosting to access the database.  If you don't know what PDO is, please see <a href='http://php.net/manual/en/book.pdo.php'>http://php.net/manual/en/book.pdo.php</a>.  You must also have MySQL version 4.1 or higher installed, since UserFrosting relies on native prepared statements."
            ];
        }

        error_log("Checking db connection");
        // 3. Check database connection
        if (!Database::testConnection()){
            $messages[] = [
                "title" => "We couldn't connect to your database.",
                "message" => "Make sure that your database is properly configured in <code>config-userfrosting.php</code>, and that you have selected the correct configuration mode ('dev' or 'production').  Also, make sure that your database user has the proper privileges to connect to the database."
            ];
        }

        error_log("Checking any current tables");
        $tables = Database::getCreatedTables();
        if (count($tables) > 0){
            $messages[] = [
                "title" => "One or more tables already exist.",
                "message" => "The following tables already exist in the database: <strong>" . implode(", ", $tables) . "</strong>.  Do you already have another installation of UserFrosting in this database?  Please either create a new database (recommended), or change the table prefix in <code>config-userfrosting.php</code> if you cannot create a new database."
            ];
        }
        error_log("Done with checks");
        if (count($messages) > 0){
            $this->_app->render('install/install-errors.twig', [
                "messages" => $messages
            ]);
        } else {
        error_log("Installing");
            // Create tables
            Database::install();

            $messages[] = [
                "title" => "<i class='fa fa-lock'></i> PDO is installed.",
                "message" => "No need to worry about any pesky SQL injection attacks!",
                "class" => "success"
            ];

            $messages[] = [
                "title" => "<i class='fa fa-database'></i> Database connection",
                "message" => "Hooray!  We were able to connect to your database and create the core tables for UserFrosting.",
                "class" => "success"
            ];
            if (PHP_VERSION_ID >= 50400 && PHP_VERSION_ID < 50500){
               $messages[] = [
                    "title" => "<i class='fa fa-warning'></i> PHP version",
                    "message" => "You currently have version " . PHP_VERSION . " of PHP installed.  We recommend version 5.5 or later.  UserFrosting can still be installed, but we highly recommend you upgrade soon.",
                    "class" => "warning"
                ];
            } else {
                $messages[] = [
                    "title" => "<i class='fa fa-check'></i> PHP version",
                    "message" => "You currently have version " . PHP_VERSION . " of PHP installed.  Good job!",
                    "class" => "success"
                ];
            }

            // Check for GD library (required for Captcha)
            if (!(extension_loaded('gd') && function_exists('gd_info'))) {
                $messages[] = [
                    "title" => "<i class='fa fa-warning'></i> GD library not installed",
                    "message" => "We could not confirm that the <code>GD</code> library is installed and enabled.  GD is an image processing library that UserFrosting uses to generate captcha codes for user account registration.  If you don't need captcha, you can disable it in Site Settings and ignore this message.  Otherwise, please see the <a href='http://www.userfrosting.com/troubleshooting/' target='_blank'>troubleshooting guide</a> for information on installing and configuring GD.",
                    "class" => "warning"
                ];
            } else {
                if (!function_exists('imagepng')){
                    $messages[] = [
                        "title" => "<i class='fa fa-warning'></i> PNG operations not available",
                        "message" => "The <code>GD</code> library is installed and enabled, but PNG functions do not seem to be available.  UserFrosting uses the PNG functions of GD, an image processing library, to generate captcha codes for user account registration.  If you don't need captcha, you can disable it in Site Settings and ignore this message.  Otherwise, please see the <a href='http://www.userfrosting.com/troubleshooting/' target='_blank'>troubleshooting guide</a> for information on updating GD to support PNG operations.",
                        "class" => "warning"
                    ];
                }
            }

            $this->_app->render('install/install-ready.twig', [
                "messages" => $messages
            ]);
        }
    }

    /**
     * Renders the page for creating the master account.
     *
     * This page performs the following steps:
     * 1. Check that the master account doesn't already exist.  If it does, redirect to the home page.
     * 2. This page features a "configuration token" as a security feature, to prevent malicious agents from intercepting
     * an in-progress installation and setting themselves as the master account.  This requires the developer to look
     * in the configuration table of the database.
     * Request type: GET
     */
    public function pageSetupMasterAccount(){

        // Get the alert message stream
        $ms = $this->_app->alerts;

        // Do not allow registering a master account if one has already been created
        if (User::find($this->_app->config('user_id_master'))){
            $ms->addMessageTranslated("danger", "MASTER_ACCOUNT_EXISTS");
            $this->_app->redirect($this->_app->urlFor('uri_home'));
        }

        $schema = new \Fortress\RequestSchema($this->_app->config('schema.path') . "/forms/register.json");
        $this->_app->jsValidator->setSchema($schema);

        $this->_app->render('install/install-master.twig', [
            'validators' => $this->_app->jsValidator->rules(),
            'table_config' => Database::getSchemaTable('configuration')->name
        ]);
    }

    /**
     * Processes a request to create the master account.
     *
     * Processes the request from the master account creation form, checking that:
     * 1. The honeypot has not been changed;
     * 2. The master account does not already exist;
     * 3. The correct configuration token was submitted;
     * 3. The submitted data is valid.
     * This route is "public access" (until the master account has been created, that is)
     * Request type: POST
     */
    public function setupMasterAccount(){
        $post = $this->_app->request->post();

        // Get the alert message stream
        $ms = $this->_app->alerts;

        // Check the honeypot. 'spiderbro' is not a real field, it is hidden on the main page and must be submitted with its default value for this to be processed.
        if (!$post['spiderbro'] || $post['spiderbro'] != "http://"){
            error_log("Possible spam received:" . print_r($this->_app->request->post(), true));
            $ms->addMessage("danger", "Aww hellllls no!");
            $this->_app->halt(500);     // Don't let on about why the request failed ;-)
        }

        // Do not allow registering a master account if one has already been created
        if (User::find($this->_app->config('user_id_master'))){
            $ms->addMessageTranslated("danger", "MASTER_ACCOUNT_EXISTS");
            $this->_app->halt(403);
        }

        // Check the configuration token
        if ($post['root_account_config_token'] != $this->_app->site->root_account_config_token) {
            $ms->addMessageTranslated("danger", "CONFIG_TOKEN_MISMATCH");
            $this->_app->halt(403);
        }

        // Load the request schema
        $requestSchema = new \Fortress\RequestSchema($this->_app->config('schema.path') . "/forms/register.json");

        // Set up Fortress to process the request
        $rf = new \Fortress\HTTPRequestFortress($ms, $requestSchema, $post);

        // Sanitize data
        $rf->sanitize();

        // Validate, and halt on validation errors.
        $error = !$rf->validate(true);

        // Get the filtered data
        $data = $rf->data();

        // Remove configuration token, password confirmation from object data
        $rf->removeFields(['root_account_config_token', 'passwordc']);

        // Perform desired data transformations.  Is this a feature we could add to Fortress?
        $data['display_name'] = trim($data['display_name']);
        $data['flag_verified'] = 1;
        $data['locale'] = $this->_app->site->default_locale;

        // Halt on any validation errors
        if ($error) {
            $this->_app->halt(400);
        }

        // Get default primary group (is_default = GROUP_DEFAULT_PRIMARY)
        $primaryGroup = Group::where('is_default', GROUP_DEFAULT_PRIMARY)->first();
        $data['primary_group_id'] = $primaryGroup->id;
        // Set default title for new users
        $data['title'] = $primaryGroup->new_user_title;
        // Hash password
        $data['password'] = Authentication::hashPassword($data['password']);

        // Create the master user
        $user = new User($data);
        $user->id = $this->_app->config('user_id_master');

        // Add user to default groups, including default primary group
        $defaultGroups = Group::where('is_default', GROUP_DEFAULT)->get();
        $user->addGroup($primaryGroup->id);
        foreach ($defaultGroups as $group) {
            $group_id = $group->id;
            $user->addGroup($group_id);
        }

        // Add sign-up event
        $user->newEventSignUp();

        // Store new user to database
        $user->save();

        // No activation required
        $ms->addMessageTranslated("success", "ACCOUNT_REGISTRATION_COMPLETE_TYPE1");

        // Update install status
        $this->_app->site->install_status = "new";
        $this->_app->site->root_account_config_token = "";
        $this->_app->site->store();
    }

}
