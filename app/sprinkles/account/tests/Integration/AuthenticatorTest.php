<?php

namespace UserFrosting\Sprinkle\Account\Tests\Integration;

use UserFrosting\Sprinkle\Account\Authenticate\Authenticator;
use UserFrosting\Sprinkle\Account\Database\Models\User;
use UserFrosting\Sprinkle\Account\Tests\withTestUser;
use UserFrosting\Sprinkle\Core\Tests\TestDatabase;
use UserFrosting\Sprinkle\Core\Tests\RefreshDatabase;
use UserFrosting\Tests\TestCase;

/**
 * Integration tests for the Authenticator.
 * Integration, cause use the real $ci. We hope classmapper, session, config and
 * cache services are working properly !
 */
class AuthenticatorTest extends TestCase
{
    use TestDatabase;
    use RefreshDatabase;
    use withTestUser;

    /**
     * Setup the test database.
     */
    public function setUp()
    {
        parent::setUp();

        // Setup test database
        $this->setupTestDatabase();
        $this->refreshDatabase();
    }

    /**
     * @return Authenticator
     */
    public function testConstructor()
    {
        $authenticator = new Authenticator($this->ci->classMapper, $this->ci->session, $this->ci->config, $this->ci->cache);
        $this->assertInstanceOf(Authenticator::class, $authenticator);
        return $authenticator;
    }
}
