<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Account\Database\Migrations\v400;

use Illuminate\Database\Schema\Blueprint;
use UserFrosting\Sprinkle\Core\Database\Migration;

/**
 * Role_users table migration
 * Many-to-many mapping between roles and users.
 * Version 4.0.0
 *
 * See https://laravel.com/docs/5.4/migrations#tables
 * @author Alex Weissman (https://alexanderweissman.com)
 */
class RoleUsersTable extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function up()
    {
        if (!$this->schema->hasTable('role_users')) {
            $this->schema->create('role_users', function (Blueprint $table) {
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
        }
    }

    /**
     * {@inheritdoc}
     */
    public function down()
    {
        $this->schema->drop('role_users');
    }
}
