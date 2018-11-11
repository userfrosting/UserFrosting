<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */

namespace UserFrosting\Tests\Integration;

use UserFrosting\Sprinkle\Core\Util\CheckEnvironment;
use UserFrosting\Tests\TestCase;

class CheckEnvironmentTest extends TestCase
{
    /**
     * Make sure the service is successfully created
     */
    public function testServiceIsCreated()
    {
        $this->assertInstanceOf(CheckEnvironment::class, $this->ci->checkEnvironment);
    }
}
