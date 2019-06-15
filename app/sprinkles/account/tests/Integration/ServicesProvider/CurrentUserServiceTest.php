<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Account\Tests\Integration\ServicesProvider;

use UserFrosting\Sprinkle\Account\Database\Models\Interfaces\UserInterface;
use UserFrosting\Tests\TestCase;
use UserFrosting\Sprinkle\Core\Tests\TestDatabase;
use UserFrosting\Sprinkle\Core\Tests\RefreshDatabase;
use UserFrosting\Sprinkle\Account\Tests\withTestUser;

/**
 * Integration tests for `currentUser` service.
 * Check to see if service returns what it's supposed to return
 */
class CurrentUserServiceTest extends TestCase
{
    use TestDatabase;
    use RefreshDatabase;
    use withTestUser;

    public function testServiceWithNoUser()
    {
        $this->assertNull($this->ci->currentUser);
    }

    public function testService()
    {
        $this->setupTestDatabase();
        $this->refreshDatabase();

        $testUser = $this->createTestUser(false, true);

        $this->assertInstanceOf(UserInterface::class, $this->ci->currentUser);
    }
}
