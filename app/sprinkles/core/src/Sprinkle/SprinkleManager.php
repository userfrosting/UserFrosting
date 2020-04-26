<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Sprinkle;

use Illuminate\Support\Str;
use Psr\Container\ContainerInterface;
use UserFrosting\Support\Exception\FileNotFoundException;
use UserFrosting\Support\Exception\JsonException;

/**
 * Sprinkle manager class.
 *
 * Manages a collection of loaded Sprinkles for the application.
 * Handles Sprinkle class creation, event subscription, services registration, and resource stream registration.
 *
 * @author Alex Weissman (https://alexanderweissman.com)
 */
class SprinkleManager
{
    /**
     * @var ContainerInterface The global container object, which holds all your services.
     */
    protected $ci;

    /**
     * @var (Sprinkle|null)[] An array of sprinkles : array<name, instance>
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
     * Initialize all base sprinkles in a specified Sprinkles schema file (e.g. 'sprinkles.json').
     *
     * @param string $schemaPath
     */
    public function initFromSchema(string $schemaPath): void
    {
        $baseSprinkleNames = $this->loadSchema($schemaPath);
        $this->init($baseSprinkleNames);
    }

    /**
     * Initialize a list of Sprinkles, instantiating their boot classes (if they exist),
     * and subscribing them to the event dispatcher.
     *
     * @param string[] $sprinkleNames
     */
    public function init(array $sprinkleNames): void
    {
        foreach ($sprinkleNames as $sprinkleName) {
            $sprinkle = $this->bootSprinkle($sprinkleName);

            if ($sprinkle) {
                $sprinkle->registerServices();

                // Subscribe the sprinkle to the event dispatcher
                $this->ci->eventDispatcher->addSubscriber($sprinkle);
            }

            // Register service
            $this->registerServices($sprinkleName);
            $this->addSprinkleResources($sprinkleName);

            $this->sprinkles[$sprinkleName] = $sprinkle;
        }
    }

    /**
     * Takes the name of a Sprinkle, and creates an instance of the initializer object (if defined).
     *
     * Creates an object of a subclass of UserFrosting\System\Sprinkle\Sprinkle if defined for the sprinkle (converting to StudlyCase).
     * Otherwise, returns null.
     *
     * @param string $sprinkleName The name of the Sprinkle to initialize.
     *
     * @return Sprinkle|null Sprinkle class instance or null if no such class exist
     *
     * @todo Enforce a Sprinkle class as of 5.0 or 4.5.0
     */
    public function bootSprinkle(string $sprinkleName): ?Sprinkle
    {
        $fullClassName = $this->getSprinkleClass($sprinkleName);

        // Check that class exists.  If not, set to null
        if (class_exists($fullClassName)) {
            $sprinkle = new $fullClassName($this->ci);

            if (!$sprinkle instanceof Sprinkle) {
                throw new \Exception("$fullClassName must be an instance of " . Sprinkle::class); // TODO Custom exception
            }

            return $sprinkle;
        } else {
            return null;
        }
    }

    /**
     * Return if a Sprinkle is available
     * Can be used by other Sprinkles to test if their dependencies are met.
     *
     * @param string $sprinkleName The name of the Sprinkle
     *
     * @return bool
     */
    public function isAvailable(string $sprinkleName): bool
    {
        return array_key_exists($sprinkleName, $this->sprinkles);
    }

    /**
     * Returns a list of available sprinkle names.
     *
     * @return string[]
     */
    public function getSprinkleNames(): array
    {
        return array_keys($this->sprinkles);
    }

    /**
     * Returns a list of available sprinkles.
     *
     * @return (Sprinkle|null)[]
     */
    public function getSprinkles(): array
    {
        return $this->sprinkles;
    }

    /**
     * Return the sprinkle class instance by name.
     *
     * @param string $sprinkleName
     *
     * @return Sprinkle|null Return sprinkle name or false if sprinkle not found
     */
    public function getSprinkle(string $sprinkleName): ?Sprinkle
    {
        if (!$this->isAvailable($sprinkleName)) {
            throw new \Exception("Sprinkle $sprinkleName not found."); //TODO : Change for custom exception
        }

        return $this->sprinkles[$sprinkleName];
    }

    /**
     * Register a sprinkle as a locator location.
     *
     * @param string $sprinkleName
     */
    public function addSprinkleResources(string $sprinkleName): void
    {
        /** @var \UserFrosting\UniformResourceLocator\ResourceLocator $locator */
        $locator = $this->ci->locator;
        $locator->registerLocation($sprinkleName, $this->getSprinklePath($sprinkleName));
    }

    /**
     * Returns sprinkle base path from name.
     *
     * @param string $sprinkleName
     *
     * @return string
     */
    public function getSprinklePath(string $sprinkleName): string
    {
        // Get path and make sure it exist
        $path = $this->getSprinklesPath() . $sprinkleName;
        if (!file_exists($path)) {
            throw new FileNotFoundException("Sprinkle `$sprinkleName` should be found at `$path`, but that directory doesn't exist.");
        }

        return $path;
    }

    /**
     * Returns the claculated sprinkle namespace.
     *
     * @param string $sprinkleName
     *
     * @return string The Sprinkle Namespace
     */
    public function getSprinkleClassNamespace(string $sprinkleName): string
    {
        $className = Str::studly($sprinkleName);

        return "UserFrosting\\Sprinkle\\$className";
    }

    /**
     * Returns sprinklePath parameter.
     *
     * @return string
     */
    public function getSprinklesPath(): string
    {
        return $this->sprinklesPath;
    }

    /**
     * Sets sprinklePath parameter.
     *
     * @param string $sprinklesPath
     *
     * @return static
     */
    public function setSprinklesPath(string $sprinklesPath)
    {
        $this->sprinklesPath = $sprinklesPath;

        return $this;
    }

    /**
     * Register services for a specified Sprinkle.
     *
     * @param string $sprinkleName
     *
     * @deprecated 4.5.0 Services class should be registered in the main Sprinkle class.
     */
    public function registerServices(string $sprinkleName): void
    {
        //Register the default services
        $fullClassName = $this->getSprinkleDefaultServiceProvider($sprinkleName);

        // Check that class exists, and register services
        if (class_exists($fullClassName)) {
            // Register core services
            $serviceProvider = new $fullClassName();
            $serviceProvider->register($this->ci);
        }
    }

    /**
     * Returns the sprinkle class.
     *
     * @param string $sprinkleName
     *
     * @return string
     */
    protected function getSprinkleClass(string $sprinkleName): string
    {
        $className = Str::studly($sprinkleName);

        return $this->getSprinkleClassNamespace($sprinkleName) . "\\$className";
    }

    /**
     * Returns the sprinkle service provider class.
     *
     * @param string $sprinkleName
     *
     * @return string
     *
     * @deprecated 4.5.0
     */
    protected function getSprinkleDefaultServiceProvider(string $sprinkleName): string
    {
        return $this->getSprinkleClassNamespace($sprinkleName) . '\\ServicesProvider\\ServicesProvider';
    }

    /**
     * Load list of Sprinkles from a JSON schema file (e.g. 'sprinkles.json').
     *
     * @param string $schemaPath
     *
     * @return string[]
     */
    protected function loadSchema(string $schemaPath): array
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

        // Remove duplicates, keep the first one
        // @see https://stackoverflow.com/a/2276400/445757
        $sprinkles = $data->base;
        $sprinkles = array_intersect_key(
            $sprinkles,
            array_unique(array_map('strtolower',$sprinkles))
        );

        return $sprinkles;
    }
}
