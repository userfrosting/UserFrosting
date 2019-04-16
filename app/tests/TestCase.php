<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Tests;

use PHPUnit\Framework\TestCase as BaseTestCase;
use UserFrosting\System\UserFrosting;
use UserFrosting\Sprinkle\Core\Facades\Debug;

/**
 * Class to handle Test
 *
 * @author Louis Charette
 */
class TestCase extends BaseTestCase
{
    /**
     * The global container object, which holds all your services.
     *
     * @var \Interop\Container\ContainerInterface
     */
    protected $ci;

    /**
     * The callbacks that should be run after the application is created.
     *
     * @var array
     */
    protected $afterApplicationCreatedCallbacks = [];

    /**
     * The callbacks that should be run before the application is destroyed.
     *
     * @var array
     */
    protected $beforeApplicationDestroyedCallbacks = [];

    /**
     * Indicates if we have made it through the base setUp function.
     *
     * @var bool
     */
    protected $setUpHasRun = false;

    /**
     * Setup the test environment.
     */
    protected function setUp()
    {
        if (!$this->ci) {
            $this->refreshApplication();
        }

        $this->setUpTraits();

        foreach ($this->afterApplicationCreatedCallbacks as $callback) {
            call_user_func($callback);
        }

        $this->setUpHasRun = true;
    }

    /**
     * Boot the testing helper traits.
     *
     * @deprecated
     */
    protected function setUpTraits()
    {
        $uses = array_flip(class_uses_recursive(static::class));

        if (isset($uses[DatabaseTransactions::class])) {
            Debug::warning("`UserFrosting\Tests\DatabaseTransactions` has been deprecated and will be removed in future versions.  Please use `UserFrosting\Sprinkle\Core\Tests\DatabaseTransactions` class instead.");
            $this->beginDatabaseTransaction();
        }
    }

    /**
     * Refresh the application instance.
     */
    protected function refreshApplication()
    {
        // Force setting UF_MODE.  This is needed at the moment because Bakery
        // uses passthru to invoke PHPUnit.  This means that the value of UF_MODE
        // has already been set when Bakery was set up, and PHPUnit < 6.3 has
        // no way to override environment vars that have already been set.
        putenv('UF_MODE=testing');

        // Setup the base UF app
        $uf = new UserFrosting(true);

        // Get the container
        $this->ci = $uf->getContainer();
    }

    /**
     * Clean up the testing environment before the next test.
     */
    protected function tearDown()
    {
        if ($this->ci) {
            foreach ($this->beforeApplicationDestroyedCallbacks as $callback) {
                call_user_func($callback);
            }

            // Force destroy test sessions 
            $this->ci->session->destroy();

            $this->ci = null;
        }

        $this->setUpHasRun = false;

        $this->afterApplicationCreatedCallbacks = [];
        $this->beforeApplicationDestroyedCallbacks = [];
    }

    /**
     * Register a callback to be run after the application is created.
     *
     * @param callable $callback
     */
    public function afterApplicationCreated(callable $callback)
    {
        $this->afterApplicationCreatedCallbacks[] = $callback;

        if ($this->setUpHasRun) {
            call_user_func($callback);
        }
    }

    /**
     * Asserts that collections are equivalent.
     *
     * @param  array                                   $expected
     * @param  array                                   $actual
     * @param  string                                  $key      [description]
     * @param  string                                  $message  [description]
     * @throws \PHPUnit_Framework_AssertionFailedError
     */
    public static function assertCollectionsSame($expected, $actual, $key = 'id', $message = '')
    {
        // Check that they have the same number of items
        static::assertEquals(count($expected), count($actual));

        // Sort by primary key
        $expected = collect($expected)->sortBy($key);
        $actual = collect($actual)->sortBy($key);

        // Check that the keys match
        $expectedKeys = $expected->keys()->all();
        $actualKeys = $actual->keys()->all();
        static::assertEquals(sort($expectedKeys), sort($actualKeys));

        // Check that the array representations of each collection item match
        $expected = $expected->values();
        $actual = $actual->values();
        for ($i = 0; $i < count($expected); $i++) {
            static::assertEquals(
                static::castToComparable($expected[$i]),
                static::castToComparable($actual[$i])
            );
        }
    }

    /**
     * Call protected/private method of a class.
     *
     * @param  object &$object    Instantiated object that we will run method on.
     * @param  string $methodName Method name to call
     * @param  array  $parameters Array of parameters to pass into method.
     * @return mixed  Method return.
     */
    public function invokeMethod(&$object, $methodName, array $parameters = [])
    {
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }

    /**
     * Register a callback to be run before the application is destroyed.
     *
     * @param callable $callback
     */
    protected function beforeApplicationDestroyed(callable $callback)
    {
        $this->beforeApplicationDestroyedCallbacks[] = $callback;
    }

    /**
     * Helpers
     */

    /**
     * Cast an item to an array if it has a toArray() method.
     *
     * @param  object $item
     * @return mixed
     */
    protected static function castToComparable($item)
    {
        return (is_object($item) && method_exists($item, 'toArray')) ? $item->toArray() : $item;
    }

    /**
     * Remove all relations on a collection of models.
     *
     * @param array $models
     */
    protected static function ignoreRelations($models)
    {
        foreach ($models as $model) {
            $model->setRelations([]);
        }
    }

    /**
     * cloneObjectArray
     *
     * @param  array $original
     * @return array
     */
    protected function cloneObjectArray($original)
    {
        $cloned = [];

        foreach ($original as $k => $v) {
            $cloned[$k] = clone $v;
        }

        return $cloned;
    }
}
