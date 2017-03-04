<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2013-2016 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */
namespace UserFrosting\Sprinkle\Core\Util;

use UserFrosting\Cache\TaggableFileStore;
use UserFrosting\Cache\MemcachedStore;
use UserFrosting\Cache\RedisStore;

/**
 * CacheHelper utility class
 *
 * @author Alex Weissman (https://alexanderweissman.com)
 */
class CacheHelper
{

    /**
     * Return a cache instance based on the global config values
     *
     * @access public
     * @static
     * @param string $namespace
     * @param \UserFrosting\Config\Config $config
     * @param \RocketTheme\Toolbox\ResourceLocator\UniformResourceLocator $locator Locator service for stream resources.
     * @return Illuminate\\Cache\\*Store
     */
    public static function getInstance($namespace, $config, $locator)
    {
        $cacheStore = CacheHelper::getStore($config, $locator);

        $cache = $cacheStore->instance();
        return $cache->tags([$config['cache.prefix'], $namespace]);
    }

    /**
     * Flush the cache for every namespace registered
     *
     * @access public
     * @static
     * @param \UserFrosting\Config\Config $config
     * @param \RocketTheme\Toolbox\ResourceLocator\UniformResourceLocator $locator Locator service for stream resources.
     */
    public static function flushAll($config, $locator) {

        $cacheStore = CacheHelper::getStore($config, $locator);
        $cache = $cacheStore->instance();
        $cache->tags($config['cache.prefix'])->flush();
    }

    /**
     * Return a cache stores from the config values
     *
     * @access protected
     * @static
     * @param \UserFrosting\Config\Config $config
     * @param \RocketTheme\Toolbox\ResourceLocator\UniformResourceLocator $locator Locator service for stream resources.
     */
    protected static function getStore($config, $locator)
    {
        if ($config['cache.store'] == 'file')
        {
            $path = $locator->findResource('cache://', true, true);
            return new TaggableFileStore($path);
        }
        else if ($config['cache.store'] == 'memcached')
        {
            return new MemcachedStore($config['cache.memcached']);
        }
        else if ($config['cache.store'] == 'redis')
        {
            return new RedisStore($config['cache.redis']);
        }
        else
        {
            throw new \Exception("Bad cache store type '{$config['cache.store']}' specified in configuration file.");
        }
    }
}
