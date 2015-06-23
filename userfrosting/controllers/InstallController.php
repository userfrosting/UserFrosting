<?php

namespace UserFrosting;

// Handles installation-related activities
class InstallController extends \UserFrosting\BaseController {

    public function pageSetupDB(){
        $messages = [];
        // 1. Check PHP version
        
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
        
        // 3. Check database connection
        
        if (!Database::testConnection()){
            $messages[] = [
                "title" => "We couldn't connect to your database.",
                "message" => "Make sure that your database is properly configured in <code>config-userfrosting.php</code>, and that you have selected the correct configuration mode ('dev' or 'production').  Also, make sure that your database user has the proper privileges to connect to the database."
            ]; 
        } 
        
        $tables = Database::getTables();
        if (count($tables) > 0){
            $messages[] = [
                "title" => "One or more tables already exist.",
                "message" => "The following tables already exist in the database: <strong>" . implode(", ", $tables) . "</strong>.  Do you already have another installation of UserFrosting in this database?  Please either create a new database (recommended), or change the table prefix in <code>config-userfrosting.php</code> if you cannot create a new database."
            ]; 
        }
        
        if (count($messages) > 0){
            $this->_app->render('common/install/install-errors.html', [
                'page' => [
                    'author' =>         $this->_app->site->author,
                    'title' =>          "Installation Error",
                    'description' =>    "Installation page for UserFrosting",
                    'alerts' =>         $this->_app->alerts->getAndClearMessages()
                ],
                "messages" => $messages
            ]);
        } else {
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
            
            $this->_app->render('common/install/install-ready.html', [
                'page' => [
                    'author' =>         $this->_app->site->author,
                    'title' =>          "Installation",
                    'description' =>    "Installation page for UserFrosting",
                    'alerts' =>         $this->_app->alerts->getAndClearMessages()
                ],
                "messages" => $messages
            ]);        
        }
    }
    
    public function pageSetupMasterAccount(){
        
        // Get the alert message stream
        $ms = $this->_app->alerts;
        
        // Do not allow registering a master account if one has already been created     
        if (UserLoader::exists($this->_app->config('user_id_master'))){
            $ms->addMessageTranslated("danger", "MASTER_ACCOUNT_EXISTS");
            $this->_app->redirect($this->_app->urlFor('uri_home'));
        }
        
        $schema = new \Fortress\RequestSchema($this->_app->config('schema.path') . "/forms/register.json");
        $validators = new \Fortress\ClientSideValidator($schema, $this->_app->translator);   
        
        $this->_app->render('common/install/install-master.html', [
            'page' => [
                'author' =>         $this->_app->site->author,
                'title' =>          "Installation | Register Master Account",
                'description' =>    "Set up the master account for your installation of UserFrosting",
                'alerts' =>         $this->_app->alerts->getAndClearMessages()
            ],
            'validators' => $validators->formValidationRulesJson(),
            'table_config' => Database::getTableConfiguration()
        ]);    
    }

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
        if (UserLoader::exists($this->_app->config('user_id_master'))){
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
        $data['user_name'] = strtolower(trim($data['user_name']));
        $data['display_name'] = trim($data['display_name']);
        $data['email'] = strtolower(trim($data['email']));
        $data['active'] = 1;
        $data['locale'] = $this->_app->site->default_locale;
                
        // Halt on any validation errors
        if ($error) {
            $this->_app->halt(400);
        }
    
        // Get default primary group (is_default = GROUP_DEFAULT_PRIMARY)
        $primaryGroup = GroupLoader::fetch(GROUP_DEFAULT_PRIMARY, "is_default");
        $data['primary_group_id'] = $primaryGroup->id;
        // Set default title for new users
        $data['title'] = $primaryGroup->new_user_title;
        // Hash password
        $data['password'] = Authentication::hashPassword($data['password']);
            
        // Create the user
        $user = new User($data, $this->_app->config('user_id_master'));

        // Add user to default groups, including default primary group
        $defaultGroups = GroupLoader::fetchAll(GROUP_DEFAULT, "is_default");
        $user->addGroup($primaryGroup->id);
        foreach ($defaultGroups as $group_id => $group)
            $user->addGroup($group_id);    
        
        // Store new user to database, forcing it to insert the new user
        $user->store(true);
        // No activation required
        $ms->addMessageTranslated("success", "ACCOUNT_REGISTRATION_COMPLETE_TYPE1");
        
        // Update install status
        $this->_app->site->install_status = "new";
        $this->_app->site->root_account_config_token = "";
        $this->_app->site->store();
    }
    
}

?>
