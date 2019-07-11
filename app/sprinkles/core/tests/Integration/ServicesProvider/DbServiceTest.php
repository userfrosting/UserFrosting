<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Tests\Integration\ServicesProvider;

use Illuminate\Database\Capsule\Manager;
use UserFrosting\Tests\TestCase;

/**
 * Integration tests for `debugLogger` service.
 * Check to see if service returns what it's supposed to return
 */
class DbServiceTest extends TestCase
{
    public function testService()
    {
        $this->assertInstanceOf(Manager::class, $this->ci->db);
    }

    public function testServiceWithDebug()
    {
        $this->ci->config['debug.queries'] = true;
        $this->assertInstanceOf(Manager::class, $this->ci->db);
    }
}
