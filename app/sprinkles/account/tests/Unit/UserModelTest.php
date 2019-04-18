<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Account\Tests\Unit;

use UserFrosting\Sprinkle\Account\Database\Models\User;
use UserFrosting\Sprinkle\Account\Tests\withTestUser;
use UserFrosting\Sprinkle\Core\Tests\TestDatabase;
use UserFrosting\Sprinkle\Core\Tests\RefreshDatabase;
use UserFrosting\Tests\TestCase;

/**
 * UserModelTest Class
 * Tests the User Model.
 */
class UserModelTest extends TestCase
{
    use TestDatabase;
    use RefreshDatabase;
    use withTestUser;

    /**
     * Setup the database schema.
     */
    public function setUp()
    {
        parent::setUp();

        // Setup test database
        $this->setupTestDatabase();
        $this->refreshDatabase();
    }

    /**
     * Test user soft deletion.
     */
    public function testUserSoftDelete()
    {
        // Create a user & make sure it exist
        $user = $this->createTestUser();
        $this->assertInstanceOf(User::class, User::withTrashed()->find($user->id));

        // Soft Delete. User won't be found using normal query, but will withTrash
        $this->assertTrue($user->delete());
        $this->assertNull(User::find($user->id));
        $this->assertInstanceOf(User::class, User::withTrashed()->find($user->id));
    }

    /**
     * Test user hard deletion.
     */
    public function testUserHardDelete()
    {
        // Create a user & make sure it exist
        $user = $this->createTestUser();
        $this->assertInstanceOf(User::class, User::withTrashed()->find($user->id));

        // Force delete. Now user can't be found at all
        $this->assertTrue($user->delete(true));
        $this->assertNull(User::withTrashed()->find($user->id));
    }
}
