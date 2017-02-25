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

    /**
     * Register the namespace to the registery.
     * The namespace registery is used to store the list of all cache namespace
     * to enable to flush the cache of every namespace at once.
     *
     * @access public
     * @static
     * @param string $namespace
     * @param \UserFrosting\Config\Config $config
     * @param \RocketTheme\Toolbox\ResourceLocator\UniformResourceLocator $locator Locator service for stream resources.
     */
    public static function register($namespace, $config, $locator)
    {
        //Don't need to add the global namespace to the namespace repository
        if ($namespace == $config['cache.global_namespace'])
        {
            return;
        }

        // The the list of current namespace in the repo
        $globalCache = static::getInstance($config['cache.global_namespace'], $config, $locator);
        $list = static::getNamepaceList($config, $locator, $globalCache);

        // Of the namespace is not in the list, add it and save the list to cache
        if (!in_array($namespace, $list)) {
            $list[] = $namespace;
            $globalCache->forever($config['cache.namespace_repository'], $list);
        }
    }

    /**
     * Return the list of currently registered namespaces
     *
     * @access public
     * @static
     * @param \UserFrosting\Config\Config $config
     * @param \RocketTheme\Toolbox\ResourceLocator\UniformResourceLocator $locator Locator service for stream resources.
     * @param Illuminate\\Cache\\*Store $instance (default: null)
     */
    public static function getNamepaceList($config, $locator, $instance = null) {

        // Get the cache instance if not provided
        if ($instance == null)
        {
            $instance = static::getInstance($config['cache.global_namespace'], $config, $locator);
        }

        // Returned the cached list, of create an empty one if none is currently cached
        return $instance->get($config['cache.namespace_repository'], function () {
            return [$config['cache.global_namespace']];
        });
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
        $namespaces = static::getNamepaceList($config, $locator);
        foreach ($namespaces as $namespace) {
            $cache = static::getInstance($namespace, $config, $locator);
            $cache->flush();
        }
    }
}
