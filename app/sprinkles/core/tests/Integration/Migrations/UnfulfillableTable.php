<?php

namespace UserFrosting\Tests\Integration\Migrations;

use Illuminate\Database\Schema\Blueprint;
use UserFrosting\Sprinkle\Core\Database\Migration;

/**
 *    This migration is not fulfulable because it's dependencie are not met !
 */
class UnfulfillableTable extends Migration
{
     /**
      * {@inheritDoc}
      */
    static public $dependencies = [
        '\UserFrosting\Tests\Integration\Migrations\NonExistingMigration'
    ];

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->schema->create('unfulfillable', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->schema->dropIfExists('unfulfillable');
    }
}
