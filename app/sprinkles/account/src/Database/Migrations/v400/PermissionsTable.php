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
use UserFrosting\Sprinkle\Account\Database\Models\Permission;
use UserFrosting\Sprinkle\Core\Database\Migration;
use UserFrosting\Sprinkle\Core\Facades\Seeder;

/**
 * Permissions table migration
 * Permissions now replace the 'authorize_group' and 'authorize_user' tables.
 * Also, they now map many-to-many to roles.
 * Version 4.0.0
 *
 * See https://laravel.com/docs/5.4/migrations#tables
 * @author Alex Weissman (https://alexanderweissman.com)
 */
class PermissionsTable extends Migration
{
    /**
     * {@inheritdoc}
     */
    public static $dependencies = [
        '\UserFrosting\Sprinkle\Account\Database\Migrations\v400\RolesTable',
        '\UserFrosting\Sprinkle\Account\Database\Migrations\v400\PermissionRolesTable'
    ];

    /**
     * {@inheritdoc}
     */
    public function up()
    {
        if (!$this->schema->hasTable('permissions')) {
            $this->schema->create('permissions', function (Blueprint $table) {
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
        }

        // Skip this if table is not empty
        if (Permission::count() == 0) {
            Seeder::execute('DefaultPermissions');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function down()
    {
        $this->schema->drop('permissions');
    }
}
