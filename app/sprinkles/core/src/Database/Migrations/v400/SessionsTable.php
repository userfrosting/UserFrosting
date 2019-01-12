<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Database\Migrations\v400;

use Illuminate\Database\Schema\Blueprint;
use UserFrosting\Sprinkle\Core\Database\Migration;

/**
 * Sessions table migration
 * Version 4.0.0
 *
 * See https://laravel.com/docs/5.4/migrations#tables
 * @author Alex Weissman (https://alexanderweissman.com)
 */
class SessionsTable extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function up()
    {
        if (!$this->schema->hasTable('sessions')) {
            $this->schema->create('sessions', function (Blueprint $table) {
                $table->string('id')->unique();
                $table->integer('user_id')->nullable();
                $table->string('ip_address', 45)->nullable();
                $table->text('user_agent')->nullable();
                $table->text('payload');
                $table->integer('last_activity');
            });
        }
    }

    /**
     * {@inheritdoc}
     */
    public function down()
    {
        $this->schema->drop('sessions');
    }
}
