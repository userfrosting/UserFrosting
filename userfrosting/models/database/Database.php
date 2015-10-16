<?php

namespace UserFrosting;

use \Illuminate\Database\Capsule\Manager as Capsule;

/**
 * Represents the UserFrosting database.
 *
 * Serves as a global static repository for table information, such as table names and columns.  Also, provides information about the database.
 * Finally, this class is responsible for initializing the database during installation.
 * @package UserFrosting
 * @author Alex Weissman
 * @see http://www.userfrosting.com/tutorials/lesson-3-data-model/ 
 */
abstract class Database {
    /**
     * @var Slim The Slim app, containing configuration info
     */
    public static $app;
    
    /**
     * @var array[DatabaseTable] An array of DatabaseTable objects representing the configuration of the database tables.
     */
    protected static $tables;
    
    /**
     * Retrieve a DatabaseTable object based on its handle.
     *
     * @param string $id the handle (id) of the table, as it was defined in the call to `setSchemaTable`.
     * @throws Exception there is no table associated with the specified handle.
     * @return DatabaseTable the DatabaseTable registered to this handle.
     */
    public static function getSchemaTable($id){
        if (isset(static::$tables[$id]))
            return static::$tables[$id];
        else
            throw new \Exception("There is no table with id '$id'.");
    }
    
    /**
     * Register a DatabaseTable object with the Database, assigning it the specified handle.
     *
     * @param string $id the handle (id) of the table, which you may choose.
     * @param DatabaseTable $table the DatabaseTable to associate with this handle.
     * @return void
     */    
    public static function setSchemaTable($id, $table){
        static::$tables[$id] = $table;
    }
    
    /**
     * Set the name for a DatabaseTable that has been registered with the database.
     *
     * @param string $id the handle (id) of the table, as it was defined in the call to `setSchemaTable`.
     * @param string $name the new name for the DatabaseTable.
     * @throws Exception there is no table associated with the specified handle.     
     * @return void
     */     
    public static function setTableName($id, $name){
        if (isset(static::$tables[$id])) {
            call_user_func_array([static::$tables[$id], "setName"], $name);
        } else
            throw new \Exception("There is no table with id '$id'.");
    }
    
    /**
     * Add columns to a DatabaseTable that has been registered with the database.
     *
     * @param string $id the handle (id) of the table, as it was defined in the call to `setSchemaTable`.
     * @param string $column,... the new columns to add to the DatabaseTable.
     * @throws Exception there is no table associated with the specified handle.     
     * @return void
     */     
    public static function addTableColumns($id){
        if (isset(static::$tables[$id])) {
            $columns = array_slice(func_get_args(), 1);
            call_user_func_array([static::$tables[$id], "addColumns"], $columns);
        } else
            throw new \Exception("There is no table with id '$id'.");
    }    
    
    /**
     * @see DatabaseInterface
     */
    public static function testConnection(){
        try {
            Capsule::connection();
        } catch (\PDOException $e){
            error_log("Error in " . $e->getFile() . " on line " . $e->getLine() . ": " . $e->getMessage());
            error_log($e->getTraceAsString());
            return false;
        }
        return true;
    }
    
    /**
     * @see DatabaseInterface
     */
    public static function getInfo(){
        $pdo = Capsule::connection()->getPdo();
        $results = [];
        try {
            $results['db_type'] = $pdo->getAttribute(\PDO::ATTR_DRIVER_NAME);
        } catch (Exception $e){
            $results['db_type'] = "Unknown";
        }
        try {
            $results['db_version'] = $pdo->getAttribute(\PDO::ATTR_SERVER_VERSION);
        } catch (Exception $e){
            $results['db_type'] = "";
        }
        $results['db_name'] = static::$app->config('db')['db_name'];
        $results['table_prefix'] = static::$app->config('db')['db_prefix'];
        return $results;
    }
    
    /**
     * @see DatabaseInterface
     */
    public static function getCreatedTables(){
        if (!static::testConnection())
            return [];
        
        $connection = Capsule::connection();
        $results = [];
        
        $test_list = [
            static::getSchemaTable('user')->name,
            static::getSchemaTable('group')->name,
            static::getSchemaTable('group_user')->name,
            static::getSchemaTable('authorize_user')->name,
            static::getSchemaTable('authorize_group')->name,
            static::$app->remember_me_table['tableName']
        ];
        
        foreach ($test_list as $table){
            try {
                $stmt = $connection->select("SELECT 1 FROM `$table` LIMIT 1;");
            } catch (\PDOException $e){
                continue;
            }
            $results[] = $table;
        }
        
        return $results;
    }
    
    /**
     * @see DatabaseInterface
     */
    public static function install(){
        $connection = Capsule::connection();
        
        $connection->statement("CREATE TABLE IF NOT EXISTS `" . static::getSchemaTable('configuration')->name . "` (
            `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
            `plugin` varchar(50) NOT NULL COMMENT 'The name of the plugin that manages this setting (set to ''userfrosting'' for core settings)',
            `name` varchar(150) NOT NULL COMMENT 'The name of the setting.',
            `value` longtext NOT NULL COMMENT 'The current value of the setting.',
            `description` text NOT NULL COMMENT 'A brief description of this setting.',
            PRIMARY KEY (`id`)
        ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='A configuration table, mapping global configuration options to their values.' AUTO_INCREMENT=1 ;");
            
        $connection->statement("CREATE TABLE IF NOT EXISTS `" . static::getSchemaTable('authorize_group')->name . "` (
            `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
            `group_id` int(11) NOT NULL,
            `hook` varchar(200) NOT NULL COMMENT 'A code that references a specific action or URI that the group has access to.',
            `conditions` text NOT NULL COMMENT 'The conditions under which members of this group have access to this hook.',
            PRIMARY KEY (`id`)
          ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;");
          
        $connection->statement("CREATE TABLE IF NOT EXISTS `" . static::getSchemaTable('authorize_user')->name . "` (
            `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
            `user_id` int(11) NOT NULL,
            `hook` varchar(200) NOT NULL COMMENT 'A code that references a specific action or URI that the user has access to.',
            `conditions` text NOT NULL COMMENT 'The conditions under which the user has access to this action.',
            PRIMARY KEY (`id`)
          ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;");
              
        $connection->statement("CREATE TABLE IF NOT EXISTS `" . static::getSchemaTable('group')->name . "` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `name` varchar(150) NOT NULL,
            `is_default` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Specifies whether this permission is a default setting for new accounts.',
            `can_delete` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Specifies whether this permission can be deleted from the control panel.',
            `theme` varchar(100) NOT NULL DEFAULT 'default' COMMENT 'The theme assigned to primary users in this group.',
            `landing_page` varchar(200) NOT NULL DEFAULT 'dashboard' COMMENT 'The page to take primary members to when they first log in.',
            `new_user_title` varchar(200) NOT NULL DEFAULT 'New User' COMMENT 'The default title to assign to new primary users.',
            `icon` varchar(100) NOT NULL DEFAULT 'fa fa-user' COMMENT 'The icon representing primary users in this group.',
            PRIMARY KEY (`id`)
          ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;");
          
          
        $connection->statement("CREATE TABLE IF NOT EXISTS `" . static::getSchemaTable('group_user')->name . "` (
            `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
            `user_id` int(10) unsigned NOT NULL,
            `group_id` int(10) unsigned NOT NULL,
            PRIMARY KEY (`id`)
          ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Maps users to their group(s)' AUTO_INCREMENT=1 ;");
          
        $connection->statement("CREATE TABLE IF NOT EXISTS `" . static::getSchemaTable('user')->name . "` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `user_name` varchar(50) NOT NULL,
            `display_name` varchar(50) NOT NULL,
            `password` varchar(255) NOT NULL,
            `email` varchar(150) NOT NULL,
            `activation_token` varchar(225) NOT NULL,
            `last_activation_request` datetime NOT NULL,
            `lost_password_request` tinyint(1) NOT NULL DEFAULT '0',
            `lost_password_timestamp` datetime DEFAULT NULL,
            `active` tinyint(1) NOT NULL DEFAULT '1',
            `title` varchar(150) NOT NULL,
            `sign_up_stamp` datetime NOT NULL,
            `last_sign_in_stamp` datetime DEFAULT NULL,
            `enabled` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'Specifies if the account is enabled.  Disabled accounts cannot be logged in to, but they retain all of their data and settings.',
            `primary_group_id` tinyint(1) NOT NULL DEFAULT '1' COMMENT 'Specifies the primary group for the user.',
            `locale` varchar(10) NOT NULL DEFAULT 'en_US' COMMENT 'The language and locale to use for this user.',
            PRIMARY KEY (`id`)
          ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;");
        
        $connection->statement("CREATE TABLE IF NOT EXISTS `" . static::$app->remember_me_table['tableName'] . "` (
            `user_id` int(11) NOT NULL,
            `token` varchar(40) NOT NULL,
            `persistent_token` varchar(40) NOT NULL,
            `expires` datetime NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;"); 
        
        // Setup initial configuration settings        
        static::$app->site->install_status = "pending";
        static::$app->site->root_account_config_token = md5(uniqid(mt_rand(), false));
        static::$app->site->store();        
        
        // Setup default groups.  TODO: finish Group API so they can be created through objects
        $connection->insert("INSERT INTO `" . static::getSchemaTable('group')->name . "` (`name`, `is_default`, `can_delete`, `theme`, `landing_page`, `new_user_title`, `icon`) VALUES
          ('User', " . GROUP_DEFAULT_PRIMARY . ", 0, 'default', 'dashboard', 'New User', 'fa fa-user'),
          ('Administrator', " . GROUP_NOT_DEFAULT . ", 0, 'nyx', 'dashboard', 'Brood Spawn', 'fa fa-flag'),
          ('Zerglings', " . GROUP_NOT_DEFAULT . ", 1, 'nyx', 'dashboard', 'Tank Fodder', 'sc sc-zergling');");        
    
        // Setup default authorizations
        $connection->insert("INSERT INTO `" . static::getSchemaTable('authorize_group')->name . "` (`group_id`, `hook`, `conditions`) VALUES
          (1, 'uri_dashboard', 'always()'),
          (2, 'uri_dashboard', 'always()'),
          (2, 'uri_users', 'always()'),
          (1, 'uri_account_settings', 'always()'),
          (1, 'update_account_setting', 'equals(self.id, user.id)&&in(property,[\"email\",\"locale\",\"password\"])'),
          (2, 'update_account_setting', 'in(property,[\"email\",\"display_name\",\"title\",\"locale\",\"enabled\"])'),
          (2, 'view_account_setting', 'in(property,[\"user_name\",\"email\",\"display_name\",\"title\",\"locale\",\"enabled\",\"groups\",\"primary_group_id\"])'),
          (2, 'delete_account', '!in_group(user.id,2)'),
          (2, 'create_account', 'always()');");    
    }
    
}
