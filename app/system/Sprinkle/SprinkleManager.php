<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\System\Sprinkle;

use Illuminate\Support\Str;
use Interop\Container\ContainerInterface;
use UserFrosting\Support\Exception\FileNotFoundException;
use UserFrosting\Support\Exception\JsonException;

/**
 * Sprinkle manager class.
 *
 * Manages a collection of loaded Sprinkles for the application.
 * Handles Sprinkle class creation, event subscription, services registration, and resource stream registration.
 * @author Alex Weissman (https://alexanderweissman.com)
 */
class SprinkleManager
{
    /**
     * @var ContainerInterface The global container object, which holds all your services.
     */
    protected $ci;

    /**
     * @var Sprinkle[] An array of sprinkles.
     */
    protected $sprinkles = [];

    /**
     * @var string Path to the sprinkles directory. Will be used to register the location with the ResourceLocator
     */
    protected $sprinklesPath = \UserFrosting\SPRINKLES_DIR . \UserFrosting\DS;

    /**
     * Create a new SprinkleManager object.
     *
     * @param ContainerInterface $ci The global container object, which holds all your services.
     */
    public function __construct(ContainerInterface $ci)
    {
        $this->ci = $ci;
    }

    /**
     * Register resource streams for all base sprinkles.
     * For each sprinkle, register its resources and then run its initializer
     */
    public function addResources()
    {
        foreach ($this->sprinkles as $sprinkleName => $sprinkle) {
            $this->addSprinkleResources($sprinkleName);
        }
    }

    /**
     * Register a sprinkle as a locator location
     * @param string $sprinkleName
     */
    public function addSprinkleResources($sprinkleName)
    {
        /** @var \UserFrosting\UniformResourceLocator\ResourceLocator $locator */
        $locator = $this->ci->locator;
        $locator->registerLocation($sprinkleName, $this->getSprinklePath($sprinkleName));
    }

    /**
     * Returns sprinkle base path from name
     *
     * @param  string $sprinkleName
     * @return string
     */
    public function getSprinklePath($sprinkleName)
    {
        // Get Sprinkle and make sure it exist
        $sprinkle = $this->getSprinkle($sprinkleName);
        if (!$sprinkle) {
            throw new FileNotFoundException("Sprinkle `$sprinkleName` doesn't exist.");
        }

        // Get path and make sure it exist
        $path = $this->getSprinklesPath() . $sprinkle;
        if (!file_exists($path)) {
            throw new FileNotFoundException("Sprinkle `$sprinkleName` should be found at `$path`, but that directory doesn't exist.");
        }

        return $path;
    }

    /**
     * Returns the sprinkle class
     *
     * @param  string $sprinkleName
     * @return string
     */
    protected function getSprinkleClass($sprinkleName)
    {
        $className = Str::studly($sprinkleName);

        return $this->getSprinkleClassNamespace($sprinkleName) . "\\$className";
    }

    /**
     * Returns the claculated sprinkle namespace
     * @param  string $sprinkleName
     * @return string The Sprinkle Namespace
     */
    public function getSprinkleClassNamespace($sprinkleName)
    {
        $className = Str::studly($sprinkleName);

        return "UserFrosting\\Sprinkle\\$className";
    }

    /**
     * Returns the sprinkle service provider class
     *
     * @param  string $sprinkleName
     * @return string
     */
    protected function getSprinkleDefaultServiceProvider($sprinkleName)
    {
        return $this->getSprinkleClassNamespace($sprinkleName) . '\\ServicesProvider\\ServicesProvider';
    }

    /**
     * Takes the name of a Sprinkle, and creates an instance of the initializer object (if defined).
     *
     * Creates an object of a subclass of UserFrosting\System\Sprinkle\Sprinkle if defined for the sprinkle (converting to StudlyCase).
     * Otherwise, returns null.
     * @param  string $sprinkleName The name of the Sprinkle to initialize.
     * @return mixed  Sprinkle class instance or null if no such class exist
     */
    public function bootSprinkle($sprinkleName)
    {
        $fullClassName = $this->getSprinkleClass($sprinkleName);

        // Check that class exists.  If not, set to null
        if (class_exists($fullClassName)) {
            $sprinkle = new $fullClassName($this->ci);

            return $sprinkle;
        } else {
            return;
        }
    }

    /**
     * Returns a list of available sprinkle names.
     *
     * @return string[]
     */
    public function getSprinkleNames()
    {
        return array_keys($this->sprinkles);
    }

    /**
     * Returns a list of available sprinkles.
     *
     * @return Sprinkle[]
     */
    public function getSprinkles()
    {
        return $this->sprinkles;
    }

    /**
     * Initialize a list of Sprinkles, instantiating their boot classes (if they exist),
     * and subscribing them to the event dispatcher.
     *
     * @param string[] $sprinkleNames
     */
    public function init(array $sprinkleNames)
    {
        foreach ($sprinkleNames as $sprinkleName) {
            $sprinkle = $this->bootSprinkle($sprinkleName);

            if ($sprinkle) {
                // Subscribe the sprinkle to the event dispatcher
                $this->ci->eventDispatcher->addSubscriber($sprinkle);
            }

            $this->sprinkles[$sprinkleName] = $sprinkle;
        }
    }

    /**
     * Initialize all base sprinkles in a specified Sprinkles schema file (e.g. 'sprinkles.json').
     *
     * @param string $schemaPath
     */
    public function initFromSchema($schemaPath)
    {
        $baseSprinkleNames = $this->loadSchema($schemaPath)->base;
        $this->init($baseSprinkleNames);
    }

    /**
     * Return if a Sprinkle is available
     * Can be used by other Sprinkles to test if their dependencies are met
     *
     * @param  string $sprinkleName The name of the Sprinkle
     * @return bool
     */
    public function isAvailable($sprinkleName)
    {
        return (bool) $this->getSprinkle($sprinkleName);
    }

    /**
     * Find sprinkle value from the sprinkles.json
     *
     * @param  string       $sprinkleName
     * @return string|false Return sprinkle name or false if sprinkle not found
     */
    public function getSprinkle($sprinkleName)
    {
        $mathches = preg_grep("/^$sprinkleName$/i", $this->getSprinkleNames());

        if (count($mathches) <= 0) {
            return false;
        }

        return array_values($mathches)[0];
    }

    /**
     * Interate through the list of loaded Sprinkles, and invoke their ServiceProvider classes.
     */
    public function registerAllServices()
    {
        foreach ($this->getSprinkleNames() as $sprinkleName) {
            $this->registerServices($sprinkleName);
        }
    }

    /**
     * Register services for a specified Sprinkle.
     *
     * @param string $sprinkleName
     */
    public function registerServices($sprinkleName)
    {
        $fullClassName = $this->getSprinkleDefaultServiceProvider($sprinkleName);

        // Check that class exists, and register services
        if (class_exists($fullClassName)) {
            // Register core services
            $serviceProvider = new $fullClassName();
            $serviceProvider->register($this->ci);
        }
    }

    /**
     * Returns sprinklePath parameter
     *
     * @return string
     */
    public function getSprinklesPath()
    {
        return $this->sprinklesPath;
    }

    /**
     * Sets sprinklePath parameter
     *
     * @param  string $sprinklesPath
     * @return static
     */
    public function setSprinklesPath($sprinklesPath)
    {
        $this->sprinklesPath = $sprinklesPath;

        return $this;
    }

    /**
     * Load list of Sprinkles from a JSON schema file (e.g. 'sprinkles.json').
     *
     * @param  string   $schemaPath
     * @return string[]
     */
    protected function loadSchema($schemaPath)
    {
        $sprinklesFile = @file_get_contents($schemaPath);

        if ($sprinklesFile === false) {
            $errorMessage = "Error: Unable to determine Sprinkle load order. File '$schemaPath' not found or unable to read. Please create a 'sprinkles.json' file and try again.";
            throw new FileNotFoundException($errorMessage);
        }

        // Make sure sprinkle contains valid json
        if (!$data = json_decode($sprinklesFile)) {
            $errorMessage = "Error: Unable to determine Sprinkle load order. File '$schemaPath' doesn't contain valid json : " . json_last_error_msg();
            throw new JsonException($errorMessage);
        }

        return $data;
    }
}
