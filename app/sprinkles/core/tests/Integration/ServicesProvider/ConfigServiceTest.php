<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Tests\Integration\ServicesProvider;

use UserFrosting\Support\Repository\Repository as Config;
use UserFrosting\Tests\TestCase;

/**
 * Integration tests for `config` service.
 * Check to see if service returns what it's supposed to return
 */
class ConfigServiceTest extends TestCase
{
    public function testService()
    {
        $this->assertInstanceOf(Config::class, $this->ci->config);
    }
}
