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
        'database'  => getenv('DB_NAME'),
        'username'  => getenv('DB_USER'),
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
    
    /**
     * "Group" now replaces the notion of "primary group" in earlier versions of UF.  A user can belong to exactly one group.
     */
    if (!$schema->hasTable('groups')) {     
        $schema->create('groups', function(Blueprint $table) {
            $table->increments('id');
            $table->string('slug');            
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('icon', 100)->nullable(false)->default('fa fa-user')->comment('The icon representing users in this group.');
            $table->timestamps();
                
            $table->engine = 'InnoDB';
            $table->collation = 'utf8_unicode_ci';
            $table->charset = 'utf8';
            $table->unique('slug');
            $table->index('slug');
        });
        
        // Add default groups
        Capsule::table('groups')->insert([
            [
                'id' => 1,
                'slug' => 'terran',
                'name' => 'Terran',
                'description' => 'The terrans are a young species with psionic potential. The terrans of the Koprulu sector descend from the survivors of a disastrous 23rd century colonization mission from Earth.',
                'icon' => 'sc sc-terran'
            ],
            [
                'id' => 2,
                'slug' => 'zerg',
                'name' => 'Zerg',
                'description' => 'Dedicated to the pursuit of genetic perfection, the zerg relentlessly hunt down and assimilate advanced species across the galaxy, incorporating useful genetic code into their own.',
                'icon' => 'sc sc-zerg'
            ],
            [
                'id' => 3,
                'slug' => 'protoss',
                'name' => 'Protoss',
                'description' => 'The protoss, a.k.a. the Firstborn, are a sapient humanoid race native to Aiur. Their advanced technology complements and enhances their psionic mastery.',
                'icon' => 'sc sc-protoss'
            ]
        ]);
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
        
        // Add default permissions
        Capsule::table('permissions')->insert([
            [
                'id' => 1,
                'slug' => 'uri_users',
                'name' => 'User management page',
                'conditions' => 'always()',
                'description' => 'View a page containing a table of users.'
            ],
            [
                'id' => 2,
                'slug' => 'update_account_setting',
                'name' => 'Edit user',
                'conditions' => '!has_role(user.id,2)&&in(property,[ "email","password", "name","flag_enabled","flag_password_reset","password","locale","theme"])',
                'description' => 'Edit users who are not Site Administrators.'
            ],            
            [
                'id' => 3,
                'slug' => 'view_account_setting',
                'name' => 'View user',
                'conditions' => 'in(property,["user_name","name","email","locale","theme","roles","group_id"])',
                'description' => 'View certain properties of any user.'
            ],
            [
                'id' => 4,
                'slug' => 'delete_account',
                'name' => 'Delete user',
                'conditions' => '!has_role(user.id,2)',
                'description' => 'Delete users who are not Site Administrators.'
            ],            
            [
                'id' => 5,
                'slug' => 'create_account',
                'name' => 'Create user',
                'conditions' => 'always()',
                'description' => 'Create a new user and assign default group and roles.'
            ],
            [
                'id' => 6,
                'slug' => 'uri_account_settings',
                'name' => 'Account settings page',
                'conditions' => 'always()',
                'description' => 'View the account settings page.'
            ]
        ]);
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
        
        // Add default mappings
        Capsule::table('permission_roles')->insert([
            // Basic user permissions
            [
                'role_id' => 1,
                'permission_id' => 6
            ],
            // Site admin permissions
            [
                'role_id' => 2,
                'permission_id' => 1
            ],
            [
                'role_id' => 2,
                'permission_id' => 2
            ],
            [
                'role_id' => 2,
                'permission_id' => 3
            ],
            [
                'role_id' => 2,
                'permission_id' => 4
            ],
            [
                'role_id' => 2,
                'permission_id' => 5
            ]
        ]);
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
        
        // Add default roles
        Capsule::table('roles')->insert([
            [
                'id' => 1,
                'slug' => 'user',
                'name' => 'User',
                'description' => 'This role provides basic user functionality.'
            ],
            [
                'id' => 2,
                'slug' => 'site-admin',
                'name' => 'Site Administrator',
                'description' => 'This role is meant for "site administrators", who can basically do anything except create, edit, or delete other administrators.'
            ],
            [
                'id' => 3,
                'slug' => 'group-admin',
                'name' => 'Group Administrator',
                'description' => 'This role is meant for "group administrators", who can basically do anything with users in their same group, except other administrators of that group.'
            ]
        ]);
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
            $table->string('theme', 100)->nullable(false)->default('default')->comment('The user\'s theme.');            
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
    