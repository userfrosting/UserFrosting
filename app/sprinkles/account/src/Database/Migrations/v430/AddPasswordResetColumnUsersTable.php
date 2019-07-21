<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Account\Database\Migrations\v430;

use Illuminate\Database\Schema\Blueprint;
use UserFrosting\Sprinkle\Core\Database\Migration;

/**
 * Users table migration
 * Adds a `flag_password_reset_required` column to the users table.
 * Version 4.3.0.
 *
 * See https://laravel.com/docs/5.8/migrations#tables
 *
 * @author Amos Folz
 */
class AddPasswordResetColumnUsersTable extends Migration
{
    /**
     * {@inheritdoc}
     */
    public static $dependencies = [
        '\UserFrosting\Sprinkle\Account\Database\Migrations\v400\UsersTable',
    ];

    /**
     * {@inheritdoc}
     */
    public function up()
    {
        if ($this->schema->hasTable('users')) {
            $this->schema->table('users', function (Blueprint $table) {
                $table->boolean('flag_password_reset_required')
                ->default(0)->comment('Set to 1 to force user to reset their password, 0 otherwise.')
                ->after('flag_enabled');
            });
        }
    }

    /**
     * {@inheritdoc}
     */
    public function down()
    {
        $this->schema->table('users', function (Blueprint $table) {
            $table->dropColumn('flag_password_reset_required');
        });
    }
}
