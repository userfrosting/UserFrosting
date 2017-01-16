<?php

    use Illuminate\Database\Capsule\Manager as Capsule;
    use Illuminate\Database\Schema\Blueprint;
    use UserFrosting\Sprinkle\Account\Model\Group;
    use UserFrosting\Sprinkle\Account\Model\Permission;
    use UserFrosting\Sprinkle\Account\Model\Role;

    /**
     * User activity table.  Renames the "user events" table.
     */
    if (!$schema->hasTable('activities')) {
        $schema->create('activities', function (Blueprint $table) {
            $table->increments('id');
            $table->string('ip_address', 45)->nullable();
            $table->integer('user_id')->unsigned();
            $table->string('type', 255)->comment('An identifier used to track the type of activity.');
            $table->timestamp('occurred_at')->nullable();
            $table->text('description')->nullable();

            $table->engine = 'InnoDB';
            $table->collation = 'utf8_unicode_ci';
            $table->charset = 'utf8';
            //$table->foreign('user_id')->references('id')->on('users');
            $table->index('user_id');
        });
        echo "Created table 'activities'..." . PHP_EOL;
    } else {
        echo "Table 'activities' already exists.  Skipping..." . PHP_EOL;
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
        $groups = [
            'terran' => new Group([
                'slug' => 'terran',
                'name' => 'Terran',
                'description' => 'The terrans are a young species with psionic potential. The terrans of the Koprulu sector descend from the survivors of a disastrous 23rd century colonization mission from Earth.',
                'icon' => 'sc sc-terran'
            ]),
            'zerg' => new Group([
                'slug' => 'zerg',
                'name' => 'Zerg',
                'description' => 'Dedicated to the pursuit of genetic perfection, the zerg relentlessly hunt down and assimilate advanced species across the galaxy, incorporating useful genetic code into their own.',
                'icon' => 'sc sc-zerg'
            ]),
            'protoss' => new Group([
                'slug' => 'protoss',
                'name' => 'Protoss',
                'description' => 'The protoss, a.k.a. the Firstborn, are a sapient humanoid race native to Aiur. Their advanced technology complements and enhances their psionic mastery.',
                'icon' => 'sc sc-protoss'
            ])
        ];

        foreach ($groups as $slug => $group) {
            $group->save();
        }
        echo "Created table 'groups'..." . PHP_EOL;
    } else {
        echo "Table 'groups' already exists.  Skipping..." . PHP_EOL;
    }

    /**
     * Manages requests for password resets.
     */
    if (!$schema->hasTable('password_resets')) {
        $schema->create('password_resets', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned();
            $table->string('hash');
            $table->boolean('completed')->default(0);
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->engine = 'InnoDB';
            $table->collation = 'utf8_unicode_ci';
            $table->charset = 'utf8';
            //$table->foreign('user_id')->references('id')->on('users');
            $table->index('user_id');
            $table->index('hash');
        });
        echo "Created table 'password_resets'..." . PHP_EOL;
    } else {
        echo "Table 'password_resets' already exists.  Skipping..." . PHP_EOL;
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
        $roles = [
            'user' => new Role([
                'slug' => 'user',
                'name' => 'User',
                'description' => 'This role provides basic user functionality.'
            ]),
            'site-admin' => new Role([
                'slug' => 'site-admin',
                'name' => 'Site Administrator',
                'description' => 'This role is meant for "site administrators", who can basically do anything except create, edit, or delete other administrators.'
            ]),
            'group-admin' => new Role([
                'slug' => 'group-admin',
                'name' => 'Group Administrator',
                'description' => 'This role is meant for "group administrators", who can basically do anything with users in their own group, except other administrators of that group.'
            ])
        ];

        foreach ($roles as $slug => $role) {
            $role->save();
        }
        echo "Created table 'roles'..." . PHP_EOL;
    } else {
        echo "Table 'roles' already exists.  Skipping..." . PHP_EOL;
    }

    /**
     * Many-to-many mapping between permissions and roles.
     */
    if (!$schema->hasTable('permission_roles')) {
        $schema->create('permission_roles', function (Blueprint $table) {
            $table->integer('permission_id')->unsigned();
            $table->integer('role_id')->unsigned();
            $table->timestamps();

            $table->engine = 'InnoDB';
            $table->collation = 'utf8_unicode_ci';
            $table->charset = 'utf8';
            $table->primary(['permission_id', 'role_id']);
            //$table->foreign('permission_id')->references('id')->on('permissions');
            //$table->foreign('role_id')->references('id')->on('roles');
            $table->index('permission_id');
            $table->index('role_id');
        });

        echo "Created table 'permission_roles'..." . PHP_EOL;
    } else {
        echo "Table 'permission_roles' already exists.  Skipping..." . PHP_EOL;
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
        });

        // Add default permissions
        $permissions = [
            'create_user' => new Permission([
                'slug' => 'create_user',
                'name' => 'Create user',
                'conditions' => 'always()',
                'description' => 'Create a new user and assign default group and roles.'
            ]),
            'delete_user' => new Permission([
                'slug' => 'delete_user',
                'name' => 'Delete user',
                'conditions' => '!has_role(user.id,2) && !is_master(user.id)',
                'description' => 'Delete users who are not Site Administrators.'
            ]),
            'update_account_settings' => new Permission([
                'slug' => 'update_account_settings',
                'name' => 'Edit user',
                'conditions' => 'always()',
                'description' => 'Edit your own account settings.'
            ]),
            'update_user_field' => new Permission([
                'slug' => 'update_user_field',
                'name' => 'Edit user',
                'conditions' => '!has_role(user.id,2) && subset(fields,["name","email","theme","locale","group","flag_enabled","flag_verified","password"])',
                'description' => 'Edit users who are not Site Administrators.'
            ]),
            'uri_account_settings' => new Permission([
                'slug' => 'uri_account_settings',
                'name' => 'Account settings page',
                'conditions' => 'always()',
                'description' => 'View the account settings page.'
            ]),
            'uri_user' => new Permission([
                'slug' => 'uri_user',
                'name' => 'View user',
                'conditions' => 'always()',
                'description' => 'View the user page of any user.'
            ]),
            'uri_user_in_group' => new Permission([
                'slug' => 'uri_user',
                'name' => 'View user',
                'conditions' => 'equals_num(self.group_id,user.group_id)',
                'description' => 'View the user page of any user in your group.'
            ]),
            'uri_users' => new Permission([
                'slug' => 'uri_users',
                'name' => 'User management page',
                'conditions' => 'always()',
                'description' => 'View a page containing a table of users.'
            ]),
            'view_user_field' => new Permission([
                'slug' => 'view_user_field',
                'name' => 'View user',
                'conditions' => 'in(property,["user_name","name","email","locale","theme","roles","group"])',
                'description' => 'View certain properties of any user.'
            ])
        ];

        foreach ($permissions as $slug => $permission) {
            $permission->save();
        }

        // Add default mappings to permissions
        $roleUser = Role::where('slug', 'user')->first();
        if ($roleUser) {
            $roleUser->permissions()->sync([
                $permissions['update_account_settings']->id,
                $permissions['uri_account_settings']->id
            ]);
        }

        $roleSiteAdmin = Role::where('slug', 'site-admin')->first();
        if ($roleSiteAdmin) {
            $roleSiteAdmin->permissions()->sync([
                $permissions['create_user']->id,
                $permissions['delete_user']->id,
                $permissions['update_user_field']->id,
                $permissions['uri_user']->id,
                $permissions['uri_users']->id,
                $permissions['view_user_field']->id
            ]);
        }

        $roleGroupAdmin = Role::where('slug', 'group-admin')->first();
        if ($roleGroupAdmin) {
            $roleGroupAdmin->permissions()->sync([
                $permissions['uri_user_in_group']->id
            ]);
        }

        echo "Created table 'permissions'..." . PHP_EOL;
    } else {
        echo "Table 'permissions' already exists.  Skipping..." . PHP_EOL;
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
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->engine = 'InnoDB';
            $table->collation = 'utf8_unicode_ci';
            $table->charset = 'utf8';
            //$table->foreign('user_id')->references('id')->on('users');
            $table->index('user_id');
            $table->index('token');
            $table->index('persistent_token');
        });
        echo "Created table 'persistences'..." . PHP_EOL;
    } else {
        echo "Table 'persistences' already exists.  Skipping..." . PHP_EOL;
    }

    /**
     * Many-to-many mapping between roles and users.
     */
    if (!$schema->hasTable('role_users')) {
        $schema->create('role_users', function (Blueprint $table) {
            $table->integer('user_id')->unsigned();
            $table->integer('role_id')->unsigned();
            $table->timestamps();

            $table->engine = 'InnoDB';
            $table->collation = 'utf8_unicode_ci';
            $table->charset = 'utf8';
            $table->primary(['user_id', 'role_id']);
            //$table->foreign('user_id')->references('id')->on('users');
            //$table->foreign('role_id')->references('id')->on('roles');
            $table->index('user_id');
            $table->index('role_id');
        });
        echo "Created table 'role_users'..." . PHP_EOL;
    } else {
        echo "Table 'role_users' already exists.  Skipping..." . PHP_EOL;
    }

    /**
     * Table for database sessions.
     */
    if (!$schema->hasTable('sessions')) {
        $schema->create('sessions', function (Blueprint $table) {
            $table->string('id')->unique();
            $table->integer('user_id')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->text('payload');
            $table->integer('last_activity');
        });
        echo "Created table 'sessions'..." . PHP_EOL;
    } else {
        echo "Table 'sessions' already exists.  Skipping..." . PHP_EOL;
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
        echo "Created table 'throttles'..." . PHP_EOL;
    } else {
        echo "Table 'throttles' already exists.  Skipping..." . PHP_EOL;
    }

    /**
     * Removed the 'display_name', 'title', 'secret_token', and 'flag_password_reset' fields, and added first and last name and 'last_activity_at'.
     */
    if (!$schema->hasTable('users')) {
        $schema->create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('user_name', 50);
            $table->string('email', 254);
            $table->string('first_name', 20);
            $table->string('last_name', 30);
            $table->string('locale', 10)->default('en_US')->comment('The language and locale to use for this user.');
            $table->string('theme', 100)->nullable()->comment("The user theme.");
            $table->integer('group_id')->unsigned()->default(1)->comment("The id of the user group.");
            $table->boolean('flag_verified')->default(1)->comment("Set to 1 if the user has verified their account via email, 0 otherwise.");
            $table->boolean('flag_enabled')->default(1)->comment("Set to 1 if the user account is currently enabled, 0 otherwise.  Disabled accounts cannot be logged in to, but they retain all of their data and settings.");
            $table->integer('last_activity_id')->unsigned()->nullable()->comment("The id of the last activity performed by this user.");
            $table->string('password', 255);
            $table->softDeletes();
            $table->timestamps();

            $table->engine = 'InnoDB';
            $table->collation = 'utf8_unicode_ci';
            $table->charset = 'utf8';
            //$table->foreign('group_id')->references('id')->on('groups');
            //$table->foreign('last_activity_id')->references('id')->on('activities');
            $table->unique('user_name');
            $table->index('user_name');
            $table->unique('email');
            $table->index('email');
            $table->index('group_id');
            $table->index('last_activity_id');
        });
        echo "Created table 'users'..." . PHP_EOL;
    } else {
        echo "Table 'users' already exists.  Skipping..." . PHP_EOL;
    }

    /**
     * Manages requests for email account verification.
     */
    if (!$schema->hasTable('verifications')) {
        $schema->create('verifications', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned();
            $table->string('hash');
            $table->boolean('completed')->default(0);
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->engine = 'InnoDB';
            $table->collation = 'utf8_unicode_ci';
            $table->charset = 'utf8';
            //$table->foreign('user_id')->references('id')->on('users');
            $table->index('user_id');
            $table->index('hash');
        });
        echo "Created table 'verifications'..." . PHP_EOL;
    } else {
        echo "Table 'verifications' already exists.  Skipping..." . PHP_EOL;
    }
