<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */
namespace UserFrosting\System\Bakery;

use Illuminate\Support\Str;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use UserFrosting\System\UserFrosting;
use UserFrosting\UniformResourceLocator\Resource;
use UserFrosting\UniformResourceLocator\ResourceLocator;

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
     * @var string $scheme The resource locator scheme
     */
    protected $scheme = 'bakery://';

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
        $uf = new UserFrosting(true);

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
        /**
        * @var ResourceLocator $locator
        */
        $locator = $this->ci->locator;

        // Get Bakery command resources
        $commandResources = $locator->listResources($this->scheme);

        // Add commands to the App
        foreach ($commandResources as $commandResource) {

            // Translate the resource to a class
            $command = $this->getResourceClass($commandResource);

            // Get command instance
            $instance = new $command();

            // Class must be an instance of symfony command
            if (!$instance instanceof Command) {
                throw new \Exception("Bakery command class must be an instance of `" . Command::class . "`");
            }

            // Add command to the Console app
            $instance->setContainer($this->ci);
            $this->app->add($instance);
        };
    }

    /**
     * Transform a Bakery Command Resource into a classpath
     *
     * @param  Resource $file The command resource
     * @return string The command class path
     */
    protected function getResourceClass(Resource $file)
    {
        // Process sprinkle and system commands
        if (!is_null($location = $file->getLocation())) {

            // Format the sprinkle name for the namespace
            $sprinkleName = $file->getLocation()->getName();
            $sprinkleName = Str::studly($sprinkleName);

            // Getting the classpath
            $basePath = str_replace($file->getBasename(), '', $file->getBasePath());
            $className = str_replace('/', '\\', $basePath) . $file->getFilename();
            $classPath = "\\UserFrosting\\Sprinkle\\$sprinkleName\\Bakery\\$className";

        } else {
            // Getting the classpath
            $basePath = str_replace($file->getBasename(), '', $file->getBasePath());
            $className = str_replace('/', '\\', $basePath) . $file->getFilename();
            $classPath = "\\UserFrosting\\System\\Bakery\\Command\\$className";
        }

        // Make sure class exist
        if (!class_exists($classPath)) {
            throw new \Exception("Bakery command found in `{$file->getAbsolutePath()}`, but class `$classPath` doesn't exist. Make sure the class has the correct namespace.");
        }

        return $classPath;
    }

    /**
     * Write the base `sprinkles.json` file if none exist.
     *
     * @return string The sprinkle model file
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
