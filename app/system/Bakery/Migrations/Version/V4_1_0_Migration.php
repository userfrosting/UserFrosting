<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2013-2016 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */
namespace UserFrosting\System\Bakery\Migrations\Version;

use UserFrosting\System\Bakery\Migrations\MigrationInterface;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\Builder;

/**
 * Version table migration
 * Version 4.1.0
 *
 * See https://laravel.com/docs/5.4/migrations#tables
 * @implements MigrationInterface
 * @author Alex Weissman (https://alexanderweissman.com)
 */
class V4_1_0_Migration implements MigrationInterface
{
    public function up(Builder $schema) {
        $schema->table('version', function (Blueprint $table) {
            $table->dropUnique(['sprinkle']);
            $table->increments('id')->first();
        });
    }

    public function down(Builder $schema) {
        // N.B.: While this may work when no data is in the table, it won't
        // work once there new data inserted into that table. This is not called by the Migration
        // system anyway, and only serves as an example
        $schema->table('version', function (Blueprint $table) {
            $table->dropColumn('id');
            $table->unique('sprinkle');
        });
    }
}