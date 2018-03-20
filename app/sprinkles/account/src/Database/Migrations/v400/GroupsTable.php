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
use UserFrosting\Sprinkle\Account\Database\Models\Group;
use UserFrosting\Sprinkle\Core\Database\Migration;

/**
 * Groups table migration
 * "Group" now replaces the notion of "primary group" in earlier versions of UF.  A user can belong to exactly one group.
 * Version 4.0.0
 *
 * See https://laravel.com/docs/5.4/migrations#tables
 * @extends Migration
 * @author Alex Weissman (https://alexanderweissman.com)
 */
class GroupsTable extends Migration
{
    /**
     * {@inheritDoc}
     */
    public function up()
    {
        if (!$this->schema->hasTable('groups')) {
            $this->schema->create('groups', function(Blueprint $table) {
                $table->increments('id');
                $table->string('slug');
                $table->string('name');
                $table->text('description')->nullable();
                $table->string('icon', 100)->nullable(false)->default('fa fa-user')->comment('The icon representing users in this group.');
                $table->timestamps();

                $table->engine = 'InnoDB';
                $table->collation = 'utf8_unicode_ci';
                $table->charset = 'utf8';
                $table->unique('slug');
                $table->index('slug');
            });

            // Add default groups
            $groups = [
                'terran' => new Group([
                    'slug' => 'terran',
                    'name' => 'Terran',
                    'description' => 'The terrans are a young species with psionic potential. The terrans of the Koprulu sector descend from the survivors of a disastrous 23rd century colonization mission from Earth.',
                    'icon' => 'sc sc-terran'
                ]),
                'zerg' => new Group([
                    'slug' => 'zerg',
                    'name' => 'Zerg',
                    'description' => 'Dedicated to the pursuit of genetic perfection, the zerg relentlessly hunt down and assimilate advanced species across the galaxy, incorporating useful genetic code into their own.',
                    'icon' => 'sc sc-zerg'
                ]),
                'protoss' => new Group([
                    'slug' => 'protoss',
                    'name' => 'Protoss',
                    'description' => 'The protoss, a.k.a. the Firstborn, are a sapient humanoid race native to Aiur. Their advanced technology complements and enhances their psionic mastery.',
                    'icon' => 'sc sc-protoss'
                ])
            ];

            foreach ($groups as $slug => $group) {
                $group->save();
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    public function down()
    {
        $this->schema->drop('groups');
    }
}
