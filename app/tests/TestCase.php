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

/**
 * Class to handle Test
 *
 * @author Alex Weissman (https://alexanderweissman.com)
 * @see http://www.userfrosting.com/navigating/#structure
 */
class TestCase extends BaseTestCase
{
    /**
     * The Illuminate application instance.
     *
     * @var \Illuminate\Foundation\Application
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

        /*if (isset($uses[DatabaseMigrations::class])) {
            $this->runDatabaseMigrations();
        }*/

        if (isset($uses[DatabaseTransactions::class])) {
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
     * Register a callback to be run before the application is destroyed.
     *
     * @param  callable  $callback
     * @return void
     */
    protected function beforeApplicationDestroyed(callable $callback)
    {
        $this->beforeApplicationDestroyedCallbacks[] = $callback;
    }
}