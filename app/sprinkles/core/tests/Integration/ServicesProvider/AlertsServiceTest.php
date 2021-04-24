<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Tests\Integration\ServicesProvider;

use UserFrosting\Sprinkle\Core\Alert\CacheAlertStream;
use UserFrosting\Sprinkle\Core\Alert\SessionAlertStream;
use UserFrosting\Tests\TestCase;

/**
 * Integration tests for `alerts` service.
 * Check to see if service returns what it's supposed to return
 */
class AlertsServiceTest extends TestCase
{
    public function testCacheConfig()
    {
        $this->ci->config['alert.storage'] = 'cache';
        $this->assertInstanceOf(CacheAlertStream::class, $this->ci->alerts);
    }

    public function testSessionConfig()
    {
        $this->ci->config['alert.storage'] = 'session';
        $this->assertInstanceOf(SessionAlertStream::class, $this->ci->alerts);
    }

    public function testBadConfig()
    {
        $this->ci->config['alert.storage'] = 'foo';
        $this->expectException(\Exception::class);
        $alerts = $this->ci->alerts;
    }
}
