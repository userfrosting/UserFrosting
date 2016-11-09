<?php

namespace UserFrosting;

use \Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;

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
                $stmt = $connection->select("SELECT 1 FROM $table LIMIT 1;");
            } catch (\PDOException $e){
                continue;
            }
            $results[] = $table;
        }
        
        return $results;
    }
    
    /**
     * Set up the initial tables for the database.
     *
     * Creates all tables, and loads the configuration table with the default config data.  Also, sets install_status to `pending`.
     */   
    public static function install(){
        $schema = Capsule::schema();

        /**
         * `configuration` table.
         */
        if (!$schema->hasTable(static::getSchemaTable('configuration')->name)) {
            $schema->create(static::getSchemaTable('configuration')->name, function (Blueprint $table) {
                $table->increments('id');
                $table->string('plugin', 50)->comment("The name of the plugin that manages this setting (set to ''userfrosting'' for core settings)");
                $table->string('name', 150)->comment('The name of the setting.');
                $table->text('value')->comment('The current value of the setting.');
                $table->text('description')->comment('A brief description of this setting.');
                $table->engine = 'InnoDB';
                $table->collation = 'utf8_unicode_ci';
                $table->charset = 'utf8';
            });
        }

        /**
         * `authorize_group` table.
         */
        if (!$schema->hasTable(static::getSchemaTable('authorize_group')->name)) {
            $schema->create(static::getSchemaTable('authorize_group')->name, function (Blueprint $table) {
                $table->increments('id');
                $table->integer('group_id')->unsigned();
                $table->string('hook', 200)->comment('A code that references a specific action or URI that the group has access to.');
                $table->text('conditions')->comment('The conditions under which members of this group have access for this hook.');
                $table->engine = 'InnoDB';
                $table->collation = 'utf8_unicode_ci';
                $table->charset = 'utf8';
            });
        }
        
        /**
         * `authorize_user` table.
         */
        if (!$schema->hasTable(static::getSchemaTable('authorize_user')->name)) {
            $schema->create(static::getSchemaTable('authorize_user')->name, function (Blueprint $table) {
                $table->increments('id');
                $table->integer('user_id')->unsigned();
                $table->string('hook', 200)->comment('A code that references a specific action or URI that the user has access to.');
                $table->text('conditions')->comment('The conditions under which the user has access for this hook.');
                $table->engine = 'InnoDB';
                $table->collation = 'utf8_unicode_ci';
                $table->charset = 'utf8';
            });
        }

        /**
         * `group` table.
         */
        if (!$schema->hasTable(static::getSchemaTable('group')->name)) {
            $schema->create(static::getSchemaTable('group')->name, function (Blueprint $table) {
                $table->increments('id');
                $table->string('name', 150);
                $table->boolean('is_default')->default(0)->comment('Specifies whether this permission is a default setting for new accounts.');
                $table->boolean('can_delete')->default(1)->comment('Specifies whether this permission can be deleted from the control panel.');
                $table->string('theme', 100)->default('default')->comment('The theme assigned to primary users in this group.');
                $table->string('landing_page', 200)->default('dashboard')->comment('The page to take primary members to when they first log in.');
                $table->string('new_user_title', 200)->default('New User')->comment('The default title to assign to new primary users.');
                $table->string('icon', 100)->default('fa fa-user')->comment('The icon representing primary users in this group.');
                $table->engine = 'InnoDB';
                $table->collation = 'utf8_unicode_ci';
                $table->charset = 'utf8';
            });
        }

        /**
         * `group_user` table.
         */
        if (!$schema->hasTable(static::getSchemaTable('group_user')->name)) {
            $schema->create(static::getSchemaTable('group_user')->name, function (Blueprint $table) {
                $table->increments('id');
                $table->integer('user_id')->unsigned();
                $table->integer('group_id')->unsigned();
                $table->engine = 'InnoDB';
                $table->collation = 'utf8_unicode_ci';
                $table->charset = 'utf8';
            });
        }

        /**
         * `user` table.
         */
        if (!$schema->hasTable(static::getSchemaTable('user')->name)) {
            $schema->create(static::getSchemaTable('user')->name, function (Blueprint $table) {
                $table->increments('id');
                $table->string('user_name', 50);
                $table->string('display_name', 50);
                $table->string('email', 150);
                $table->string('title', 150);
                $table->string('locale', 10)->default('en_US')->comment('The language and locale to use for this user.');
                $table->integer('primary_group_id')->unsigned()->default(1)->comment("The id of this user''s primary group.");
                $table->string('secret_token', 32)->default('')->comment('The current one-time use token for various user activities confirmed via email.');
                $table->boolean('flag_verified')->default(1)->comment("Set to ''1'' if the user has verified their account via email, ''0'' otherwise.");
                $table->boolean('flag_enabled')->default(1)->comment("Set to ''1'' if the user''s account is currently enabled, ''0'' otherwise.  Disabled accounts cannot be logged in to, but they retain all of their data and settings.");
                $table->boolean('flag_password_reset')->default(0)->comment("Set to ''1'' if the user has an outstanding password reset request, ''0'' otherwise.");
                $table->timestamps();
                $table->string('password', 255);
                $table->engine = 'InnoDB';
                $table->collation = 'utf8_unicode_ci';
                $table->charset = 'utf8';
            });
        }

        /**
         * `user_event` table.
         */
        if (!$schema->hasTable(static::getSchemaTable('user_event')->name)) {
            $schema->create(static::getSchemaTable('user_event')->name, function (Blueprint $table) {
                $table->increments('id');
                $table->integer('user_id')->unsigned();
                $table->string('event_type', 255)->comment('An identifier used to track the type of event.');
                $table->timestamp('occurred_at');
                $table->text('description');
                $table->engine = 'InnoDB';
                $table->collation = 'utf8_unicode_ci';
                $table->charset = 'utf8';             
            });
        }

        /**
         * 'remember me' table.
         */
        if (!$schema->hasTable(static::$app->remember_me_table['tableName'])) {
            $schema->create(static::$app->remember_me_table['tableName'], function (Blueprint $table) {
                $table->integer('user_id')->unsigned();
                $table->string('token', 40);
                $table->string('persistent_token', 40);
                $table->dateTime('expires');
                $table->engine = 'InnoDB';
                $table->collation = 'utf8_unicode_ci';
                $table->charset = 'utf8';             
            });
        }
        
        // Setup initial configuration settings        
        static::$app->site->install_status = "pending";
        static::$app->site->root_account_config_token = md5(uniqid(mt_rand(), false));
        static::$app->site->store();        
        
        // Setup default groups
        Capsule::table(static::getSchemaTable('group')->name)->insert([
            [
                'id' => 1,
                'name' => 'User',
                'is_default' => GROUP_DEFAULT_PRIMARY,
                'can_delete' => 0,
                'theme' => 'default',
                'landing_page' => 'dashboard',
                'new_user_title' => 'New User',
                'icon' => 'fa fa-user'
            ],
            [
                'id' => 2,
                'name' => 'Administrator',
                'is_default' => GROUP_NOT_DEFAULT,
                'can_delete' => 0,
                'theme' => 'nyx',
                'landing_page' => 'dashboard',
                'new_user_title' => 'Brood Spawn',
                'icon' => 'fa fa-flag'
            ],
            [
                'id' => 3,
                'name' => 'Zerglings',
                'is_default' => GROUP_NOT_DEFAULT,
                'can_delete' => 1,
                'theme' => 'nyx',
                'landing_page' => 'dashboard',
                'new_user_title' => 'Tank Fodder',
                'icon' => 'sc sc-zergling'
            ]
        ]);        
    
    
        // Setup default authorizations
        Capsule::table(static::getSchemaTable('authorize_group')->name)->insert([
            [
                'id' => 1,
                'group_id' => 1,
                'hook' => 'uri_dashboard',
                'conditions' => 'always()'
            ],
            [
                'id' => 2,
                'group_id' => 2,
                'hook' => 'uri_dashboard',
                'conditions' => 'always()'
            ],            
            [
                'id' => 3,
                'group_id' => 2,
                'hook' => 'uri_users',
                'conditions' => 'always()'
            ],
            [
                'id' => 4,
                'group_id' => 1,
                'hook' => 'uri_account_settings',
                'conditions' => 'always()'
            ],
            [
                'id' => 5,
                'group_id' => 1,
                'hook' => 'update_account_setting',
                'conditions' => 'equals(self.id, user.id)&&in(property,[\"email\",\"locale\",\"password\"])'
            ],
            [
                'id' => 6,
                'group_id' => 2,
                'hook' => 'update_account_setting',
                'conditions' => '!in_group(user.id,2)&&in(property,[\"email\",\"display_name\",\"title\",\"locale\",\"flag_password_reset\",\"flag_enabled\"])'
            ],
            [
                'id' => 7,
                'group_id' => 2,
                'hook' => 'view_account_setting',
                'conditions' => 'in(property,[\"user_name\",\"email\",\"display_name\",\"title\",\"locale\",\"flag_enabled\",\"groups\",\"primary_group_id\"])'
            ],
            [
                'id' => 8,
                'group_id' => 2,
                'hook' => 'delete_account',
                'conditions' => '!in_group(user.id,2)'
            ],
            [
                'id' => 9,
                'group_id' => 2,
                'hook' => 'create_account',
                'conditions' => 'always()'
            ]
        ]);
    }
}
