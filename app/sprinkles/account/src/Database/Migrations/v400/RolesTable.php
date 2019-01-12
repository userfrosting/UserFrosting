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
 * Roles table migration
 * Roles replace "groups" in UF 0.3.x.  Users acquire permissions through roles.
 * N.B.: Default roles will be added in `DefaultPermissions` seed
 * Version 4.0.0
 *
 * See https://laravel.com/docs/5.4/migrations#tables
 * @author Alex Weissman (https://alexanderweissman.com)
 */
class RolesTable extends Migration
{
    /**
     * {@inheritdoc}
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
        }
    }

    /**
     * {@inheritdoc}
     */
    public function down()
    {
        $this->schema->drop('roles');
    }
}
