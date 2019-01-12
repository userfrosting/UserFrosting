<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Tests\Integration\Migrations\one;

use Illuminate\Database\Schema\Blueprint;
use UserFrosting\Sprinkle\Core\Database\Migration;

class CreateUsersTable extends Migration
{
    /**
     * {@inheritdoc}
     *
     * N.B.: Not using static here to test old deprecated behavior
     */
    public $dependencies = [];

    /**
     * Run the migrations.
     */
    public function up()
    {
        $this->schema->create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        $this->schema->dropIfExists('users');
    }
}
