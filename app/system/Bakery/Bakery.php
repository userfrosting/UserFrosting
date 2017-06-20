<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */
namespace UserFrosting\System\Bakery;

use Symfony\Component\Console\Application;
use UserFrosting\System\UserFrosting;
use Illuminate\Support\Str;

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
        // Check for `sprinkles.json`
        $path = \UserFrosting\APP_DIR . '/sprinkles.json';
        $sprinklesFile = @file_get_contents($path);
        if ($sprinklesFile === false) {
            $sprinklesFile = $this->setupBaseSprinkleList();
        }

        // Create Symfony Console App
        $this->app = new Application("UserFrosting Bakery", \UserFrosting\VERSION);

        // Setup the sprinkles
        $uf = new UserFrosting();

        // Set argument as false, we are using the CLI
        $uf->setupSprinkles(false);

        // Get the container
        $this->ci = $uf->getContainer();

        // Add each commands to the Console App
        $this->loadCommands();
    }

    /**
     * Run the Symfony Console App
     */
    public function run()
    {
        $this->app->run();
    }

    /**
     * Return the list of available commands for a specific sprinkle
     */
    protected function loadCommands()
    {
        // Get base Bakery command
        $commands = $this->getBakeryCommands();

        // Get the sprinkles commands
        $sprinkles = $this->ci->sprinkleManager->getSprinkleNames();
        foreach ($sprinkles as $sprinkle) {
            $commands = $commands->merge($this->getSprinkleCommands($sprinkle));
        }

        // Add commands to the App
        $commands->each(function($command) {
            $instance = new $command();
            $instance->setContainer($this->ci);
            $this->app->add($instance);
        });
    }

    /**
     * Return the list of available commands for a specific sprinkle
     * Sprinkles commands should be located in `src/Bakery/`
     */
    protected function getSprinkleCommands($sprinkle)
    {
        // Find all the migration files
        $path = $this->commandDirectoryPath($sprinkle);
        $files = glob($path . "*.php");
        $commands = collect($files);

        // Transform the path into a class names
        $commands->transform(function ($file) use ($sprinkle, $path) {
            $className = basename($file, '.php');
            $sprinkleName = Str::studly($sprinkle);
            $className = "\\UserFrosting\\Sprinkle\\".$sprinkleName."\\Bakery\\".$className;
            return $className;
        });

        return $commands;
    }

    /**
     * Return the list of available commands in system/Bakery/Command/
     */
    protected function getBakeryCommands()
    {
        // Find all the migration files
        $files = glob(\UserFrosting\APP_DIR . "/system/Bakery/Command/" . "*.php");
        $commands = collect($files);

        // Transform the path into a class names
        $commands->transform(function ($file) {
            $className = basename($file, '.php');
            $className = "\\UserFrosting\\System\\Bakery\\Command\\".$className;
            return $className;
        });

        return $commands;
    }

    /**
     * Returns the path of the Migration directory.
     *
     * @access protected
     * @param mixed $sprinkleName
     * @return void
     */
    protected function commandDirectoryPath($sprinkleName)
    {
        return \UserFrosting\SPRINKLES_DIR .
               \UserFrosting\DS .
               $sprinkleName .
               \UserFrosting\DS .
               \UserFrosting\SRC_DIR_NAME .
               "/Bakery/";
    }

    /**
     * Write the base `sprinkles.json` file if none exist.
     *
     * @access protected
     * @return void
     */
    protected function setupBaseSprinkleList()
    {
        $model = \UserFrosting\APP_DIR . '/sprinkles.example.json';
        $destination = \UserFrosting\APP_DIR . '/sprinkles.json';
        $sprinklesModelFile = @file_get_contents($model);
        if ($sprinklesModelFile === false) {
            $this->io->error("File `$sprinklesModelFile` not found. Please create '$destination' manually and try again.");
            exit(1);
        }

        file_put_contents($destination, $sprinklesModelFile);

        return $sprinklesModelFile;
    }
}