<?php

namespace UserFrosting;

/**
 * DatabaseInterface.php
 *
 * These define the interfaces for the database object interface.
 * Any other implementations you write for the model MUST implement these interfaces.
 */

/**
 * DatabaseInterface Interface
 *
 * Represents a generic static class for connecting to a database.
 *
 * @package UserFrosting
 * @author Alex Weissman
 * @link http://alexanderweissman.com
 */
interface DatabaseInterface {

    /**
     * Creates a fresh connection to the database.
     */
    public static function connection();
    
    /**
     * Test whether a DB connection can be established.
     *
     * @return bool true if the connection can be established, false otherwise.
     */
    public static function testConnection();
    
    /**
     * Get an array of key-value pairs containing basic information about this database.
     *
     * The site settings module expects the following key-value pairs:
     * db_type, db_version, db_name, table_prefix
     * @return array[string] the properties of this database.
     */
    public static function getInfo();

    /**
     * Get an array of the names of tables that exist in the database.
     *
     * Looks for tables with the following handles: user, group, group_user, authorize_group, authorize_user
     * @return array[string] the names of the UF tables that actually exist.
     */    
    public static function getCreatedTables();
    
    /**
     * Set up the initial tables for the database.
     *
     * Creates all tables, and loads the configuration table with the default config data.  Also, sets install_status to `pending`.
     */   
    public static function install();
}

/**
 * A static class is responsible for retrieving user and group authorization object(s) from the database, etc.
 *
 * @package UserFrosting
 * @author Alex Weissman
 * @see http://www.userfrosting.com/components/#authorization
 */
interface AuthLoaderInterface {

    /**
     * Fetch all authorization rules associated directly with a specified User from the database for a given hook.
     *
     * @param int $user_id the id of the user.
     * @param string $hook the authorization hook to match.
     * @return array An array of rows from the user authorization table.
     */
    public static function fetchUserAuthHook($user_id, $hook);

    /**
     * Fetch all authorization rules associated with a specified Group from the database for a given hook.
     *
     * @param int $group_id the id of the group.
     * @param string $hook the authorization hook to match.
     * @return array An array of rows from the group authorization table.
     */   
    public static function fetchGroupAuthHook($group_id, $hook);
}

/**
 * ObjectLoaderInterface Interface
 *
 * Represents a generic static class for loading an object from the database.
 *
 * @package UserFrosting
 * @author Alex Weissman
 * @see http://www.userfrosting.com/tutorials/lesson-3-data-model/
 */
interface ObjectLoaderInterface {
    
    /**
     * Set table and columns for this class.  Kinda hacky but I don't see any other way to do it.
     *
     * @param DatabaseTable $table The table information object.
     */    
    public static function init($table);
    
    /**
     * Determine whether or not a record exists in the table for the given value.
     *
     * @param string $value The value to look up in the table for this type of object.
     * @param string $name The column to use when looking up $value.  Defaults to `id`.
     * @return boolean True if the record exists, false otherwise.
     */  
    public static function exists($value, $name = "id");

    /**
     * Fetch a single record from a table into the corresponding object.
     *
     * @param string $value The value to look up in the table for this type of object.
     * @param string $name The column to use when looking up $value.  Defaults to `id`.
     * @return DatabaseObjectInterface|false The object, or false if it doesn't exist.
     */  
    public static function fetch($value, $name = "id");

    /**
     * Fetch a set of records from a table into an array of the corresponding object.
     *
     * @param string $value[optional] The value to look up in the table for this type of object.
     * @param string $name[optional] The column to use when looking up $value.  Defaults to `id`.
     * @return DatabaseObjectInterface[] An array of objects that match the criteria.  Returns empty array if no matching objects were found.
     */  
    public static function fetchAll($value = null, $name = null);
}

/**
 * DatabaseObjectInterface Interface
 *
 * Represents a generic database object (User, Group, Spaceship, etc) represented by a particular row in a table.
 *
 * @package UserFrosting
 * @author Alex Weissman
 * @see http://www.userfrosting.com/tutorials/lesson-3-data-model/
 * @todo expand fetch functions to support arbitrary filtering, perhaps allowing for with() type clauses that support arbitary SQL
 */
interface DatabaseObjectInterface {
    
    /**
     * Get the DatabaseTable object that was assigned in the constructor of the concrete child class.
     *
     * @return DatabaseTable
     */
    public function table();
      
    /**
     * Determine if the property for this object exists.
     *
     * Properties of a DatabaseObject include the id and the column names as defined in the associated DatabaseTable.
     * Other properties may also be defined by the specific child classes.
     * @param string the name of the property to check.
     * @return bool true if the property is defined, false otherwise.
     */
    public function __isset($name);
    
    /**
     * Get a property for this object.
     *
     * Properties of a DatabaseObject include the id and the column names as defined in the associated DatabaseTable.
     * Other properties may also be defined by the specific child classes.
     * @param string the name of the property to retrieve.
     * @throws Exception the property does not exist for this object.
     * @return string the associated property.
     */
    public function __get($name);
    
    /**
     * Set a property for this object.
     *
     * Checks that the property, as defined in the DatabaseTable columns, is permitted.
     * This implements whitelisting, protecting the database against SQL injection attacks.
     * Other properties may also be defined by the specific child classes.
     * @param string the name of the property to set.
     * @param string the value to set the property to.
     * @throws Exception the property does not exist for this object.
     * @return string the associated property.
     */
    public function __set($name, $value);  
    
    /**
     * Refresh the object from the DB.
     *
     * @todo Should this just update the internal contents of this object, rather than create a new one?
     */
    public function fresh();
    
    /**
     * Get the properties of this object as an associative array.
     *
     * @return array
     */  
    public function export();
    
    /**
     * Store the object in the DB, creating a new row if one doesn't already exist.
     *
     * @return int the id of this object.
     */ 
    public function store();
    
    /**
     * Delete the object from the database, if it exists.
     *
     * @return bool true if the deletion was successful, false otherwise.
     */
    public function delete();    
}

/**
 * UserLoaderInterface Interface
 *
 * Represents a static class for loading User object(s) from the database, checking for existence, etc.
 *
 * @package UserFrosting
 * @author Alex Weissman
 * @see http://www.userfrosting.com/tutorials/lesson-3-data-model/
 */
interface UserLoaderInterface {

    /**
     * Generate an activation token for a user.
     *
     * This generates a token to use for activating a new account, resetting a lost password, etc.
     * @param string $gen specify an existing token that, if we happen to generate the same value, we should regenerate on.
     * @return string
     */
    public static function generateActivationToken($gen = null);
}

/**
 * GroupLoaderInterface Interface
 *
 * Represents a static class for loading Group object(s) from the database, checking for existence, etc.
 *
 * @package UserFrosting
 * @author Alex Weissman
 * @see http://www.userfrosting.com/tutorials/lesson-3-data-model/
 */
interface GroupLoaderInterface {

}

/**
 * GroupObjectInterface Interface
 *
 * Represents a group object as stored in the database.
 *
 * @package UserFrosting
 * @author Alex Weissman
 * @see http://www.userfrosting.com/tutorials/lesson-3-data-model/
 *
 * @property string name
 * @property string theme
 * @property string landing_page
 * @property string new_user_title
 * @property string icon
 * @property bool is_default
 * @property bool can_delete
 */
interface GroupObjectInterface {
    /**
     * Lazily load a collection of Users which belong to this group.
     * @todo implement this!
     */
    public function getUsers();
}

/**
 * UserObjectInterface Interface
 *
 * Represents a User object as stored in the database.
 *
 * @package UserFrosting
 * @author Alex Weissman
 * @see http://www.userfrosting.com/tutorials/lesson-3-data-model/
 *
 * @property string user_name
 * @property string display_name
 * @property string email
 * @property string password
 * @property string title
 * @property int activation_token
 * @property datetime last_activation_request
 * @property int lost_password_request
 * @property datetime lost_password_timestamp
 * @property int active
 * @property datetime sign_up_stamp
 * @property datetime last_sign_in_stamp
 * @property int enabled
 * @property int primary_group_id
 * @property string locale
 */
interface UserObjectInterface {
    
    /**
     * Determine whether or not this User object is a guest user (id set to `user_id_guest`) or an authenticated user.
     *
     * @return boolean True if the user is a guest, false otherwise.
     */    
    public function isGuest();
    
    /**
     * Get an array containing all groups to which this user belongs.
     *
     * This method caches the data after the first time loading from the database.  To force a refresh, use the `fresh` method.
     * @return GroupObjectInterface[] An array of Group objects, indexed by the group id.
     */  
    public function getGroups();

    /**
     * Adds this user to a specified group, skipping if this user is already a member of the group.  Call `store` to persist to database.
     *
     * @param int $group_id The id of the group to add the user to.
     * @throws Exception The specified group does not exist.
     * @return UserObjectInterface this User object.
     */  
    public function addGroup($group_id);

    /**
     * Remove this user from a specified group, skipping if this user is not a member of the group.  Call `store` to persist to database.
     *
     * @param int $group_id The id of the group to remove the user from.
     * @return UserObjectInterface this User object.
     */ 
    public function removeGroup($group_id);
    
    /**
     * Get the theme for this user.
     *
     * The theme for the root user is always 'root'.  The theme for guest users is 'default'.  Any other users will have their themes determined by their primary group.
     * @return GroupObjectInterface[] An array of Group objects, indexed by the group id.
     */ 
    public function getTheme();
    
    /**
     * Get this user's primary group.
     *
     * This method caches the data after the first time loading from the database.  To force a refresh, use the `fresh` method.
     * @return GroupObjectInterface the Group object representing the user's primary group.
     */  
    public function getPrimaryGroup();
    
    /**
     * Checks whether or not this user has access for a particular authorization hook.
     *
     * Determine if this user has access to the given $hook under the given $params.
     * @param string $hook The authorization hook to check for access.
     * @param array $params[optional] An array of field names => values, specifying any additional data to provide the authorization module
     * when determining whether or not this user has access.
     * @return boolean True if the user has access, false otherwise.
     */ 
    public function checkAccess($hook, $params);

    /**
     * Verify a plaintext password against the user's hashed password.
     *
     * @param string $password The plaintext password to verify.
     * @return boolean True if the password matches, false otherwise.
     */     
    public function verifyPassword($password);
    
    /**
     * Log this user in.  This basically updates the user's sign-in time, and updates any old password hashes.
     *
     * You should set this user object to $_SESSION["userfrosting"]["user"] after calling login, so that it will persist in the session.
     */
    public function login();
}

/**
 * SiteSettingsInterface Interface
 *
 * A interface for site settings database classes.
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
interface SiteSettingsInterface {
    
    /**
     * Determine whether or not all settings defined in this object are present in the database.
     *
     * @return boolean true if the table exists and all keys (core userfrosting and plugins) are defined in the table, false otherwise.
     */
    public function isConsistent();
    
    /**
     * Fetch the settings from the database.
     *
     * @return array An array of site settings, containing the name and description for each setting.
     */
    public function fetchSettings();
    
    /**
     * Magic isset to determine if a particular setting is defined in the environment or core userfrosting settings.
     * This does not check plugin settings or descriptions.
     *
     * @param string $name The name of the setting.
     * @return boolean true if $name is defined, false otherwise.
     */
    public function __isset($name);
    
    /**
     * Magic setter to set the value of a core userfrosting setting.
     * This does not allow you to set the setting description.  To do that, you must use `set`.
     *
     * @param string $name The name of the setting.
     * @param string $value The value to assign the setting.
     */
    public function __set($name, $value);
    
    /**
     * Magic getter to get the value of an environment or core userfrosting setting.  This does not get plugin settings.  For that, you must use `get`.
     * This will first check if an environment setting of the specified $name exists and return it.  If not, it will then check if a DB setting of that name exists.
     *
     * @param string $name The name of the setting.
     * @return string the value of the setting.
     * @throws Exception The value does not exist in the environment or core settings.
     */
    public function __get($name);

    /**
     * Create/update a setting value.  If it exists, update, otherwise, create.  If updating, then a value or description set to null tells it to remain the same.  If creating, a value or description of null sets the field to an empty string.
     *
     * @param string $plugin The name of the plugin to associate this setting with.
     * @param string $name The name of the setting.
     */    
    public function set($plugin, $name, $value = null, $description = null);

    /**
     * Get a persistent setting value for a particular plugin.  Throws an exception if the plugin or value does not exist.
     *
     * @param string $name The name of the setting.
     * @param string $plugin The plugin scope of this setting.  Defaults to "userfrosting".
     * @throws Exception The value does not exist for this plugin.     
     */  
    public function get($name, $plugin = "userfrosting");
    
    /**
     * Get the description persistent setting value for a particular plugin.  Throws an exception if the plugin or description does not exist.
     *
     * @param string $name The name of the setting.
     * @param string $plugin The plugin scope of this setting.  Defaults to "userfrosting".
     * @throws Exception The description does not exist for this value of the specified plugin.     
     */      
    public function getDescription($name, $plugin = "userfrosting");

    /**
     * Get a site environment (non-persistent) variable.  Throws an exception if the variable does not exist.
     *
     * @param string $name The name of the site environment variable.
     * @throws Exception The specified environment variable does not exist. 
     */    
    public function getEnvironment($name);
    
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
    public function register($plugin, $name, $label, $type = "text", $options = []);
    
    /**
     * Get an array of site settings that have been registered for display on the site settings page.
     *
     * The result is a multidimensional array indexed first by plugin name, then by setting name.  Each setting name will then have an array containing values for "value", "label", "type", "options", and "description".
     * For example, $settings['userfrosting']['site_title']['value'] = "UserFrosting".
     * @return array The array of site settings.
     */   
    public function getRegisteredSettings();
    
    /**
     * Get an array of available locales for UserFrosting by scanning the path specified in $app->config('locales.path').
     *
     * @return array An array containing the names of all available locales (e.g. "en_EN", "es_ES", etc.)
     */  
    public function getLocales();
    
    /**
     * Get an array of available themes for UserFrosting by scanning the path specified in $app->config('themes.path').
     *
     * @return array An array containing the names of all available themes (e.g. "default", "root", etc.)
     */      
    public function getThemes();
    
    /**
     * Get an array of installed plugins by scanning the path specified in $app->config('plugins.path').
     *
     * @return array An array containing the names of all available themes (e.g. "oauth", "datatables", etc.)
     */      
    public function getPlugins();
    
    /**
     * Get an array of system information for UserFrosting.
     *
     * @return array An array containing a list of information, such as software version, application path, etc.
     */       
    public function getSystemInfo();
    
    /**
     * Get the PHP error log as an array of lines.
     *
     * @param int $lines the number of lines to display.  Set to `null` to display all lines.
     * @return array An array containing 'path', which is the path of the PHP error log, and 'messages', which is an array of error messages sorted with the newest messages first.
     */ 
    public function getLog($lines = null);
    
    /**
     * Store the site settings from this object to the database, inserting new records when necessary and updating existing records otherwise.
     */ 
    public function store();
}
