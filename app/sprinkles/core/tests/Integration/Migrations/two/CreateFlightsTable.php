<?php

namespace UserFrosting\Tests\Integration\Migrations\two;

use Illuminate\Database\Schema\Blueprint;
use UserFrosting\Sprinkle\Core\Database\Migration;

class CreateFlightsTable extends Migration
{
    /**
     * {@inheritDoc}
     */
    public static $dependencies = [
        '\UserFrosting\Tests\Integration\Migrations\one\CreateUsersTable',
        '\UserFrosting\Tests\Integration\Migrations\one\CreatePasswordResetsTable'
    ];

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->schema->create('flights', function (Blueprint $table) {
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
        $this->schema->dropIfExists('flights');
    }
}
