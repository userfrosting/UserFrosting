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
     *    @var $app Symfony\Component\Console\Application
     */
    protected $app;

    /**
     *    @var \Interop\Container\ContainerInterface The global container object, which holds all your services.
     */
    protected $ci;

    /**
     *    Constructor
     */
    public function __construct()
    {
        // Check for Sprinkles schema file
        $sprinklesFile = @file_get_contents(\UserFrosting\SPRINKLES_SCHEMA_FILE);
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
     *    Run the Symfony Console App
     *
     *    @return void
     */
    public function run()
    {
        $this->app->run();
    }

    /**
     *    Return the list of available commands for a specific sprinkle
     *
     *    @return void
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
     *    Return the list of available commands for a specific sprinkle
     *    Sprinkles commands should be located in `src/Bakery/`
     *
     *    @param  string $sprinkle The sprinkle name
     *    @return \Illuminate\Support\Collection A collection of commands
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
     *    Return the list of available commands in system/Bakery/Command/
     *
     *    @return \Illuminate\Support\Collection A collection of commands
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
     *    Returns the path of the Bakery commands directory.
     *
     *    @param  string $sprinkleName The sprinkle name
     *    @return string The sprinkle bakery command directory path
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
     *    Write the base `sprinkles.json` file if none exist.
     *
     *    @return string The sprinkle model file
     */
    protected function setupBaseSprinkleList()
    {
        $model = \UserFrosting\APP_DIR . '/sprinkles.example.json';
        $destination = \UserFrosting\SPRINKLES_SCHEMA_FILE;
        $sprinklesModelFile = @file_get_contents($model);
        if ($sprinklesModelFile === false) {
            $this->io->error("File `$sprinklesModelFile` not found. Please create '$destination' manually and try again.");
            exit(1);
        }

        file_put_contents($destination, $sprinklesModelFile);

        return $sprinklesModelFile;
    }
}
