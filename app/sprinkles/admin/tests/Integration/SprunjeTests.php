<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Admin\Tests\Integration;

use UserFrosting\Sprinkle\Account\Database\Models\Permission;
use UserFrosting\Sprinkle\Account\Database\Models\Role;
use UserFrosting\Sprinkle\Account\Database\Models\User;
use UserFrosting\Sprinkle\Admin\Sprunje\UserPermissionSprunje;
use UserFrosting\Sprinkle\Core\Util\ClassMapper;
use UserFrosting\Tests\TestCase;
use UserFrosting\Sprinkle\Core\Tests\TestDatabase;
use UserFrosting\Sprinkle\Core\Tests\RefreshDatabase;

/**
 * Integration tests for the built-in Sprunje classes.
 */
class SprunjeTests extends TestCase
{
    use TestDatabase;
    use RefreshDatabase;

    /**
     *    @var ClassMapper
     */
    protected $classMapper;

    /**
     * Setup the database schema.
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->classMapper = new ClassMapper();

        // Setup test database
        $this->setupTestDatabase();
        $this->refreshDatabase();
    }

    /**
     * Tests...
     */
    public function testUserPermissionSprunje()
    {
        $fm = $this->ci->factory;

        // Generate some test models
        $users = $fm->seed(3, User::class);
        $roles = $fm->seed(3, Role::class);
        $permissions = $fm->seed(3, Permission::class);

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

        $this->classMapper->setClassMapping('user', User::class);

        // Test user 0
        $sprunje = new UserPermissionSprunje($this->classMapper, [
            'user_id' => $users[0]->id,
        ]);

        list($count, $countFiltered, $models) = $sprunje->getModels();

        // Check that counts are correct
        $this->assertEquals(count($models), $count);
        $this->assertEquals(count($models), $countFiltered);

        // Ignore pivot and roles_via.  These are covered by the tests for the relationships themselves.
        static::ignoreRelations($models);
        $this->assertCollectionsSame(collect($permissions), $models);

        // Test user 1
        $sprunje = new UserPermissionSprunje($this->classMapper, [
            'user_id' => $users[1]->id,
        ]);

        list($count, $countFiltered, $models) = $sprunje->getModels();

        // Check that counts are correct
        $this->assertEquals(count($models), $count);
        $this->assertEquals(count($models), $countFiltered);

        // Ignore pivot and roles_via.  These are covered by the tests for the relationships themselves.
        static::ignoreRelations($models);
        $this->assertCollectionsSame(collect([
            $permissions[1],
            $permissions[2],
        ]), $models);

        // Test user 2
        $sprunje = new UserPermissionSprunje($this->classMapper, [
            'user_id' => $users[2]->id,
        ]);

        list($count, $countFiltered, $models) = $sprunje->getModels();

        // Check that counts are correct
        $this->assertEquals(count($models), $count);
        $this->assertEquals(count($models), $countFiltered);

        // Ignore pivot and roles_via.  These are covered by the tests for the relationships themselves.
        static::ignoreRelations($models);
        $this->assertCollectionsSame(collect([
            $permissions[2],
        ]), $models);
    }
}
