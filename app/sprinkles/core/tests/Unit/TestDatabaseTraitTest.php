<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Tests\Unit;

use UserFrosting\Tests\TestCase;
use UserFrosting\Sprinkle\Core\Tests\TestDatabase;

class TestDatabaseTraitTest extends TestCase
{
    use TestDatabase;

    /**
     * Setup TestDatabase
     */
    public function setUp()
    {
        // Boot parent TestCase, which will set up the database and connections for us.
        parent::setUp();

        // Setup test database
        $this->setupTestDatabase();
    }

    /**
     *    Test the TestDatabase traits works
     */
    public function testTrait()
    {
        // Use the testing db for this test
        $connection = $this->ci->db->getConnection();
        $this->assertEquals($this->ci->config['testing.dbConnection'], $connection->getName());
    }
}
