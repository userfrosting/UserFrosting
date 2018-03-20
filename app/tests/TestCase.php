<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */
namespace UserFrosting\Tests;

use Slim\App;
use PHPUnit\Framework\TestCase as BaseTestCase;
use UserFrosting\System\UserFrosting;
use UserFrosting\Sprinkle\Core\Facades\Debug;
use UserFrosting\Tests\DatabaseTransactions;

/**
 * Class to handle Test
 *
 * @author Louis Charette
 */
class TestCase extends BaseTestCase
{
    /**
     * The Slim application instance.
     *
     * @var \Slim\App
     */
    protected $app;

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
     *
     * @return void
     */
    protected function setUp()
    {
        if (!$this->app) {
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
     * @return void
     */
    protected function setUpTraits()
    {
        $uses = array_flip(class_uses_recursive(static::class));

        if (isset($uses[DatabaseTransactions::class])) {
            Debug::debug("`UserFrosting\Tests\DatabaseTransactions` has been deprecated and will be removed in future versions.  Please use `UserFrosting\Sprinkle\Core\Tests\DatabaseTransactions` class instead.");
            $this->beginDatabaseTransaction();
        }
    }

    /**
     * Refresh the application instance.
     *
     * @return void
     */
    protected function refreshApplication()
    {
        // Force setting UF_MODE.  This is needed at the moment because Bakery
        // uses passthru to invoke PHPUnit.  This means that the value of UF_MODE
        // has already been set when Bakery was set up, and PHPUnit < 6.3 has
        // no way to override environment vars that have already been set.
        putenv('UF_MODE=testing');

        // Setup the sprinkles
        $uf = new UserFrosting();

        // Set argument as false, we are using the CLI
        $uf->setupSprinkles(false);

        // Get the container
        $this->ci = $uf->getContainer();

        // Next, we'll instantiate the application.  Note that the application is required for the SprinkleManager to set up routes.
        $this->app = new App($this->ci);
    }

    /**
     * Clean up the testing environment before the next test.
     *
     * @return void
     */
    protected function tearDown()
    {
        if ($this->app) {
            foreach ($this->beforeApplicationDestroyedCallbacks as $callback) {
                call_user_func($callback);
            }

            $this->app = null;
            $this->ci = null;
        }

        $this->setUpHasRun = false;

        $this->afterApplicationCreatedCallbacks = [];
        $this->beforeApplicationDestroyedCallbacks = [];
    }

    /**
     * Register a callback to be run after the application is created.
     *
     * @param  callable  $callback
     * @return void
     */
    public function afterApplicationCreated(callable $callback)
    {
        $this->afterApplicationCreatedCallbacks[] = $callback;

        if ($this->setUpHasRun) {
            call_user_func($callback);
        }
    }

    /**
     *    Asserts that collections are equivalent.
     *
     *    @param  array $expected
     *    @param  array $actual
     *    @param  string $key [description]
     *    @param  string $message [description]
     *    @throws \PHPUnit_Framework_AssertionFailedError
     *    @return void
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
     * Register a callback to be run before the application is destroyed.
     *
     * @param  callable  $callback
     * @return void
     */
    protected function beforeApplicationDestroyed(callable $callback)
    {
        $this->beforeApplicationDestroyedCallbacks[] = $callback;
    }

    /**
     * Helpers
     */

    /**
     *    Cast an item to an array if it has a toArray() method.
     *
     *    @param  object $item
     *    @return mixed
     */
    protected static function castToComparable($item)
    {
        return (is_object($item) && method_exists($item, 'toArray')) ? $item->toArray() : $item;
    }

    /**
     *    Remove all relations on a collection of models.
     *
     *    @param  array $models
     *    @return void
     */
    protected static function ignoreRelations($models)
    {
        foreach ($models as $model) {
            $model->setRelations([]);
        }
    }

    /**
     *    cloneObjectArray
     *
     *    @param  array $original
     *    @return array
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
