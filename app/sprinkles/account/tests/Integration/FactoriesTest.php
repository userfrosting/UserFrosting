<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Account\Tests\Integration;

use UserFrosting\Tests\TestCase;
use UserFrosting\Sprinkle\Account\Database\Models\Interfaces\UserInterface;
use UserFrosting\Sprinkle\Account\Database\Models\User;
use UserFrosting\Sprinkle\Core\Tests\TestDatabase;
use UserFrosting\Sprinkle\Core\Tests\RefreshDatabase;

/**
 * FactoriesTest class.
 * Tests the factories defined in this sprinkle are working
 */
class FactoriesTest extends TestCase
{
    use TestDatabase;
    use RefreshDatabase;

    /**
     * Test the user factory
     */
    public function testUserFactory()
    {
        // Setup test database
        $this->setupTestDatabase();
        $this->refreshDatabase();

        $fm = $this->ci->factory;

        $user = $fm->create(User::class);
        $this->assertInstanceOf(UserInterface::class, $user);
    }
}
