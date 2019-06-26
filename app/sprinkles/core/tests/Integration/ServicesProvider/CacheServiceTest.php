<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Tests\Integration\ServicesProvider;

use Illuminate\Cache\Repository as Cache;
use UserFrosting\Tests\TestCase;

/**
 * Integration tests for `cache` service.
 * Check to see if service returns what it's supposed to return
 */
class CacheServiceTest extends TestCase
{
    public function testCacheConfig()
    {
        $this->ci->config['cache.driver'] = 'file';
        $this->assertInstanceOf(Cache::class, $this->ci->cache);
    }

    /**
     * @requires extension Memcached
     */
    public function testMemcachedConfig()
    {
        $this->ci->config['cache.driver'] = 'memcached';
        $this->assertInstanceOf(Cache::class, $this->ci->cache);
    }

    /**
     * @requires extension redis
     */
    public function testRedisConfig()
    {
        $this->ci->config['cache.driver'] = 'redis';
        $this->assertInstanceOf(Cache::class, $this->ci->cache);
    }

    public function testBadConfig()
    {
        $this->ci->config['cache.driver'] = 'foo';
        $this->expectException(\Exception::class);
        $cache = $this->ci->cache;
    }
}
