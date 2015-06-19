<?php

namespace UserFrosting;

// Represents the UserFrosting database.  Used for initializing connections for queries.  Set $params to the connection variables you'd like to use.
abstract class MySqlDatabase extends UFDatabase implements DatabaseInterface {
   
    // Call this to get a fresh connection to the database.
	public static function connection(){
        $db_host = static::$app->config('db')['db_host'];
        $db_name = static::$app->config('db')['db_name'];
    
        $db = new \PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8", static::$app->config('db')['db_user'], static::$app->config('db')['db_pass']);
        $db->setAttribute(\PDO::ATTR_EMULATE_PREPARES, false);
        $db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);       // Let this function throw a PDO exception if it cannot connect.
        return $db;    
    }
    
    // Test whether a DB connection can be established
    public static function testConnection(){
        try {
            static::connection();
        } catch (\PDOException $e){
            return false;
        }
        return true;
    }
    
    // Get information about this database as key->value pairs
    public static function getInfo(){
        $connection = static::connection();
        $results = [];
        try {
            $results['db_type'] = $connection->getAttribute(\PDO::ATTR_DRIVER_NAME);
        } catch (Exception $e){
            $results['db_type'] = "Unknown";
        }
        try {
            $results['db_version'] = $connection->getAttribute(\PDO::ATTR_SERVER_VERSION);
        } catch (Exception $e){
            $results['db_type'] = "";
        }
        $results['db_name'] = static::$app->config('db')['db_name'];
        $results['table_prefix'] = static::$app->config('db')['db_prefix'];
        return $results;
    }
    
    // Return a list of UF tables that actually exist
    public static function getTables(){
        if (!static::testConnection())
            return [];
        
        $connection = static::connection();
        $results = [];
        
        $test_list = [
            static::getTableAuthorizeGroup(),
            static::getTableAuthorizeUser(),
            static::getTableConfiguration(),
            static::getTableGroup(),
            static::getTableGroupUser(),
            static::getTableUser()
        ];
        
        foreach ($test_list as $table){
            try {
                $stmt = $connection->prepare("SELECT 1 FROM `$table` LIMIT 1;");
            } catch (\PDOException $e){
                continue;
            }
            $results[] = $table;
        }
        
        return $results;
    }
    
    // Creates the tables for UserFrosting
    public static function install(){
        $connection = static::connection();
        
        $prefix = static::$app->config('db')['db_prefix'];
        
        $connection->query("CREATE TABLE IF NOT EXISTS `$prefix" . "authorize_group` (
            `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
            `group_id` int(11) NOT NULL,
            `hook` varchar(200) NOT NULL COMMENT 'A code that references a specific action or URI that the group has access to.',
            `conditions` text NOT NULL COMMENT 'The conditions under which members of this group have access to this hook.',
            PRIMARY KEY (`id`)
          ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;");
          
        $connection->query("CREATE TABLE IF NOT EXISTS `$prefix" . "authorize_user` (
            `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
            `user_id` int(11) NOT NULL,
            `hook` varchar(200) NOT NULL COMMENT 'A code that references a specific action or URI that the user has access to.',
            `conditions` text NOT NULL COMMENT 'The conditions under which the user has access to this action.',
            PRIMARY KEY (`id`)
          ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;");
          
        $connection->query("CREATE TABLE IF NOT EXISTS `$prefix" . "configuration` (
            `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
            `plugin` varchar(50) NOT NULL COMMENT 'The name of the plugin that manages this setting (set to ''userfrosting'' for core settings)',
            `name` varchar(150) NOT NULL COMMENT 'The name of the setting.',
            `value` longtext NOT NULL COMMENT 'The current value of the setting.',
            `description` text NOT NULL COMMENT 'A brief description of this setting.',
            PRIMARY KEY (`id`)
          ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='A configuration table, mapping global configuration options to their values.' AUTO_INCREMENT=1 ;");
                   
        $connection->query("CREATE TABLE IF NOT EXISTS `$prefix" . "group` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `name` varchar(150) NOT NULL,
            `is_default` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Specifies whether this permission is a default setting for new accounts.',
            `can_delete` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Specifies whether this permission can be deleted from the control panel.',
            `theme` varchar(100) NOT NULL DEFAULT 'default' COMMENT 'The theme assigned to primary users in this group.',
            `landing_page` varchar(200) NOT NULL DEFAULT 'account' COMMENT 'The page to take primary members to when they first log in.',
            `new_user_title` varchar(200) NOT NULL DEFAULT 'New User' COMMENT 'The default title to assign to new primary users.',
            `icon` varchar(100) NOT NULL DEFAULT 'fa fa-user' COMMENT 'The icon representing primary users in this group.',
            PRIMARY KEY (`id`)
          ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;");
          
          
        $connection->query("CREATE TABLE IF NOT EXISTS `$prefix" . "group_user` (
            `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
            `user_id` int(10) unsigned NOT NULL,
            `group_id` int(10) unsigned NOT NULL,
            PRIMARY KEY (`id`)
          ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COMMENT='Maps users to their group(s)' AUTO_INCREMENT=1 ;");
          
        $connection->query("CREATE TABLE IF NOT EXISTS `$prefix" . "user` (
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

        // Setup default configuration settings
        static::$app->site->install_status = "pending";
        static::$app->site->root_account_config_token = md5(uniqid(mt_rand(), false));
        static::$app->site->store();        
        
        // Setup default groups.  TODO: finish Group API so they can be created through objects
        $connection->query("INSERT INTO `$prefix" . "group` (`name`, `is_default`, `can_delete`, `theme`, `landing_page`, `new_user_title`, `icon`) VALUES
          ('User', " . GROUP_DEFAULT_PRIMARY . ", 0, 'default', 'dashboard', 'New User', 'fa fa-user'),
          ('Administrator', " . GROUP_NOT_DEFAULT . ", 0, 'nyx', 'dashboard', 'Brood Spawn', 'fa fa-flag'),
          ('Zerglings', " . GROUP_NOT_DEFAULT . ", 1, 'nyx', 'dashboard', 'Tank Fodder', 'sc sc-zergling');");        
    
        // Setup default authorizations
        $connection->query("INSERT INTO `$prefix" . "authorize_group` (`group_id`, `hook`, `conditions`) VALUES
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
