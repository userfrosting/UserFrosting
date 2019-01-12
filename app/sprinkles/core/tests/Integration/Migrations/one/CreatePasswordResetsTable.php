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

class CreatePasswordResetsTable extends Migration
{
    /**
     * {@inheritdoc}
     */
    public static $dependencies = [];

    /**
     * Run the migrations.
     */
    public function up()
    {
        $this->schema->create('password_resets', function (Blueprint $table) {
            $table->string('email')->index();
            $table->string('token')->index();
            $table->timestamp('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        $this->schema->dropIfExists('password_resets');
    }
}
