<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2013-2016 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */
namespace UserFrosting\Tests\Unit;

use UserFrosting\Tests\TestCase;
//use UserFrosting\Tests\DatabaseMigrations;
use UserFrosting\Tests\DatabaseTransactions;

/**
 * FactoriesTest class.
 * Tests the factories defined in this sprinkle are working
 *
 * @extends TestCase
 */
class FactoriesTest extends TestCase
{
    use DatabaseTransactions;

    function testUserFactory()
    {
        $fm = $this->ci->factory;

        $user = $fm->create('UserFrosting\Sprinkle\Account\Model\User');
        $this->assertInstanceOf('UserFrosting\Sprinkle\Account\Model\User', $user);
    }
}