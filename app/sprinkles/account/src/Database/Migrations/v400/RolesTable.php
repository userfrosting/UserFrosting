<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */
namespace UserFrosting\Sprinkle\Account\Database\Migrations\v400;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\Builder;
use UserFrosting\Sprinkle\Account\Database\Models\Role;
use UserFrosting\Sprinkle\Core\Database\Migration;

/**
 * Roles table migration
 * Roles replace "groups" in UF 0.3.x.  Users acquire permissions through roles.
 * Version 4.0.0
 *
 * See https://laravel.com/docs/5.4/migrations#tables
 * @extends Migration
 * @author Alex Weissman (https://alexanderweissman.com)
 */
class RolesTable extends Migration
{
    /**
     * {@inheritDoc}
     */
    public function up()
    {
        if (!$this->schema->hasTable('roles')) {
            $this->schema->create('roles', function (Blueprint $table) {
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
        }
    }

    /**
     * {@inheritDoc}
     */
    public function down()
    {
        $this->schema->drop('roles');
    }
}
