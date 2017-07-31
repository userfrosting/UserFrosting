<?php

namespace UserFrosting\Tests\Integration;

use Exception;
use Illuminate\Database\Capsule\Manager as DB;
use Symfony\Component\Console\Style\SymfonyStyle;
use UserFrosting\Sprinkle\Admin\Sprunje\UserPermissionSprunje;
use UserFrosting\Sprinkle\Core\Util\ClassMapper;
use UserFrosting\Tests\DatabaseTransactions;
use UserFrosting\Tests\TestCase;

/**
 * Integration tests for the built-in Sprunje classes.
 */
class SprunjeTests extends TestCase
{
    use DatabaseTransactions;

    protected $classMapper;

    /**
     * Setup the database schema.
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();

        $this->classMapper = new ClassMapper(); 
    }

    /**
     * Tests...
     */
    public function testUserPermissionSprunje()
    {
        $fm = $this->ci->factory;

        // Generate some test models
        $users = $fm->seed(5, 'UserFrosting\Sprinkle\Account\Database\Models\User');
        $roles = $fm->seed(3, 'UserFrosting\Sprinkle\Account\Database\Models\Role');
        $permissions = $fm->seed(3, 'UserFrosting\Sprinkle\Account\Database\Models\Permission');
        $permissions[0]->update([
            'name' => 'a'
        ]);
        $permissions[1]->update([
            'name' => 'b'
        ]);
        $permissions[2]->update([
            'name' => 'c'
        ]);

        // Create some relationships
        $roles[0]->permissions()->attach($permissions[1]);
        $roles[0]->permissions()->attach($permissions[2]);
        $roles[1]->permissions()->attach($permissions[2]);
        $roles[2]->permissions()->attach($permissions[0]);
        $roles[2]->permissions()->attach($permissions[1]);

        $users[0]->roles()->attach($roles[1]);
        $users[0]->roles()->attach($roles[2]);
        $users[1]->roles()->attach($roles[0]);
        $users[1]->roles()->attach($roles[1]);
        $users[2]->roles()->attach($roles[1]);

        $this->classMapper->setClassMapping('user', 'UserFrosting\Sprinkle\Account\Database\Models\User');

        $sprunje = new UserPermissionSprunje($this->classMapper, [
            'user_id' => $users[0]->id,
            'sorts' => [
                'name' => 'asc'
            ]
        ]);

        $result = $sprunje->getResults();

        // TODO: assert that deeply nested keys actually exist
        $this->assertArraySubset([
            $permissions[0]->toArray(),
            $permissions[1]->toArray(),
            $permissions[2]->toArray()
        ], $result['rows']);
        

    }
}
