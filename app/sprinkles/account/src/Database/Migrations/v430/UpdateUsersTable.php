<?php
/**
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
 * Groups table migration
 * Changes `group_id` column properties to allow user to be created without a group.
 * Version 4.3.0
 *
 * See https://laravel.com/docs/5.4/migrations#tables
 * @author Alex Weissman (https://alexanderweissman.com)
 */
class UpdateUsersTable extends Migration
{
    /**
     * {@inheritdoc}
     */
    public static $dependencies = [
        '\UserFrosting\Sprinkle\Account\Database\Migrations\v400\UsersTable'
    ];

    /**
     * {@inheritdoc}
     */
    public function up()
    {
        if ($this->schema->hasTable('users')) {
            $this->schema->table('users', function (Blueprint $table) {
                $table->integer('group_id')->unsigned()->default(null)->comment('The id of the user group.')->nullable()->change();
            });
        }
    }

    /**
     * {@inheritdoc}
     */
    public function down()
    {
        $this->schema->table('users', function (Blueprint $table) {
            $table->integer('group_id')->unsigned()->default(1)->comment('The id of the user group.')->nullable(false)->change();
        });
    }
}
