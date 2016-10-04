<?php
    require_once '../app/vendor/autoload.php';    

    use Dotenv\Dotenv;
    use Dotenv\Exception\InvalidPathException;    
    use Illuminate\Database\Capsule\Manager as Capsule;
    use Illuminate\Database\Schema\Blueprint;
    
    // Grab any relevant dotenv variables from the .env file
    try {
        $dotenv = new Dotenv(\UserFrosting\APP_DIR);
        $dotenv->load();
    } catch (InvalidPathException $e) {
        // Skip loading the environment config file if it doesn't exist.
    }    
    
    $capsule = new Capsule;
    
    $capsule->addConnection([
        'driver'    => 'mysql',
        'host'      => 'localhost',
        'database'  => 'uf4',
        'username'  => 'userfrosting',
        'password'  => getenv('DB_PASSWORD'),
        'charset'   => 'utf8',
        'collation' => 'utf8_unicode_ci',
        'prefix'    => ''
    ]);
    
    // Register as global connection
    $capsule->setAsGlobal();
    
    // Start Eloquent
    $capsule->bootEloquent();
       
    $schema = Capsule::schema();
    
    /**
     * Keeps track of throttleable requests.
     */
    if (!$schema->hasTable('throttles')) {
        $schema->create('throttles', function (Blueprint $table) {
            $table->increments('id');
            $table->string('type');            
            $table->string('ip')->nullable();
            $table->text('request_data')->nullable();
            $table->timestamps();

            $table->engine = 'InnoDB';
            $table->collation = 'utf8_unicode_ci';
            $table->charset = 'utf8';            
            $table->index('type');
            $table->index('ip');
        });    
    }
    
    /**
     * Removed the 'display_name' and 'title' fields, and added first and last name.
     */
    if (!$schema->hasTable('users')) {
        $schema->create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('user_name', 50);
            $table->string('email', 254);
            $table->string('first_name', 20);
            $table->string('last_name', 30);
            $table->string('locale', 10)->default('en_US')->comment('The language and locale to use for this user.');
            $table->integer('group_id')->unsigned()->default(1)->comment('The id of the user\'s group.');
            $table->string('secret_token',32)->comment('The current one-time use token for various user activities confirmed via email.');
            $table->boolean('flag_verified')->default(1)->comment('Set to \'1\' if the user has verified their account via email, \'0\' otherwise.');
            $table->boolean('flag_enabled')->default(1)->comment('Set to \'1\' if the user\'s account is currently enabled, \'0\' otherwise.  Disabled accounts cannot be logged in to, but they retain all of their data and settings.');
            $table->boolean('flag_password_reset')->default(0)->comment('Set to \'1\' if the user has an outstanding password reset request, \'0\' otherwise.');
            $table->string('password', 255);
            $table->timestamps();
            
            $table->engine = 'InnoDB';
            $table->collation = 'utf8_unicode_ci';
            $table->charset = 'utf8';
            $table->unique('user_name');
            $table->index('user_name');
            $table->unique('email');
            $table->index('email');
            $table->index('group_id');
            $table->index('secret_token');
        });
    }    
    
    /**
     * Roles replace "groups" in UF 0.3.x.  Users acquire permissions through roles.
     */
    if (!$schema->hasTable('roles')) {
        $schema->create('roles', function (Blueprint $table) {
            $table->increments('id');
            $table->string('slug');
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();
            
            $table->engine = 'InnoDB';
            $table->collation = 'utf8_unicode_ci';
            $table->charset = 'utf8';
            $table->unique('slug');
            $table->index('slug');
        });
    }
    
    /**
     * Permissions now replace the 'authorize_group' and 'authorize_user' tables.
     * Also, they now map many-to-many to roles.
     */
    if (!$schema->hasTable('permissions')) {     
        $schema->create('permissions', function(Blueprint $table) {
            $table->increments('id');        
            $table->string('slug')->comment('A code that references a specific action or URI that an assignee of this permission has access to.');
            $table->string('name');
            $table->text('conditions')->comment('The conditions under which members of this group have access to this hook.');
            $table->text('description')->nullable();
            $table->timestamps();
            
            $table->engine = 'InnoDB';
            $table->collation = 'utf8_unicode_ci';
            $table->charset = 'utf8';
            $table->unique('slug');
            $table->index('slug');
        });
    }
    
    /**
     * Many-to-many mapping between roles and users.
     */
    if (!$schema->hasTable('role_users')) {
        $schema->create('role_users', function (Blueprint $table) {
            $table->integer('user_id')->unsigned();
            $table->integer('role_id')->unsigned();
            $table->nullableTimestamps();

            $table->engine = 'InnoDB';
            $table->collation = 'utf8_unicode_ci';
            $table->charset = 'utf8';            
            $table->primary(['user_id', 'role_id']);
            $table->index('user_id');
            $table->index('role_id');
        });
    }

    /**
     * Many-to-many mapping between permissions and roles.
     */
    if (!$schema->hasTable('permission_roles')) {
        $schema->create('permission_roles', function (Blueprint $table) {
            $table->integer('permission_id')->unsigned();
            $table->integer('role_id')->unsigned();
            $table->nullableTimestamps();

            $table->engine = 'InnoDB';
            $table->collation = 'utf8_unicode_ci';
            $table->charset = 'utf8';            
            $table->primary(['permission_id', 'role_id']);
            $table->index('permission_id');
            $table->index('role_id');
        });
    }
    
    /**
     * Renaming the "rememberme" table to something more standard.
     */
    if (!$schema->hasTable('persistences')) {
        $schema->create('persistences', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned();
            $table->string('token', 40);
            $table->string('persistent_token', 40);
            $table->timestamp('expires_at');
            $table->timestamps();

            $table->engine = 'InnoDB';
            $table->collation = 'utf8_unicode_ci';
            $table->charset = 'utf8';
            $table->index('user_id');
            $table->index('token');
            $table->index('persistent_token');
        });
    }    
    
    /**
     * "Group" now replaces the notion of "primary group" in earlier versions of UF.  A user can belong to exactly one group.
     */
    if (!$schema->hasTable('groups')) {     
        $schema->create('groups', function(Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('theme', 100)->nullable(false)->default('default')->comment('The default theme assigned to users in this group.');
            $table->string('landing_page', 200)->nullable(false)->default('dashboard')->comment('The page to take members to when they first log in.');
            $table->string('icon', 100)->nullable(false)->default('fa fa-user')->comment('The icon representing users in this group.');
            $table->timestamps();
                
            $table->engine = 'InnoDB';
            $table->collation = 'utf8_unicode_ci';
            $table->charset = 'utf8';
            
        });
    }
    
    /**
     * User activity table.  Renames the "user events" table.
     */
    if (!$schema->hasTable('activities')) {
        $schema->create('activities', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned();
            $table->string('type', 255)->comment('An identifier used to track the type of activity.');
            $table->timestamp('occurred_at');
            $table->text('description')->nullable();

            $table->engine = 'InnoDB';
            $table->collation = 'utf8_unicode_ci';
            $table->charset = 'utf8';
            $table->index('user_id');
        });
    }
