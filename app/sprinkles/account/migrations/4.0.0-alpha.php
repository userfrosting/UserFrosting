<?php

    use Illuminate\Database\Capsule\Manager as Capsule;
    use Illuminate\Database\Schema\Blueprint;
    use UserFrosting\Sprinkle\Account\Model\Group;
    use UserFrosting\Sprinkle\Account\Model\Permission;
    use UserFrosting\Sprinkle\Account\Model\Role;
    use UserFrosting\Sprinkle\Account\Model\User;
    use UserFrosting\Sprinkle\Account\Util\Password;

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

        $defaultRoleIds = [
            'user' => Role::where('slug', 'user')->first()->id,
            'group-admin' => Role::where('slug', 'group-admin')->first()->id,
            'site-admin' => Role::where('slug', 'site-admin')->first()->id
        ];

        // Add default permissions
        $permissions = [
            'create_group' => new Permission([
                'slug' => 'create_group',
                'name' => 'Create group',
                'conditions' => 'always()',
                'description' => 'Create a new group.'
            ]),
            'create_user' => new Permission([
                'slug' => 'create_user',
                'name' => 'Create user',
                'conditions' => 'always()',
                'description' => 'Create a new user in your own group and assign default roles.'
            ]),
            'create_user_field' => new Permission([
                'slug' => 'create_user_field',
                'name' => 'Set new user group',
                'conditions' => "subset(fields,['group'])",
                'description' => 'Set the group when creating a new user.'
            ]),
            'delete_group' => new Permission([
                'slug' => 'delete_group',
                'name' => 'Delete group',
                'conditions' => "always()",
                'description' => 'Delete a group.'
            ]),
            'delete_user' => new Permission([
                'slug' => 'delete_user',
                'name' => 'Delete user',
                'conditions' => "!has_role(user.id,{$defaultRoleIds['site-admin']}) && !is_master(user.id)",
                'description' => 'Delete users who are not Site Administrators.'
            ]),
            'update_account_settings' => new Permission([
                'slug' => 'update_account_settings',
                'name' => 'Edit user',
                'conditions' => 'always()',
                'description' => 'Edit your own account settings.'
            ]),
            'update_group_field' => new Permission([
                'slug' => 'update_group_field',
                'name' => 'Edit group',
                'conditions' => 'always()',
                'description' => 'Edit basic properties of any group.'
            ]),
            'update_user_field' => new Permission([
                'slug' => 'update_user_field',
                'name' => 'Edit user',
                'conditions' => "!has_role(user.id,{$defaultRoleIds['site-admin']}) && subset(fields,['name','email','locale','group','flag_enabled','flag_verified','password'])",
                'description' => 'Edit users who are not Site Administrators.'
            ]),
            'update_user_field_group' => new Permission([
                'slug' => 'update_user_field',
                'name' => 'Edit group user',
                'conditions' => "equals_num(self.group_id,user.group_id) && !is_master(user.id) && !has_role(user.id,{$defaultRoleIds['site-admin']}) && (!has_role(user.id,{$defaultRoleIds['group-admin']}) || equals_num(self.id,user.id)) && subset(fields,['name','email','locale','flag_enabled','flag_verified','password'])",
                'description' => 'Edit users in your own group who are not Site or Group Administrators, except yourself.'
            ]),
            'uri_account_settings' => new Permission([
                'slug' => 'uri_account_settings',
                'name' => 'Account settings page',
                'conditions' => 'always()',
                'description' => 'View the account settings page.'
            ]),
            'uri_activities' => new Permission([
                'slug' => 'uri_activities',
                'name' => 'Activity monitor',
                'conditions' => 'always()',
                'description' => 'View a list of all activities for all users.'
            ]),
            'uri_dashboard' => new Permission([
                'slug' => 'uri_dashboard',
                'name' => 'Admin dashboard',
                'conditions' => 'always()',
                'description' => 'View the administrative dashboard.'
            ]),
            'uri_group' => new Permission([
                'slug' => 'uri_group',
                'name' => 'View group',
                'conditions' => 'always()',
                'description' => 'View the group page of any group.'
            ]),
            'uri_group_own' => new Permission([
                'slug' => 'uri_group',
                'name' => 'View own group',
                'conditions' => 'equals_num(self.group_id,group.id)',
                'description' => 'View the group page of your own group.'
            ]),
            'uri_groups' => new Permission([
                'slug' => 'uri_groups',
                'name' => 'Group management page',
                'conditions' => 'always()',
                'description' => 'View a page containing a list of groups.'
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
                'conditions' => "equals_num(self.group_id,user.group_id) && !is_master(user.id) && !has_role(user.id,{$defaultRoleIds['site-admin']}) && (!has_role(user.id,{$defaultRoleIds['group-admin']}) || equals_num(self.id,user.id))",
                'description' => 'View the user page of any user in your group, except the master user and Site and Group Administrators (except yourself).'
            ]),
            'uri_users' => new Permission([
                'slug' => 'uri_users',
                'name' => 'User management page',
                'conditions' => 'always()',
                'description' => 'View a page containing a table of users.'
            ]),
            'view_group_field' => new Permission([
                'slug' => 'view_group_field',
                'name' => 'View group',
                'conditions' => "in(property,['name','icon','slug','description','users'])",
                'description' => 'View certain properties of any group.'
            ]),
            'view_group_field_own' => new Permission([
                'slug' => 'view_group_field',
                'name' => 'View group',
                'conditions' => "equals_num(self.group_id,group.id) && in(property,['name','icon','slug','description','users'])",
                'description' => 'View certain properties of your own group.'
            ]),
            'view_user_field' => new Permission([
                'slug' => 'view_user_field',
                'name' => 'View user',
                'conditions' => "in(property,['user_name','name','email','locale','theme','roles','group','activities'])",
                'description' => 'View certain properties of any user.'
            ]),
            'view_user_field_group' => new Permission([
                'slug' => 'view_user_field',
                'name' => 'View user',
                'conditions' => "equals_num(self.group_id,user.group_id) && !is_master(user.id) && !has_role(user.id,{$defaultRoleIds['site-admin']}) && (!has_role(user.id,{$defaultRoleIds['group-admin']}) || equals_num(self.id,user.id)) && in(property,['user_name','name','email','locale','roles','group','activities'])",
                'description' => 'View certain properties of any user in your own group, except the master user and Site and Group Administrators (except yourself).'
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
                $permissions['uri_account_settings']->id,
                $permissions['uri_dashboard']->id
            ]);
        }

        $roleSiteAdmin = Role::where('slug', 'site-admin')->first();
        if ($roleSiteAdmin) {
            $roleSiteAdmin->permissions()->sync([
                $permissions['create_group']->id,
                $permissions['create_user']->id,
                $permissions['create_user_field']->id,
                $permissions['delete_group']->id,
                $permissions['delete_user']->id,
                $permissions['update_user_field']->id,
                $permissions['update_group_field']->id,
                $permissions['uri_activities']->id,
                $permissions['uri_group']->id,
                $permissions['uri_groups']->id,
                $permissions['uri_user']->id,
                $permissions['uri_users']->id,
                $permissions['view_group_field']->id,
                $permissions['view_user_field']->id
            ]);
        }

        $roleGroupAdmin = Role::where('slug', 'group-admin')->first();
        if ($roleGroupAdmin) {
            $roleGroupAdmin->permissions()->sync([
                $permissions['create_user']->id,
                $permissions['update_user_field_group']->id,
                $permissions['uri_group_own']->id,
                $permissions['uri_user_in_group']->id,
                $permissions['view_group_field_own']->id,
                $permissions['view_user_field_group']->id
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

    // Make sure that there are no users currently in the user table
    // We setup the root account here so it can be done independent of the version check
    if (User::count() > 0) {

        echo PHP_EOL . "Table 'users' is not empty. Skipping root account setup. To set up the root account again, please truncate or drop the table and try again." . PHP_EOL;

    } else {

        echo PHP_EOL . 'To complete the installation process, you must set up a master (root) account.' . PHP_EOL;
        echo 'Please answer the following questions to complete this process:' . PHP_EOL;

        // Username
        echo PHP_EOL . 'Please choose a username (1-50 characters, no leading or trailing whitespace): ';
        $user_name = rtrim(fgets(STDIN), "\r\n");
        while (strlen($user_name) < 1 || strlen($user_name) > 50 || !filter_var($user_name, FILTER_VALIDATE_REGEXP, [
            'options' => [
                'regexp' => "/^\S((.*\S)|)$/"
            ]
        ])) {
            echo PHP_EOL . "Invalid username '$user_name', please try again: ";
            $user_name = rtrim(fgets(STDIN), "\r\n");
        }

        // Email
        echo PHP_EOL . 'Please choose a valid email address (1-254 characters, must be compatible with FILTER_VALIDATE_EMAIL): ';
        $email = rtrim(fgets(STDIN), "\r\n");
        while (strlen($email) < 1 || strlen($email) > 254 || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo PHP_EOL . "Invalid email '$email', please try again: ";
            $email = rtrim(fgets(STDIN), "\r\n");
        }

        // First name
        echo PHP_EOL . 'Please enter your first name (1-20 characters): ';
        $first_name = rtrim(fgets(STDIN), "\r\n");
        while (strlen($first_name) < 1 || strlen($first_name) > 20) {
            echo PHP_EOL . "Invalid first name '$first_name', please try again: ";
            $first_name = rtrim(fgets(STDIN), "\r\n");
        }

        // Last name
        echo PHP_EOL . 'Please enter your last name (1-30 characters): ';
        $last_name = rtrim(fgets(STDIN), "\r\n");
        while (strlen($last_name) < 1 || strlen($last_name) > 30) {
            echo PHP_EOL . "Invalid last name '$last_name', please try again: ";
            $last_name = rtrim(fgets(STDIN), "\r\n");
        }

        // Password
        echo PHP_EOL . 'Please choose a password (12-255 characters): ';
        $password = readPassword($detectedOS);
        while (strlen($password) < 12 || strlen($password) > 255) {
            echo PHP_EOL . 'Invalid password, please try again: ';
            $password = readPassword($detectedOS);
        }

        // Confirm password
        echo PHP_EOL . 'Please re-enter your chosen password: ';
        $password_confirm = readPassword($detectedOS);
        while ($password !== $password_confirm) {
            echo PHP_EOL . 'Passwords do not match, please try again. ';
            echo PHP_EOL . 'Please choose a password (12-255 characters): ';
            $password = readPassword($detectedOS);
            while (strlen($password) < 12 || strlen($password) > 255) {
                echo PHP_EOL . 'Invalid password, please try again: ';
                $password = readPassword($detectedOS);
            }
            echo PHP_EOL . 'Please re-enter your chosen password: ';
            $password_confirm = readPassword($detectedOS);
        }

        // To make output pretty...
        echo PHP_EOL;

        // Ok, now we've got the info and we can create the new user.

        $rootUser = new User([
            "user_name" => $user_name,
            "email" => $email,
            "first_name" => $first_name,
            "last_name" => $last_name,
            "theme" => 'root',
            "password" => Password::hash($password)
        ]);

        $rootUser->save();

        $defaultRoles = [
            'user' => Role::where('slug', 'user')->first(),
            'group-admin' => Role::where('slug', 'group-admin')->first(),
            'site-admin' => Role::where('slug', 'site-admin')->first()
        ];

        foreach ($defaultRoles as $slug => $role) {
            if ($role) {
                $rootUser->roles()->attach($role->id);
            }
        }
    }