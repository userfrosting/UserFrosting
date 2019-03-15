<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Tests\Integration;

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
