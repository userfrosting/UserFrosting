<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\System\Bakery;

use Illuminate\Support\Str;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\ConsoleOutput;
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
     * @var \Symfony\Component\Console\Application
     */
    protected $app;

    /**
     * @var \Psr\Container\ContainerInterface The global container object, which holds all your services.
     */
    protected $ci;

    /**
     * @var string The resource locator scheme
     */
    protected $scheme = 'bakery://';

    /**
     * Constructor.
     */
    public function __construct()
    {
        // Create Symfony Console App
        $this->app = new Application('UserFrosting Bakery', \UserFrosting\VERSION);

        // Check for Sprinkles schema file
        $sprinklesFile = @file_get_contents(\UserFrosting\SPRINKLES_SCHEMA_FILE);
        if ($sprinklesFile === false) {
            try {
                $sprinklesFile = $this->setupBaseSprinkleList();
            } catch (\Exception $e) {
                $this->app->renderException($e, new ConsoleOutput());
                exit(1);
            }
        }

        // Setup the sprinkles
        $uf = new UserFrosting(true);

        // Get the container
        $this->ci = $uf->getContainer();

        // Add each commands to the Console App
        try {
            $this->loadCommands();
        } catch (\Exception $e) {
            $this->app->renderException($e, new ConsoleOutput());
            exit(1);
        }
    }

    /**
     * Run the Symfony Console App.
     */
    public function run()
    {
        $this->app->run();
    }

    /**
     * Return the list of available commands for a specific sprinkle.
     */
    protected function loadCommands()
    {
        /**
         * @var ResourceLocator
         */
        $locator = $this->ci->locator;

        // Get Bakery command resources
        $commandResources = $locator->listResources($this->scheme, false, false);

        // Add commands to the App
        foreach ($commandResources as $commandResource) {

            // Translate the resource to a class. Skip if class not found
            if (!$command = $this->getResourceClass($commandResource)) {
                continue;
            }

            // Get command instance
            $instance = new $command();

            // Class must be an instance of symfony command
            if (!$instance instanceof Command) {
                continue;
            }

            // Add command to the Console app
            $instance->setContainer($this->ci);
            $this->app->add($instance);
        }
    }

    /**
     * Transform a Bakery Command Resource into a classpath.
     *
     * @param \UserFrosting\UniformResourceLocator\Resource $file The command resource
     *
     * @return string The command class path
     */
    protected function getResourceClass(Resource $file)
    {
        // Process sprinkle and system commands
        if (!is_null($location = $file->getLocation())) {

            // Format the sprinkle name for the namespace
            $sprinkleName = $file->getLocation()->getName();
            $sprinkleName = Str::studly($sprinkleName);
            $classPath = "\\UserFrosting\\Sprinkle\\$sprinkleName\\Bakery\\{$this->getClassNameFromFile($file)}";
        } else {
            $classPath = "\\UserFrosting\\System\\Bakery\\Command\\{$this->getClassNameFromFile($file)}";
        }

        // Make sure class exist
        if (!class_exists($classPath)) {
            return false;
        }

        return $classPath;
    }

    /**
     * Return the classname from the file instance.
     *
     * @param \UserFrosting\UniformResourceLocator\Resource $file The command resource
     *
     * @return string
     */
    protected function getClassNameFromFile(Resource $file)
    {
        $basePath = str_replace($file->getBasename(), '', $file->getBasePath());
        $className = str_replace('/', '\\', $basePath) . $file->getFilename();

        return $className;
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
            throw new \Exception("File `$model` not found. Please create '$destination' manually and try again.");
        }

        file_put_contents($destination, $sprinklesModelFile);

        return $sprinklesModelFile;
    }
}
