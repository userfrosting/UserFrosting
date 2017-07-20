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
    protected $schemaName = 'integration';

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

        $this->schema($this->schemaName)->create('emails', function ($table) {
            $table->increments('id');
            $table->integer('user_id');
            $table->string('label');
            $table->string('email');
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
     * Helpers...
     */

    /**
     * Get a database connection instance.
     *
     * @return \Illuminate\Database\Connection
     */
    protected function connection($connection = 'integration')
    {
        return Model::getConnectionResolver()->connection($connection);
    }

    /**
     * Get a schema builder instance.
     *
     * @return \Illuminate\Database\Schema\Builder
     */
    protected function schema($connection = 'integration')
    {
        return $this->connection($connection)->getSchemaBuilder();
    }
}

/**
 * Eloquent Models...
 */
class EloquentTestModel extends Model
{
    protected $connection = 'integration';
}

class EloquentTestUser extends EloquentTestModel
{
    protected $table = 'users';
    protected $guarded = [];

    public function emails()
    {
        return $this->hasMany('UserFrosting\Tests\Integration\EloquentTestEmail', 'user_id');
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
