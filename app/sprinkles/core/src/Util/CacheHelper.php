<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2013-2016 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */
namespace UserFrosting\Sprinkle\Core\Util;

use UserFrosting\Cache\FileStore;
use UserFrosting\Cache\MemcachedStore;
use UserFrosting\Cache\RedisStore;

/**
 * CacheHelper utility class
 *
 * @author Alex Weissman (https://alexanderweissman.com)
 */
class CacheHelper
{

    public static function getInstance($namespace, $config, $locator)
    {
        // Set namespace.
        $namespace = $config['cache.prefix'] . $namespace;

        if ($config['cache.store'] == 'file') {
            $path = $locator->findResource('cache://', true, true);
            $cacheStore = new FileStore($namespace, $path);
        } else if ($config['cache.store'] == 'memcached') {
            $cacheStore = new MemcachedStore($namespace, $config['cache.memcached']);
        } else if ($config['cache.store'] == 'redis') {
            $cacheStore = new RedisStore($namespace, $config['cache.redis']);
        } else {
            throw new \Exception("Bad cache store type '{$config['cache.store']}' specified in configuration file.");
        }

        return $cacheStore->instance();
    }
}
