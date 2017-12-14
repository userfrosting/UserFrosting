<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */
namespace UserFrosting\Sprinkle\Account\Database\Migrations\v400;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Schema\Builder;
use UserFrosting\System\Bakery\Migration;

/**
 * Users table migration
 * Removed the 'display_name', 'title', 'secret_token', and 'flag_password_reset' fields, and added first and last name and 'last_activity_id'.
 * Version 4.0.0
 *
 * See https://laravel.com/docs/5.4/migrations#tables
 * @extends Migration
 * @author Alex Weissman (https://alexanderweissman.com)
 */
class UsersTable extends Migration
{
    /**
     * {@inheritDoc}
     */
    public function up()
    {
        if (!$this->schema->hasTable('users')) {
            $this->schema->create('users', function (Blueprint $table) {
                $table->increments('id');
                $table->string('user_name', 50);
                $table->string('email', 254);
                $table->string('first_name', 20);
                $table->string('last_name', 30);
                $table->string('locale', 10)->default('en_US')->comment('The language and locale to use for this user.');
                $table->string('theme', 100)->nullable()->comment("The user theme.");
                $table->integer('group_id')->unsigned()->default(1)->comment("The id of the user group.");
                $table->boolean('flag_verified')->default(1)->comment("Set to 1 if the user has verified their account via email, 0 otherwise.");
                $table->boolean('flag_enabled')->default(1)->comment("Set to 1 if the user account is currently enabled, 0 otherwise.  Disabled accounts cannot be logged in to, but they retain all of their data and settings.");
                $table->integer('last_activity_id')->unsigned()->nullable()->comment("The id of the last activity performed by this user.");
                $table->string('password', 255);
                $table->softDeletes();
                $table->timestamps();

                $table->engine = 'InnoDB';
                $table->collation = 'utf8_unicode_ci';
                $table->charset = 'utf8';
                //$table->foreign('group_id')->references('id')->on('groups');
                //$table->foreign('last_activity_id')->references('id')->on('activities');
                $table->unique('user_name');
                $table->index('user_name');
                $table->unique('email');
                $table->index('email');
                $table->index('group_id');
                $table->index('last_activity_id');
            });
        }
    }

    /**
     * {@inheritDoc}
     */
    public function down()
    {
        $this->schema->drop('users');
    }
}
