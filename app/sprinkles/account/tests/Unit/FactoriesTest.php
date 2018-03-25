<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */
namespace UserFrosting\Sprinkle\Account\Tests\Unit;

use UserFrosting\Tests\TestCase;
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
     * Setup TestDatabase
     */
    public function setUp()
    {
        // Boot parent TestCase, which will set up the database and connections for us.
        parent::setUp();

        // Setup test database
        $this->setupTestDatabase();
        $this->refreshDatabase();
    }

    /**
     *    Test the user factory
     *    @return void 
     */
    function testUserFactory()
    {
        $fm = $this->ci->factory;

        $user = $fm->create('UserFrosting\Sprinkle\Account\Database\Models\User');
        $this->assertInstanceOf('UserFrosting\Sprinkle\Account\Database\Models\User', $user);
    }
}
