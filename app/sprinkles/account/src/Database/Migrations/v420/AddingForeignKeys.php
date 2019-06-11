<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Account\Database\Migrations\v420;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\SQLiteConnection;
use UserFrosting\Sprinkle\Core\Database\Migration;

/**
 * Foreign Keys migration
 * Adds missing foreign keys from 4.0.0 migrations
 * Version 4.2.0.
 */
class AddingForeignKeys extends Migration
{
    /**
     * @var array List of operation to do
     */
    protected $tables = [
        'activities' => [
            'user_id' => ['id', 'users'],
        ],
        'password_resets' => [
            'user_id' => ['id', 'users'],
        ],
        'permission_roles' => [
            'permission_id' => ['id', 'permissions'],
            'role_id'       => ['id', 'roles'],
        ],
        'persistences' => [
            'user_id' => ['id', 'users'],
        ],
        'role_users' => [
            'user_id' => ['id', 'users'],
            'role_id' => ['id', 'roles'],
        ],
        'users' => [
            'group_id'         => ['id', 'groups'],
            'last_activity_id' => ['id', 'activities'],
        ],
        'verifications' => [
            'user_id' => ['id', 'users'],
        ],
    ];

    /**
     * {@inheritdoc}
     */
    public function up()
    {
        foreach ($this->tables as $tableName => $keys) {
            if ($this->schema->hasTable($tableName)) {
                $this->schema->table($tableName, function (Blueprint $table) use ($keys) {
                    foreach ($keys as $key => $data) {
                        $table->foreign($key)->references($data[0])->on($data[1]);
                    }
                });
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function down()
    {
        /*
         * sqlite can't drop foreign key wihout dropping the entire table
         * since Laravel 5.7. Skip drop if an sqlite connection is detected
         * @see https://github.com/laravel/framework/issues/25475
         */
        if ($this->schema->getConnection() instanceof SQLiteConnection) {
            return;
        }

        foreach ($this->tables as $tableName => $keys) {
            if ($this->schema->hasTable($tableName)) {
                $this->schema->table($tableName, function (Blueprint $table) use ($keys) {
                    foreach ($keys as $key => $data) {
                        $table->dropForeign([$key]);
                    }
                });
            }
        }
    }
}
