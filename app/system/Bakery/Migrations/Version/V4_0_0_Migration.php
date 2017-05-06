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
use Illuminate\Database\Schema\MySqlBuilder;

/**
 * Version table migration
 * Version 4.0.0
 *
 * See https://laravel.com/docs/5.4/migrations#tables
 * @implements MigrationInterface
 * @author Alex Weissman (https://alexanderweissman.com)
 */
class V4_0_0_Migration implements MigrationInterface
{
    public function up(MySqlBuilder $schema) {
        $schema->create('version', function (Blueprint $table) {
            $table->string('sprinkle', 45);
            $table->string('version', 25);
            $table->timestamps();

            $table->engine = 'InnoDB';
            $table->collation = 'utf8_unicode_ci';
            $table->charset = 'utf8';
            $table->unique('sprinkle');
        });
    }

    public function down(MySqlBuilder $schema) {
        $schema->drop('version');
    }
}