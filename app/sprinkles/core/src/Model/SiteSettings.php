<?php

namespace UserFrosting\Sprinkle\Core\Model;

use \Illuminate\Database\Capsule\Manager as Capsule;

/**
 * SiteSettings Class
 *
 * A site settings database object for MySQL databases.
 *
 * @package UserFrosting
 * @author Alex Weissman
 * @link http://alexanderweissman.com
 *
 * @property string site_title The title of the site.  By default, displayed in the title tag, as well as the upper left corner of every user page.
 * @property string site_location The nation or state in which legal jurisdiction for this site falls.
 * @property string admin_email The administrative email for the site.  Automated emails, such as activation emails and password reset links, will come from this address.
 * @property int email_login 0|1 Specify whether users can login via email address or username instead of just username.
 * @property int can_register 0|1 Specify whether public registration of new accounts is enabled.
 * Enable if you have a service that users can sign up for, disable if you only want accounts to be created by you or an admin.
 * @property int enable_captcha 0|1 Specify whether new users must complete a captcha code when registering for an account.
 * @property int show_terms_on_register 0|1 Specify whether or not to show terms and conditions when registering.
 * @property int require_activation 0|1 Specify whether email activation is required for newly registered accounts.  Accounts created on the admin side never need to be activated.
 * @property int resend_activation_threshold The time, in seconds, that a user must wait before requesting that the activation email be resent.
 * @property int reset_password_timeout The time, in seconds, before a user's password reminder email expires.
 * @property string default_locale The default language for newly registered users.
 * @property int minify_css 0|1 Specify whether to use concatenated, minified CSS (production) or raw CSS includes (dev).
 * @property int minify_js 0|1 Specify whether to use concatenated, minified JS (production) or raw JS includes (dev).
 * @property string version The current version of UserFrosting.
 * @property string author The author of the site.  Will be used in the site's author meta tag.  
 */
class SiteSettings extends UFModel {    
    /**
     * @var string The id of the table for the current model.
     */ 
    protected static $_table_id = "configuration";   
    
    /**
     * @var array An array of UF environment variables.  Should be read-only.
     */
    protected $_environment;

    /**
     * @var array A list of plugin names => arrays of settings for that plugin.  The core plugin is "userfrosting".
     */    
    protected $_settings;
    
    /**
     * @var array A list of plugin names => arrays of descriptions for that plugin.  The core plugin is "userfrosting".
     */ 
    protected $_descriptions;
    
    /**
     * @var array A list of settings that have been registered to appear in the site settings interface.
     */ 
    protected $_settings_registered;

    /**
     * Construct the site settings object, loading values from the database.
     *
     * Fall back to default settings, if the configuration table cannot be loaded for one reason or another.
     * @param array $settings the default settings to use, if they can't be retrieved from the DB.
     * @param array $descriptions the default descriptions to use, if they can't be retrieved from the DB.
     */
    public function __construct($settings = [], $descriptions = []) {        
        $table_schema = Database::getSchemaTable(static::$_table_id);
        $this->table = $table_schema->name;
        $this->fillable = $table_schema->columns;        
        
        // Initialize UF environment
        $this->initEnvironment();
        
        // Set default settings first
        $this->_settings = $settings;
        $this->_descriptions = $descriptions;
        
        // Now, try to load settings from database if possible
        try {
            $results = $this->fetchSettings();
            // Merge, replacing default settings with DB settings as necessary.
            $this->_settings = array_replace_recursive($this->_settings, $results['settings']);
            $this->_descriptions = array_replace_recursive($this->_descriptions, $results['descriptions']);
            
            // If there are settings in this object that are not present in the database, go ahead and store them to the DB.
            if (!$this->isConsistent()){
                $this->store();
            }            
        } catch (\PDOException $e){
            error_log("The configuration table could not be loaded.  Falling back to default configuration settings.");
        }
    }
    
    /**
     * Determine whether or not all settings defined in this object are present in the database.
     *
     * @return boolean true if the table exists and all keys (core userfrosting and plugins) are defined in the table, false otherwise.
     */
    public function isConsistent(){
        
        $table_exists = count(static::queryBuilder()->select("SHOW TABLES LIKE '{$this->table}'")) > 0;
        
        if (!$table_exists){
            return false;
        }

        $db_data = $this->fetchSettings()['settings'];
        foreach ($this->_settings as $plugin => $setting){
            if (!isset($db_data[$plugin])){
                return false;
            }
            foreach ($setting as $name => $value){
                if (!isset($db_data[$plugin][$name])){
                    return false;
                }
            }
        }
        return true;
    }
    
    /**
     * Fetch the settings from the database.
     *
     * @return array An array of site settings, containing the name and description for each setting.
     */
    public function fetchSettings(){
        $rows = static::queryBuilder()->get();
                  
        $results = [];
        $results['settings'] = [];
        $results['descriptions'] = [];
        foreach ($rows as $row) {
            $name = $row['name'];
            $value = $row['value'];
            $plugin = $row['plugin'];
            $description = $row['description'];
            if (!isset($results['settings'][$plugin])) {
                $results['settings'][$plugin] = [];
                $results['descriptions'][$plugin] = [];
            }
                        
            $results['settings'][$plugin][$name] = $value;
            $results['descriptions'][$plugin][$name] = $description;          
        }
        return $results;
    }
    
    /**
     * Initialize the environment (non-persistent) variables for the app.  This includes things like the public root URL, css URLs, etc.
     *
     */    
    private function initEnvironment(){
        $this->_environment = [
            'uri' => Database::$app->config('uri')
        ];
    }
    
    /**
     * Magic isset to determine if a particular setting is defined in the environment or core userfrosting settings.
     * This does not check plugin settings or descriptions.
     *
     * @param string $name The name of the setting.
     * @return boolean true if $name is defined, false otherwise.
     */
    public function __isset($name) {
        if (isset($this->_environment[$name]) || isset($this->_settings['userfrosting'][$name]))
            return true;
        else
            return false;
    }
    
    /**
     * Magic setter to set the value of a core userfrosting setting.
     * This does not allow you to set the setting description.  To do that, you must use `set`.
     *
     * @param string $name The name of the setting.
     * @param string $value The value to assign the setting.
     */
    public function __set($name, $value){
        return $this->set('userfrosting', $name, $value);   
    }

    /**
     * Magic getter to get the value of an environment or core userfrosting setting.  This does not get plugin settings.  For that, you must use `get`.
     * This will first check if an environment setting of the specified $name exists and return it.  If not, it will then check if a DB setting of that name exists.
     *
     * @param string $name The name of the setting.
     * @return string the value of the setting.
     * @throws Exception The value does not exist in the environment or core settings.
     */
    public function __get($name){
        if ($name == "id") {
            return 0;
        } else if (isset($this->_environment[$name])){
            return $this->_environment[$name];
        } else if (isset($this->_settings['userfrosting'][$name])){
            return $this->_settings['userfrosting'][$name];
        } else {
            throw new \Exception("The value '$name' does not exist in the core userfrosting settings.");
        }
    }

    /**
     * Get a persistent setting value for a particular plugin.  Throws an exception if the plugin or value does not exist.
     *
     * @param string $name The name of the setting.
     * @param string $plugin The plugin scope of this setting.  Defaults to "userfrosting".
     * @throws Exception The value does not exist for this plugin.     
     */  
    public function get($name, $plugin = "userfrosting"){
        if (isset($this->_settings[$plugin]) && isset($this->_settings[$plugin][$name])){
            return $this->_settings[$plugin][$name];
        } else {
            throw new \Exception("The value '$name' does not exist in the settings for plugin '$plugin'.");
        }
    }
    
    /**
     * Get the description persistent setting value for a particular plugin.  Throws an exception if the plugin or description does not exist.
     *
     * @param string $name The name of the setting.
     * @param string $plugin The plugin scope of this setting.  Defaults to "userfrosting".
     * @throws Exception The description does not exist for this value of the specified plugin.     
     */ 
    public function getDescription($name, $plugin = "userfrosting"){
        if (isset($this->_settings[$plugin]) && isset($this->_descriptions[$plugin][$name])){
            return $this->_descriptions[$plugin][$name];
        } else {
            throw new \Exception("The value '$name' does not exist in the setting descriptions for plugin '$plugin'.");
        }
    }
    
    /**
     * Get a site environment (non-persistent) variable.  Throws an exception if the variable does not exist.
     *
     * @param string $name The name of the site environment variable.
     * @throws Exception The specified environment variable does not exist. 
     */  
    public function getEnvironment($name){
        if (isset($this->_environment[$name])){
            return $this->_environment[$name];
        } else {
            throw new \Exception("The value '$name' does not exist in the settings environment.");
        }
    }
    
    /**
     * Create/update a setting value.  If it exists, update, otherwise, create.  If updating, then a value or description set to null tells it to remain the same.  If creating, a value or description of null sets the field to an empty string.
     *
     * @param string $plugin The name of the plugin to associate this setting with.
     * @param string $name The name of the setting.
     */ 
    public function set($plugin, $name, $value = null, $description = null){
        if (!isset($this->_settings[$plugin])){
            $this->_settings[$plugin] = [];
            $this->_descriptions[$plugin] = [];
        }
        if ($value !== null) {
            $this->_settings[$plugin][$name] = $value; 
        } else {
            if (!isset($this->_settings[$plugin][$name]))
                $this->_settings[$plugin][$name] = ""; 
        }
        if ($description !== null) {
            $this->_descriptions[$plugin][$name] = $description; 
        } else {
            if (!isset($this->_descriptions[$plugin][$name]))
                $this->_descriptions[$plugin][$name] = ""; 
        }
    }
    
    /**
     * Register a setting to appear on the site settings page.
     *
     * @param string $plugin The name of the plugin that this setting is associated with.
     * @param string $name The name of the site setting.
     * @param string $label The label to display next to the site setting field.
     * @param string $type "text"|"readonly"|"toggle"|"select" The type of field - plain text, readonly, toggle switch, or dropdown select.
     * @param array $options If this field is a switch or dropdown, an associative array of values => labels to be presented.  Switches should have values "0" and "1".
     * @throws Exception The specified plugin or site setting does not exist.
     */
    public function register($plugin, $name, $label, $type = "text", $options = []){
        // Get the array of settings & descriptions
        if (isset($this->_settings[$plugin])){
            $settings = $this->_settings[$plugin];
            $descriptions = $this->_descriptions[$plugin];      
        } else {
            throw new \Exception("The plugin '$plugin' does not have any site settings.  Be sure to add them first by calling set().");
        }
        
        if (!isset($settings[$name])){
            throw new \Exception("The plugin '$plugin' does not have a value for '$name'.  Please add it first by calling set().");
        }
        
        // Check type
        if (!in_array($type, ["readonly", "text", "toggle", "select"]))
            throw new \Exception("Type must be one of 'readonly', 'text', 'toggle', or 'select'.");
            
        if (!isset($this->_settings_registered[$plugin]))
            $this->_settings_registered[$plugin] = [];
        if (!isset($this->_settings_registered[$plugin][$name]))
            $this->_settings_registered[$plugin][$name] = [];
            
        $this->_settings_registered[$plugin][$name]['label'] = $label;
        $this->_settings_registered[$plugin][$name]['type'] = $type;
        $this->_settings_registered[$plugin][$name]['options'] = $options;
        $this->_settings_registered[$plugin][$name]['description'] = $descriptions[$name];
    }
    
    /**
     * Get an array of site settings that have been registered for display on the site settings page.
     *
     * The result is a multidimensional array indexed first by plugin name, then by setting name.  Each setting name will then have an array containing values for "value", "label", "type", "options", and "description".
     * For example, $settings['userfrosting']['site_title']['value'] = "UserFrosting".
     * @return array The array of site settings.
     */   
    public function getRegisteredSettings(){
        foreach ($this->_settings_registered as $plugin => $setting){
            foreach ($setting as $name => $params){
                $this->_settings_registered[$plugin][$name]['value'] = $this->_settings[$plugin][$name];
            }
        }
        return $this->_settings_registered;
    }
    
    /**
     * Get an array of available locales for UserFrosting by scanning the path specified in $app->config('locales.path').
     *
     * @return array An array containing the names of all available locales (e.g. "en_EN", "es_ES", etc.)
     */  
    public function getLocales(){
    	$directory = static::$app->config('locales.path');
        $languages = glob($directory . "/*.php");
        $results = [];
        foreach ($languages as $language){
            $basename = basename($language, ".php");
            $results[$basename] = $basename;
        }
        return $results;
    }

    /**
     * Get an array of available themes for UserFrosting by scanning the path specified in $app->config('themes.path').
     *
     * @return array An array containing the names of all available themes (e.g. "default", "root", etc.)
     */     
    public function getThemes(){
    	$directory = static::$app->config('themes.path');
        $themes = glob($directory . "/*", GLOB_ONLYDIR);
        $results = [];
        foreach ($themes as $theme){
            $basename = basename($theme);
            $results[$basename] = $basename;
        }
        return $results;
    }
    
    /**
     * Get an array of installed plugins by scanning the path specified in $app->config('plugins.path').
     *
     * @return array An array containing the names of all available themes (e.g. "oauth", "datatables", etc.)
     */      
    public function getPlugins(){
    	$directory = static::$app->config('plugins.path');
        $themes = glob($directory . "/*", GLOB_ONLYDIR);
        $results = [];
        foreach ($themes as $theme){
            $basename = basename($theme);
            $results[$basename] = $basename;
        }
        return $results;
    }
    
    /**
     * Get an array of system information for UserFrosting.
     *
     * @return array An array containing a list of information, such as software version, application path, etc.
     */ 
    public function getSystemInfo(){
        $results = [];
        $results['UserFrosting Version'] = $this->version;
        $results['Web Server'] = $_SERVER['SERVER_SOFTWARE'];
        $results['PHP Version'] = phpversion();
        $dbinfo = Database::getInfo();
        $results['Database Version'] = $dbinfo['db_type'] . " " .  $dbinfo['db_version'];
        $results['Database Name'] = $dbinfo['db_name'];
        $results['Table Prefix'] = $dbinfo['table_prefix'];
        $environment = static::$app->environment();
        $results['Application Root'] = static::$app->config('base.path');
        $results['Document Root'] = $this->uri['public'];
        return $results;
    }
    
    /**
     * Get the PHP error log as an array of lines.
     *
     * @param int $targetLines the number of lines to display.  Set to `null` to display all lines.
     * @param int $seekLen the number of bytes to grab at a time.
     * @return array An array containing 'path', which is the path of the PHP error log, and 'messages', which is an array of error messages sorted with the newest messages first.
     */
    static public function getLog($targetLines = null, $seekLen = 4096){
        // Check if error logging is enabled
        if (!ini_get("error_log")){
            $path = "Unavailable";
            $messages = ["You do not seem to have an error log set up.  Please check your php.ini file."];
        } else {
            if (!ini_get("log_errors")){
                $path = ini_get('error_log');
                $messages = ["Error logging appears to be disabled.  Please check your php.ini file."];
            } else {
                $path = ini_get('error_log');
                @$file = file($path);
                if (!$targetLines){
                    /* If they want all lines, give it to them */
                    @$file = file($path);
                    $messages = $messages = array_reverse($file);
                } else {

                    /** If they want a specific number of lines, seek
                     *  back from the end of the file, grabbing lines
                     *  as we go until we reach count.
                     *
                     * @var array $messages Log lines in reverse order
                     * @var int $linesRead Count of good lines stored to $messages
                     * @var int $targetLines Count of lines we want to read in total
                     * @var resource $fileHandle Log file handle
                     * @var int $sizeRemaining Bytes of file left to read
                     * @var int $seekLen Amount of bytes to read at a time
                     * @var string $remainder End of file remaining after previous loop
                     * @var string $current Current buffer chunk from file (plus remainder)
                     * @var array $curArray Current buffer chunk split by EOLs
                     * @var int $curLines Lines we still want to read from current buffer
                     */

                    $messages = [];
                    $fileHandle = fopen($path, 'r');
                    fseek($fileHandle, 0, SEEK_END);
                    $sizeRemaining = filesize($path);
                    $linesRead = 0;

                    /* If the end of the file is whitespace, discard
                       it. The remainder left over will be attached
                       to the back of the next line we read.          */
                    $remainder = ' ';
                    while (ctype_space($remainder) && $sizeRemaining){
                        fseek($fileHandle, -1, SEEK_CUR);
                        $remainder = fread($fileHandle, 1);
                        fseek($fileHandle, -1, SEEK_CUR);
                        $sizeRemaining -= 1;
                    }


                    while ($linesRead < $targetLines){

                        /* If there's no file left to read, return with
                           what we have. If the amount we want to read
                           is more than we have left, just take what's left. */
                        if ($sizeRemaining == 0){
                            break 1;
                        } elseif ($seekLen > $sizeRemaining){
                            if ($sizeRemaining < 0){
                                $sizeRemaining = 0;
                            }
                            $seekLen = $sizeRemaining;
                            $sizeRemaining = 0;
                        }
                        fseek($fileHandle, -$seekLen, SEEK_CUR); // Seek to the point we want to read from
                        $current = fread($fileHandle, $seekLen) . $remainder; // Attach the remainder from previous loop
                        fseek($fileHandle, -$seekLen, SEEK_CUR); // Reset back to same point after reading
                        $sizeRemaining -= $seekLen;
                        $curArray = explode(PHP_EOL, $current);
                        $curLines = count($curArray) - 1;

                        /* Take the buffer we've read and get as
                           many complete lines as we can from it. */
                        while ($curLines > 0){
                            $line = array_pop($curArray);
                            if (trim($line) !== ''){
                                $messages[] = $line;
                                $linesRead++;
                            }
                            $curLines--;
                            /* If we've got the lines we want,
                               break out of both while loops   */
                            if ($linesRead == $targetLines){
                                break 2;
                            }


                        }
                        /* Store the remainder for the next loop */
                        $remainder = $curArray[0];
                        /* If there's nothing left to grab,
                           break out of the outer while loop
                           with what we already have         */
                        if (($sizeRemaining == 0) && ($curLines == 0) && ($linesRead < $targetLines)){
                            $messages[] = $curArray[0];
                            break 1;
                        }
                    }
                }
            }
        }
        return [
            "path"      => $path,
            "messages"  => $messages
        ];
    }
    
    /**
     * Store the site settings from this object to the database, inserting new records when necessary and updating existing records otherwise.
     *
     * @todo use Eloquent to make these queries database-agnostic
     */ 
    public function save(array $options = []){
        // Get current values as stored in DB
        $db_settings = $this->fetchSettings();
        
        $db = Capsule::connection()->getPdo();
        
        $table = $this->table;
        
        $stmt_insert = $db->prepare("INSERT INTO `$table`
            (plugin, name, value, description)
            VALUES (:plugin, :name, :value, :description);");
        
        $stmt_update = $db->prepare("UPDATE `$table` SET
            value = :value,
            description = :description 
            WHERE plugin = :plugin and name = :name;");
        
        // For each setting in this object, check if it exists in DB.  If it does not exist, add.  If it exists and is different from the current value, update.
        foreach ($this->_settings as $plugin => $setting){
            foreach ($setting as $name => $value){
                $sqlVars = [
                    ":plugin" => $plugin,
                    ":name" => $name,
                    ":value" => $value,
                    ":description" => $this->_descriptions[$plugin][$name]
                ];
                if (!isset($db_settings['settings'][$plugin]) || !isset($db_settings['settings'][$plugin][$name])){
                    $stmt_insert->execute($sqlVars);
                } else if (($db_settings['settings'][$plugin][$name] !== $this->_settings[$plugin][$name]) || ($db_settings['descriptions'][$plugin][$name] !== $this->_descriptions[$plugin][$name])){
                    $stmt_update->execute($sqlVars);
                }
            }
        }
        
        return true;   
    }
}
