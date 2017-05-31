<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2013-2016 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */
namespace UserFrosting\System\Bakery\Migrations\v410;

use UserFrosting\System\Bakery\Migrations\UFMigration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\Builder;

/**
 * Migration table migration
 * Version 4.1.0
 *
 * See https://laravel.com/docs/5.4/migrations#tables
 * @extends UFMigration
 * @author Alex Weissman (https://alexanderweissman.com)
 */
class MigrationTable extends UFMigration
{
    /**
     * {@inheritDoc}
     */
    public $dependencies = [];

    /**
     * {@inheritDoc}
     */
    public function up() {
        $this->schema->create('migrations', function (Blueprint $table) {
            $table->increments('id');
            $table->string('sprinkle');
            $table->string('migration');
            $table->integer('batch');
            $table->timestamps();

            $table->engine = 'InnoDB';
            $table->collation = 'utf8_unicode_ci';
            $table->charset = 'utf8';
        });

        // Drop the old `version` table if found
        if ($this->schema->hasTable('version')) {
            $this->schema->drop('version');
        }
    }

    /**
     * {@inheritDoc}
     */
    public function down() {
        $this->schema->drop('migrations');
    }
}