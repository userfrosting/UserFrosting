<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Filesystem;

use League\Flysystem\FilesystemInterface;
use Illuminate\Filesystem\FilesystemManager as LaravelFilesystemManager;
use UserFrosting\Support\Repository\Repository as ConfigRepository;

/**
 * Filesystem disk manager service
 *
 * @author Louis Charette
 */
class FilesystemManager extends LaravelFilesystemManager
{
    /**
     * The config service.
     *
     * @var \UserFrosting\Support\Repository\Repository
     */
    protected $config;

    /**
     * Create a new filesystem manager instance.
     *
     * @param \UserFrosting\Support\Repository\Repository $config
     */
    public function __construct(ConfigRepository $config)
    {
        $this->config = $config;
    }

    /**
     * Call a custom driver creator.
     *
     * @param  array                                       $config
     * @return \Illuminate\Contracts\Filesystem\Filesystem
     */
    protected function callCustomCreator(array $config)
    {
        $driver = $this->customCreators[$config['driver']]($this->config, $config);

        if ($driver instanceof FilesystemInterface) {
            return $this->adapt($driver);
        }

        return $driver;
    }

    /**
     * Create a cache store instance.
     *
     * @param  mixed                                   $config
     * @throws \InvalidArgumentException
     * @return \League\Flysystem\Cached\CacheInterface
     */
    // N.B.: Introduced after laravel 5.4
    /*protected function createCacheStore($config)
    {
        if ($config === true) {
            return new MemoryStore;
        }

        return new Cache(
            $this->cache->store($config['store']),
            $config['prefix'] ?? 'flysystem',
            $config['expire'] ?? null
        );
    }*/

    /**
     * Get the filesystem connection configuration.
     *
     * @param  string $name
     * @return array
     */
    protected function getConfig($name)
    {
        return $this->config["filesystems.disks.{$name}"];
    }

    /**
     * Get the default driver name.
     *
     * @return string
     */
    public function getDefaultDriver()
    {
        return $this->config['filesystems.default'];
    }

    /**
     * Get the default cloud driver name.
     *
     * @return string
     */
    public function getDefaultCloudDriver()
    {
        return $this->config['filesystems.cloud'];
    }
}
