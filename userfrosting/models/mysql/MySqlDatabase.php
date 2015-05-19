<?php

namespace UserFrosting;

// Represents the UserFrosting database.  Used for initializing connections for queries.  Set $params to the connection variables you'd like to use.
abstract class MySqlDatabase implements DatabaseInterface {

    public static $params;      // The connection parameters for the database
    public static $prefix;      // The table prefix to use in the database
    
    // Call this to get a fresh connection to the database.
	public static function connection(){
        $db_host = self::$params['db_host'];
        $db_name = self::$params['db_name'];
    
        $db = new \PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8", self::$params['db_user'], self::$params['db_pass']);
        $db->setAttribute(\PDO::ATTR_EMULATE_PREPARES, false);
        $db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);       // Let this function throw a PDO exception if it cannot connect.
        return $db;    
    }
}
