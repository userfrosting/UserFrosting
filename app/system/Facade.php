<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\System;

use Mockery;
use Mockery\MockInterface;
use Interop\Container\ContainerInterface;
use RuntimeException;

/**
 * Implements base functionality for static "facade" classes.
 *
 * Adapted from the Laravel Facade class: https://github.com/laravel/framework/blob/5.3/src/Illuminate/Support/Facades/Facade.php
 *
 * @author Alex Weissman (https://alexanderweissman.com)
 * @see https://laravel.com/docs/5.2/facades
 * @license https://github.com/laravel/framework/blob/5.3/LICENSE.md (MIT License)
 */
abstract class Facade
{
    /**
     * The Pimple container being facaded.
     *
     * @var ContainerInterface
     */
    protected static $container;

    /**
     * The resolved object instances.
     *
     * @var array
     */
    protected static $resolvedInstance;

    /**
     * Hotswap the underlying service instance behind the facade.
     *
     * @param mixed $instance
     */
    public static function swap($instance)
    {
        $name = static::getFacadeAccessor();

        static::$resolvedInstance[$name] = $instance;

        // Replace service in container
        unset(static::$container->$name);
        static::$container->$name = $instance;
    }

    /**
     * Initiate a mock expectation on the facade.
     *
     * @return \Mockery\Expectation
     */
    public static function shouldReceive()
    {
        $name = static::getFacadeAccessor();

        if (static::isMock()) {
            $mock = static::$resolvedInstance[$name];
        } else {
            $mock = static::createFreshMockInstance($name);
        }

        return call_user_func_array([$mock, 'shouldReceive'], func_get_args());
    }

    /**
     * Create a fresh mock instance for the given class.
     *
     * @param  string               $name
     * @return \Mockery\Expectation
     */
    protected static function createFreshMockInstance($name)
    {
        static::$resolvedInstance[$name] = $mock = static::createMockByName($name);

        $mock->shouldAllowMockingProtectedMethods();

        if (isset(static::$container)) {
            static::$container->$name = $mock;
        }

        return $mock;
    }

    /**
     * Create a fresh mock instance for the given class.
     *
     * @param  string               $name
     * @return \Mockery\Expectation
     */
    protected static function createMockByName($name)
    {
        $class = static::getMockableClass($name);

        return $class ? Mockery::mock($class) : Mockery::mock();
    }

    /**
     * Determines whether a mock is set as the instance of the facade.
     *
     * @return bool
     */
    protected static function isMock()
    {
        $name = static::getFacadeAccessor();

        return isset(static::$resolvedInstance[$name]) && static::$resolvedInstance[$name] instanceof MockInterface;
    }

    /**
     * Get the mockable class for the bound instance.
     *
     * @return string|null
     */
    protected static function getMockableClass()
    {
        if ($root = static::getFacadeRoot()) {
            return get_class($root);
        }
    }

    /**
     * Get the root object behind the facade.
     *
     * @return mixed
     */
    public static function getFacadeRoot()
    {
        return static::resolveFacadeInstance(static::getFacadeAccessor());
    }

    /**
     * Get the registered name of the component.
     *
     * @throws \RuntimeException
     */
    protected static function getFacadeAccessor()
    {
        throw new RuntimeException('Facade does not implement getFacadeAccessor method.');
    }

    /**
     * Resolve the facade root instance from the container.
     *
     * @param  string|object $name
     * @return mixed
     */
    protected static function resolveFacadeInstance($name)
    {
        if (is_object($name)) {
            return $name;
        }

        if (isset(static::$resolvedInstance[$name])) {
            return static::$resolvedInstance[$name];
        }

        return static::$resolvedInstance[$name] = static::$container->$name;
    }

    /**
     * Clear a resolved facade instance.
     *
     * @param string $name
     */
    public static function clearResolvedInstance($name)
    {
        unset(static::$resolvedInstance[$name]);
    }

    /**
     * Clear all of the resolved instances.
     */
    public static function clearResolvedInstances()
    {
        static::$resolvedInstance = [];
    }

    /**
     * Get the container instance behind the facade.
     *
     * @return ContainerInterface
     */
    public static function getFacadeContainer()
    {
        return static::$container;
    }

    /**
     * Set the container instance.
     *
     * @param ContainerInterface $container
     */
    public static function setFacadeContainer(ContainerInterface $container)
    {
        static::$container = $container;
    }

    /**
     * Handle dynamic, static calls to the object.
     *
     * @param  string            $method
     * @param  array             $args
     * @throws \RuntimeException
     * @return mixed
     */
    public static function __callStatic($method, $args)
    {
        $instance = static::getFacadeRoot();

        if (!$instance) {
            throw new RuntimeException('A facade root has not been set.');
        }

        switch (count($args)) {
            case 0:
                return $instance->$method();
            case 1:
                return $instance->$method($args[0]);
            case 2:
                return $instance->$method($args[0], $args[1]);
            case 3:
                return $instance->$method($args[0], $args[1], $args[2]);
            case 4:
                return $instance->$method($args[0], $args[1], $args[2], $args[3]);
            default:
                return call_user_func_array([$instance, $method], $args);
        }
    }
}
