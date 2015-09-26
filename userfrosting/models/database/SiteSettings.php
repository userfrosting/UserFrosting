<?php

namespace UserFrosting;

use \Illuminate\Database\Capsule\Manager as Capsule;

/**
 * MySqlSiteSettings Class
 *
 * A site settings database object for MySQL databases.
 *
 * @see DatabaseInterface
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
     * @see DatabaseInterface
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
     * @see DatabaseInterface
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
     * @see DatabaseInterface
     */
    public function __isset($name) {
        if (isset($this->_environment[$name]) || isset($this->_settings['userfrosting'][$name]))
            return true;
        else
            return false;
    }
    
    /**
     * @see DatabaseInterface
     */
    public function __set($name, $value){
        return $this->set('userfrosting', $name, $value);   
    }

    /**
     * @see DatabaseInterface
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
     * @see DatabaseInterface
     */
    public function get($name, $plugin = "userfrosting"){
        if (isset($this->_settings[$plugin]) && isset($this->_settings[$plugin][$name])){
            return $this->_settings[$plugin][$name];
        } else {
            throw new \Exception("The value '$name' does not exist in the settings for plugin '$plugin'.");
        }
    }
    
    /**
     * @see DatabaseInterface
     */
    public function getDescription($name, $plugin = "userfrosting"){
        if (isset($this->_settings[$plugin]) && isset($this->_descriptions[$plugin][$name])){
            return $this->_descriptions[$plugin][$name];
        } else {
            throw new \Exception("The value '$name' does not exist in the setting descriptions for plugin '$plugin'.");
        }
    }
    
    /**
     * @see DatabaseInterface
     */
    public function getEnvironment($name){
        if (isset($this->_environment[$name])){
            return $this->_environment[$name];
        } else {
            throw new \Exception("The value '$name' does not exist in the settings environment.");
        }
    }
    
    /**
     * @see DatabaseInterface
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
     * @see DatabaseInterface
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
     * @see DatabaseInterface
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
     * @see DatabaseInterface
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
     * @see DatabaseInterface
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
     * @see DatabaseInterface
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
     * @see DatabaseInterface
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
     * @see DatabaseInterface
     */
    public function getLog($lines = null){
        // Check if error logging is enabled
        if (!ini_get("error_log")){
            $path = "Unavailable";
            $messages = ["You do not seem to have an error log set up.  Please check your php.ini file."];
        } else if (!ini_get("log_errors")){
            $path = ini_get('error_log');
            $messages = ["Error logging appears to be disabled.  Please check your php.ini file."];
        } else {
            $path = ini_get('error_log');
            @$file = file($path);
            if (!$file) {
                $messages = ["No error log found."];
            } else {
                if ($lines){
                    $messages = array_reverse(array_slice($file, -$lines));
                } else {
                    $messages = array_reverse($file);
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
    public function save(){
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
