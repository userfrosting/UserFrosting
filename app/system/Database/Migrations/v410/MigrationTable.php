<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */
namespace UserFrosting\System\Database\Migrations\v410;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\Builder;
use UserFrosting\System\Bakery\Migration;

/**
 * Migration table migration
 * Version 4.1.0
 *
 * See https://laravel.com/docs/5.4/migrations#tables
 * @extends Migration
 * @author Alex Weissman (https://alexanderweissman.com)
 */
class MigrationTable extends Migration
{
    /**
     * {@inheritDoc}
     */
    public $dependencies = [];

    /**
     * {@inheritDoc}
     */
    public function up()
    {
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
    public function down()
    {
        $this->schema->drop('migrations');
    }
}
