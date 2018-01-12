<?php

namespace UserFrosting\Tests\Integration\Migrations;

use Illuminate\Database\Schema\Blueprint;
use UserFrosting\System\Bakery\Migration;

class DeprecatedClassTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->schema->create('deprecated_table', function (Blueprint $table) {
            $table->string('email')->index();
            $table->string('token')->index();
            $table->timestamp('created_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->schema->dropIfExists('deprecated_table');
    }
}
