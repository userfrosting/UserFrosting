<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Account\Tests\Integration;

use UserFrosting\Sprinkle\Account\Database\Models\User;
use UserFrosting\Sprinkle\Account\Database\Models\Group;
use UserFrosting\Sprinkle\Account\Tests\withTestUser;
use UserFrosting\Sprinkle\Core\Tests\TestDatabase;
use UserFrosting\Sprinkle\Core\Tests\RefreshDatabase;
use UserFrosting\Tests\TestCase;

/**
 * Test for bug with `withTrashed` in `findUnique` not available when `SoftDeletes` trait is not included in a model.
 * @see https://chat.userfrosting.com/channel/support?msg=aAYvdwczSvBMzriJ6
 */
class FindUniqueTest extends TestCase
{
    use TestDatabase;
    use RefreshDatabase;
    use withTestUser;

    /**
     * Setup the database schema.
     */
    public function setUp(): void
    {
        parent::setUp();

        // Setup test database
        $this->setupTestDatabase();
        $this->refreshDatabase();
    }

    /**
     * User Model does have the soft Delete
     */
    public function testUserFindUnique(): void
    {
        $user = $this->createTestUser();
        $resultA = User::findUnique($user->user_name, 'user_name', true);
        $resultB = $this->ci->classMapper->staticMethod('user', 'findUnique', $user->user_name, 'user_name');
        $this->assertEquals($resultA, $resultB);
    }

    /**
     * Group model doesn't have the soft delete
     */
    public function testGroupFindUnique(): void
    {
        $group = $this->ci->factory->create(Group::class);
        $resultA = Group::findUnique($group->name, 'name', true);
        $resultB = $this->ci->classMapper->staticMethod('group', 'findUnique', $group->name, 'name');
        $this->assertEquals($resultA, $resultB);
    }
}
