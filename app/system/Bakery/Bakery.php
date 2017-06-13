<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2013-2016 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */
namespace UserFrosting\System\Bakery;

use Symfony\Component\Console\Application;
use UserFrosting\System\UserFrosting;

/**
 * Base class for UserFrosting Bakery CLI tools.
 *
 * @author Alex Weissman (https://alexanderweissman.com)
 */
class Bakery
{
    /**
     * @var $app Symfony\Component\Console\Application
     */
    protected $app;

    /**
     * @var ContainerInterface The global container object, which holds all your services.
     */
    protected $ci;

    /**
     * Constructor
     */
    public function __construct()
    {
        // Create Symfony Console App
        $this->app = new Application("UserFrosting Bakery", \UserFrosting\VERSION);

        // Setup the sprinkles
        $uf = new UserFrosting();

        // Set argument as false, we are using the CLI
        $uf->setupSprinkles(false);

        // Get the container
        $this->ci = $uf->getContainer();

        // Add each commands to the Console App
        foreach ($this->getCommands() as $command) {
            $instance = new $command();
            $instance->setContainer($this->ci);
            $this->app->add($instance);
        }
    }

    /**
     * Run the Symfony Console App
     */
    public function run()
    {
        $this->app->run();
    }

    /**
     * Return the list of available commands.
     */
    protected function getCommands()
    {
        return [
            'UserFrosting\System\Bakery\Command\Debug',
            'UserFrosting\System\Bakery\Command\Assets',
            'UserFrosting\System\Bakery\Command\Bake',
            'UserFrosting\System\Bakery\Command\Setup',
            'UserFrosting\System\Bakery\Command\Test',
            'UserFrosting\System\Bakery\Command\Migration',
            'UserFrosting\System\Bakery\Command\MigrationRollback',
            'UserFrosting\System\Bakery\Command\MigrationReset',
            'UserFrosting\System\Bakery\Command\MigrationRefresh',
            'UserFrosting\System\Bakery\Command\ClearCache'
        ];
    }
}