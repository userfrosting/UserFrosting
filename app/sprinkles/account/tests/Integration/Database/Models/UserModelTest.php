<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Account\Tests\Integration\Database\Models;

use UserFrosting\Sprinkle\Account\Database\Models\Role;
use UserFrosting\Sprinkle\Account\Database\Models\User;
use UserFrosting\Sprinkle\Account\Database\Models\Persistence;
use UserFrosting\Sprinkle\Account\Database\Models\Verification;
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
     * Test user hard deletion with user relations.
     * This is not a totaly acurate test, as each relations are added manually
     * and new relations might not be added automatically to accuratly test
     */
    public function testUserHardDeleteWithUserRelations()
    {
        $fm = $this->ci->factory;

        // Create a user & make sure it exist
        $user = $this->createTestUser();
        $this->assertInstanceOf(User::class, User::withTrashed()->find($user->id));

        //$user->activities - activities
        $this->ci->userActivityLogger->info("test", [
            'type'    => 'group_create',
            'user_id' => $user->id
        ]);
        $this->assertSame(1, $user->activities()->count());

        //$user->passwordResets - password_resets
        $this->ci->repoPasswordReset->create($user, $this->ci->config['password_reset.timeouts.reset']);
        $this->assertSame(1, $user->passwordResets()->count());

        //{no relations} - persistences
        $persistence = new Persistence([
            'user_id'          => $user->id,
            'token'            => '',
            'persistent_token' => '',
            'expires_at'       => null
        ]);
        $persistence->save();
        $this->assertSame(1, Persistence::where('user_id', $user->id)->count());

        //$user->roles - role_users
        $role = $fm->create('UserFrosting\Sprinkle\Account\Database\Models\Role');
        $user->roles()->attach($role->id);
        $this->assertSame(1, $user->roles()->count());

        //{no relations} - verification
        $this->ci->repoVerification->create($user, $this->ci->config['verification.timeout']);
        $this->assertSame(1, $this->ci->classMapper->staticMethod('verification', 'where', 'user_id', $user->id)->count());

        // Force delete. Now user can't be found at all
        $this->assertTrue($user->delete(true));
        $this->assertNull(User::withTrashed()->find($user->id));

        // Assert deletions worked
        $this->assertSame(0, $user->activities()->count());
        $this->assertSame(0, $user->passwordResets()->count());
        $this->assertSame(0, $user->roles()->count());
        $this->assertSame(0, Persistence::where('user_id', $user->id)->count());
        $this->assertSame(0, $this->ci->classMapper->staticMethod('verification', 'where', 'user_id', $user->id)->count());
    }
}
