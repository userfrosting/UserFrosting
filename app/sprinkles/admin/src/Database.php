<?php

namespace UserFrosting\Sprinkle\Core\Model;

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
     * Test whether a DB connection can be established.
     *
     * @return bool true if the connection can be established, false otherwise.
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
     * Get an array of key-value pairs containing basic information about this database.
     *
     * The site settings module expects the following key-value pairs:
     * db_type, db_version, db_name, table_prefix
     * @return array[string] the properties of this database.
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
     * Get an array of the names of tables that exist in the database.
     *
     * Looks for tables with the following handles: user, group, group_user, authorize_group, authorize_user
     * @return array[string] the names of the UF tables that actually exist.
     */
    public static function getCreatedTables(){
        if (!static::testConnection())
            return [];

        $connection = Capsule::connection();
        $results = [];

        $test_list = [
            static::getSchemaTable('user')->name,
            static::getSchemaTable('user_event')->name,
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
}
