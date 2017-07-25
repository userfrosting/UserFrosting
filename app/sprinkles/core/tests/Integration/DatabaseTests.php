<?php

namespace UserFrosting\Tests\Integration;

use Exception;

use UserFrosting\Tests\TestCase;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Capsule\Manager as DB;

use UserFrosting\Sprinkle\Core\Database\Models\Model;

use Illuminate\Database\Eloquent\Relations\Relation;

class DatabaseTests extends TestCase
{
    protected $schemaName = 'test_integration';

    /**
     * Setup the database schema.
     *
     * @return void
     */
    public function setUp()
    {
        // Boot parent TestCase, which will set up the database and connections for us.
        parent::setUp();

        // Boot database
        $this->ci->db;

        $this->createSchema();
    }

    protected function createSchema()
    {
        $this->schema($this->schemaName)->create('users', function ($table) {
            $table->increments('id');
            $table->string('name')->nullable();
        });

        // Users have multiple email addresses
        $this->schema($this->schemaName)->create('emails', function ($table) {
            $table->increments('id');
            $table->integer('user_id');
            $table->string('label');
            $table->string('email');
        });

        // Users have multiple phones (polymorphic - other entities can have phones as well)
        $this->schema($this->schemaName)->create('phones', function ($table) {
            $table->increments('id');
            $table->string('label');
            $table->string('number', 20);
            $table->morphs('phoneable');
        });

        // Users have multiple roles... (m:m)
        $this->schema($this->schemaName)->create('role_users', function ($table) {
            $table->integer('user_id')->unsigned();
            $table->integer('role_id')->unsigned();
        });

        $this->schema($this->schemaName)->create('roles', function ($table) {
            $table->increments('id');
            $table->string('slug');
        });

        // And Roles have multiple permissions... (m:m)
        $this->schema($this->schemaName)->create('permission_roles', function ($table) {
            $table->integer('permission_id')->unsigned();
            $table->integer('role_id')->unsigned();
        });

        $this->schema($this->schemaName)->create('permissions', function($table) {
            $table->increments('id');
            $table->string('slug');
        });

        // A user can be assigned to a specific task at a specific location
        $this->schema($this->schemaName)->create('tasks', function($table) {
            $table->increments('id');
            $table->string('name');
        });

        $this->schema($this->schemaName)->create('locations', function($table) {
            $table->increments('id');
            $table->string('name');
        });

        $this->schema($this->schemaName)->create('assignments', function($table) {
            $table->integer('user_id')->unsigned();
            $table->integer('task_id')->unsigned();
            $table->integer('location_id')->unsigned();
        });
    }

    /**
     * Tear down the database schema.
     *
     * @return void
     */
    public function tearDown()
    {
        $this->schema($this->schemaName)->drop('users');
        $this->schema($this->schemaName)->drop('emails');
        $this->schema($this->schemaName)->drop('phones');
        $this->schema($this->schemaName)->drop('role_users');
        $this->schema($this->schemaName)->drop('roles');
        $this->schema($this->schemaName)->drop('permission_roles');
        $this->schema($this->schemaName)->drop('permissions');
        $this->schema($this->schemaName)->drop('tasks');
        $this->schema($this->schemaName)->drop('locations');
        $this->schema($this->schemaName)->drop('assignments');

        Relation::morphMap([], false);
    }

    /**
     * Tests...
     */
    public function testOneToManyRelationship()
    {
        $user = EloquentTestUser::create(['name' => 'David']);
        $user->emails()->create([
            'label' => 'primary',
            'email' => 'david@owlfancy.com'
        ]);
        $user->emails()->create([
            'label' => 'work',
            'email' => 'david@attenboroughsreef.com'
        ]);

        $emails = $user->emails;

        $this->assertInstanceOf('Illuminate\Database\Eloquent\Collection', $emails);
        $this->assertEquals(2, $emails->count());
        $this->assertInstanceOf('UserFrosting\Tests\Integration\EloquentTestEmail', $emails[0]);
        $this->assertInstanceOf('UserFrosting\Tests\Integration\EloquentTestEmail', $emails[1]);
    }

    /**
     * Tests our custom HasManySyncable class.
     */
    public function testSyncOneToMany()
    {
        $user = EloquentTestUser::create(['name' => 'David']);
        // Set up original emails
        $user->emails()->create([
            'id'    => 1,
            'label' => 'primary',
            'email' => 'david@owlfancy.com'
        ]);
        $user->emails()->create([
            'id' => 2,
            'label' => 'work',
            'email' => 'david@attenboroughsreef.com'
        ]);

        // Delete `work`, update `primary`, and add `gmail`
        $user->emails()->sync([
            [
                'id' => 1,
                'email' => 'david@aol.com'
            ],
            [
                'label' => 'gmail',
                'email' => 'davidattenborough@gmail.com'
            ]
        ]);

        $emails = $user->emails->toArray();

        $this->assertEquals([
            [
                'id' => 1,
                'user_id'=> 1,
                'label' => 'primary',
                'email' => 'david@aol.com'
            ],
            [
                'id' => 3,
                'user_id' => 1,
                'label' => 'gmail',
                'email' => 'davidattenborough@gmail.com'
            ]
        ], $emails);
    }

    /**
     * Tests our custom MorphManySyncable class.
     */
    public function testSyncMorphMany()
    {
        $user = EloquentTestUser::create(['name' => 'David']);
        // Set up original phones
        $user->phones()->create([
            'id'    => 1,
            'label' => 'primary',
            'number' => '5555551212'
        ]);
        $user->phones()->create([
            'id' => 2,
            'label' => 'work',
            'number' => '2223334444'
        ]);

        // Delete `work`, update `primary`, and add `fax`
        $user->phones()->sync([
            [
                'id' => 1,
                'number' => '8883332222'
            ],
            [
                'label' => 'fax',
                'number' => '5550005555'
            ]
        ]);

        $phones = $user->phones->toArray();

        $this->assertEquals([
            [
                'id' => 1,
                'phoneable_id'=> 1,
                'phoneable_type' => 'UserFrosting\Tests\Integration\EloquentTestUser',
                'label' => 'primary',
                'number' => '8883332222'
            ],
            [
                'id' => 3,
                'phoneable_id'=> 1,
                'phoneable_type' => 'UserFrosting\Tests\Integration\EloquentTestUser',
                'label' => 'fax',
                'number' => '5550005555'
            ]
        ], $phones);
    }

    /**
     * Test the ability of a BelongsToManyThrough relationship to retrieve structured data on a single model or set of models.
     */
    public function testBelongsToManyThrough()
    {
        $this->generateRolesWithPermissions();

        $user = EloquentTestUser::create(['name' => 'David']);

        $user->roles()->attach([1,2]);

        // Test retrieval of via models as well
        $this->assertEquals([
            [
                'id' => 1,
                'slug' => 'uri_harvest'
            ],
            [
                'id' => 2,
                'slug' => 'uri_spit_acid'
            ],
            [
                'id' => 3,
                'slug' => 'uri_slash'
            ]
        ], $user->permissions->toArray());

        $user2 = EloquentTestUser::create(['name' => 'Alex']);
        $user2->roles()->attach([2,3]);

        $users = EloquentTestUser::with('permissions')->get();
        $usersWithPermissions = $users->toArray();

        $this->assertEquals([
            [
                'id' => 2,
                'slug' => 'uri_spit_acid'
            ],
            [
                'id' => 3,
                'slug' => 'uri_slash'
            ],
            [
                'id' => 4,
                'slug' => 'uri_royal_jelly'
            ]
        ],$usersWithPermissions[1]['permissions']);
    }

    /**
     * Test the ability of a BelongsToManyThrough relationship to retrieve structured data on a single model or set of models,
     * eager loading the "via" models at the same time.
     */
    public function testBelongsToManyThroughWithVia()
    {
        $this->generateRolesWithPermissions();

        $user = EloquentTestUser::create(['name' => 'David']);

        $user->roles()->attach([1,2]);

        // Test retrieval of via models as well
        $this->assertBelongsToManyThroughForDavid($user->permissions()->withVia('roles_via')->get()->toArray());

        $user2 = EloquentTestUser::create(['name' => 'Alex']);
        $user2->roles()->attach([2,3]);

        $users = EloquentTestUser::with(['permissions' => function ($query) {
            return $query->withVia('roles_via');
        }])->get();
        $usersWithPermissions = $users->toArray();

        $this->assertBelongsToManyThroughForDavid($usersWithPermissions[0]['permissions']);
        $this->assertBelongsToManyThroughForAlex($usersWithPermissions[1]['permissions']);
    }

    /**
     * Helpers...
     */

    /**
     * Get a database connection instance.
     *
     * @return \Illuminate\Database\Connection
     */
    protected function connection($connection = 'test_integration')
    {
        return Model::getConnectionResolver()->connection($connection);
    }

    /**
     * Get a schema builder instance.
     *
     * @return \Illuminate\Database\Schema\Builder
     */
    protected function schema($connection = 'test_integration')
    {
        return $this->connection($connection)->getSchemaBuilder();
    }

    protected function generateRolesWithPermissions()
    {
        $role1 = EloquentTestRole::create([
            'id' => 1,
            'slug' => 'forager'
        ]);

        $role2 = EloquentTestRole::create([
            'id' => 2,
            'slug' => 'soldier'
        ]);

        $role3 = EloquentTestRole::create([
            'id' => 3,
            'slug' => 'egg-layer'
        ]);

        $permission1 = EloquentTestPermission::create([
            'id' => 1,
            'slug' => 'uri_harvest'
        ]);

        $permission2 = EloquentTestPermission::create([
            'id' => 2,
            'slug' => 'uri_spit_acid'
        ]);

        $permission3 = EloquentTestPermission::create([
            'id' => 3,
            'slug' => 'uri_slash'
        ]);

        $permission4 = EloquentTestPermission::create([
            'id' => 4,
            'slug' => 'uri_royal_jelly'
        ]);

        $role1->permissions()->attach([1,2]);
        // We purposefully want a permission that belongs to more than one role
        $role2->permissions()->attach([2,3]);
        $role3->permissions()->attach([2,4]);

        return [$role1, $role2, $role3];
    }

    protected function assertBelongsToManyThroughForDavid($permissions)
    {
        // User should have effective permissions uri_harvest, uri_spit_acid, and uri_slash.
        // We also check that the 'roles_via' relationship is properly set.
        $this->assertEquals('uri_harvest', $permissions[0]['slug']);
        $this->assertEquals([
            [
                'id' => 1,
                'slug' => 'forager'
            ]
        ], $permissions[0]['roles_via']);
        $this->assertEquals('uri_spit_acid', $permissions[1]['slug']);
        $this->assertEquals([
            [
                'id' => 1,
                'slug' => 'forager'
            ],
            [
                'id' => 2,
                'slug' => 'soldier'
            ]
        ], $permissions[1]['roles_via']);
        $this->assertEquals('uri_slash', $permissions[2]['slug']);
        $this->assertEquals([
            [
                'id' => 2,
                'slug' => 'soldier'
            ]
        ], $permissions[2]['roles_via']);
    }

    protected function assertBelongsToManyThroughForAlex($permissions)
    {
        // User should have effective permissions uri_spit_acid, uri_slash, and uri_royal_jelly.
        // We also check that the 'roles_via' relationship is properly set.
        $this->assertEquals('uri_spit_acid', $permissions[0]['slug']);
        $this->assertEquals([
            [
                'id' => 2,
                'slug' => 'soldier'
            ],
            [
                'id' => 3,
                'slug' => 'egg-layer'
            ]
        ], $permissions[0]['roles_via']);
        $this->assertEquals('uri_slash', $permissions[1]['slug']);
        $this->assertEquals([
            [
                'id' => 2,
                'slug' => 'soldier'
            ]
        ], $permissions[1]['roles_via']);
        $this->assertEquals('uri_royal_jelly', $permissions[2]['slug']);
        $this->assertEquals([
            [
                'id' => 3,
                'slug' => 'egg-layer'
            ]
        ], $permissions[2]['roles_via']);
    }
}

/**
 * Eloquent Models...
 */
class EloquentTestModel extends Model
{
    protected $connection = 'test_integration';
}

class EloquentTestUser extends EloquentTestModel
{
    protected $table = 'users';
    protected $guarded = [];

    public function emails()
    {
        return $this->hasMany('UserFrosting\Tests\Integration\EloquentTestEmail', 'user_id');
    }

    public function phones()
    {
        return $this->morphMany('UserFrosting\Tests\Integration\EloquentTestPhone', 'phoneable');
    }

    /**
     * Get all roles to which this user belongs.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function roles()
    {
        return $this->belongsToMany('UserFrosting\Tests\Integration\EloquentTestRole', 'role_users', 'user_id', 'role_id');
    }

    /**
     * Get all of the permissions this user has, via its roles.
     *
     * @return \UserFrosting\Sprinkle\Core\Database\Relations\BelongsToManyThrough
     */
    public function permissions()
    {
        return $this->belongsToManyThrough(
            'UserFrosting\Tests\Integration\EloquentTestPermission',
            'UserFrosting\Tests\Integration\EloquentTestRole',
            'role_users',
            'user_id',
            'role_id',
            'permission_roles',
            'role_id',
            'permission_id'
        );
    }

    public function tasks()
    {
        return $this->belongsToManyUnique(
            'UserFrosting\Tests\Integration\EloquentTestTask',
            'assignments',
            'user_id',
            'task_id'
        );
    }

    /**
     * Get all of the user's tasks, grouped by location.
     */
    public function assignments()
    {
        $relation = $this->belongsToManyConstrained(
            'UserFrosting\Tests\Integration\EloquentTestTask',
            'location_id',
            'assignments',
            'location_id',
            'task_id'
        );

        //Need to make sure we add the `enrollable_id` pivot for BelongsToManyConstrained to match
        //$relation = $relation->select('tasks.*')
        //                    ->withPivot('task_id');

        return $relation;
    }
}

class EloquentTestEmail extends EloquentTestModel
{
    protected $table = 'emails';
    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo('UserFrosting\Tests\Integration\EloquentTestUser', 'user_id');
    }
}

class EloquentTestPhone extends EloquentTestModel
{
    protected $table = 'phones';
    protected $guarded = [];

    public function phoneable()
    {
        return $this->morphTo();
    }
}

class EloquentTestRole extends EloquentTestModel
{
    protected $table = 'roles';
    protected $guarded = [];

    /**
     * Get a list of permissions assigned to this role.
     */
    public function permissions()
    {
        return $this->belongsToMany('UserFrosting\Tests\Integration\EloquentTestPermission', 'permission_roles', 'role_id', 'permission_id');
    }
}

class EloquentTestPermission extends EloquentTestModel
{
    protected $table = 'permissions';
    protected $guarded = [];

    /**
     * Get a list of roles that have this permission.
     */
    public function roles()
    {
        return $this->belongsToMany('UserFrosting\Tests\Integration\EloquentTestRole', 'permission_roles', 'permission_id', 'role_id');
    }
}

class EloquentTestTask extends EloquentTestModel
{
    protected $table = 'tasks';
    protected $guarded = [];

    public function locations()
    {
        return $this->belongsToMany(
            'UserFrosting\Tests\Integration\EloquentTestLocation',
            'assignments',
            'task_id',
            'location_id'
        );
    }
}

class EloquentTestLocation extends EloquentTestModel
{
    protected $table = 'locations';
    protected $guarded = [];
}

class EloquentTestAssignment extends EloquentTestModel
{
    protected $table = 'assignments';
    protected $guarded = [];
}
