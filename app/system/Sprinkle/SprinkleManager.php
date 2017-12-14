<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */
namespace UserFrosting\System\Sprinkle;

use Illuminate\Support\Str;
use Interop\Container\ContainerInterface;
use RocketTheme\Toolbox\Event\EventDispatcher;
use RocketTheme\Toolbox\ResourceLocator\UniformResourceLocator;
use UserFrosting\Support\Exception\FileNotFoundException;

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
     * @var string The full absolute base path to the sprinkles directory.
     */
    protected $sprinklesPath;

    /**
     * @var string[] Keeps track of a mapping from resource stream names to relative paths.
     */
    protected $resourcePaths;

    /**
     * Create a new SprinkleManager object.
     *
     * @param ContainerInterface $ci The global container object, which holds all your services.
     */
    public function __construct(ContainerInterface $ci)
    {
        $this->ci = $ci;
        $this->sprinklesPath = \UserFrosting\APP_DIR_NAME . \UserFrosting\DS . \UserFrosting\SPRINKLES_DIR_NAME . \UserFrosting\DS;

        $this->resourcePaths = [
            'assets' => \UserFrosting\DS . \UserFrosting\ASSET_DIR_NAME,
            'config' => \UserFrosting\DS . \UserFrosting\CONFIG_DIR_NAME,
            'extra' => \UserFrosting\DS . \UserFrosting\EXTRA_DIR_NAME,
            'factories' => \UserFrosting\DS . \UserFrosting\FACTORY_DIR_NAME,
            'locale' => \UserFrosting\DS . \UserFrosting\LOCALE_DIR_NAME,
            'routes' => \UserFrosting\DS . \UserFrosting\ROUTE_DIR_NAME,
            'schema' => \UserFrosting\DS . \UserFrosting\SCHEMA_DIR_NAME,
            'sprinkles' => '',
            'templates' => \UserFrosting\DS . \UserFrosting\TEMPLATE_DIR_NAME
        ];
    }

    /**
     * Adds the relative path for a specified resource type in a Sprinkle to the resource's stream.
     *
     * @param string $resourceName
     * @param string $sprinkleName
     * @return string|bool The full path to specified resource for the specified Sprinkle (if found).
     */
    public function addResource($resourceName, $sprinkleName)
    {
        $resourcePath = $this->resourcePaths[$resourceName];
        $fullPath = $this->sprinklesPath . $sprinkleName . $resourcePath;

        $this->ci->locator->addPath($resourceName, '', $fullPath);

        return $this->ci->locator->findResource("$resourceName://", true, false);

        /* This would allow a stream to subnavigate to a specific sprinkle (e.g. "templates://core/")
           Not sure if we need this.
           $locator->addPath('templates', '$name', $sprinklesDirFragment . '/' . \UserFrosting\TEMPLATE_DIR_NAME);
         */
    }

    /**
     * Register resource streams for all base sprinkles.
     */
    public function addResources()
    {
        // For each sprinkle, register its resources and then run its initializer
        foreach ($this->sprinkles as $sprinkleName => $sprinkle) {
            $this->addResource('config', $sprinkleName);
            $this->addResource('assets', $sprinkleName);
            $this->addResource('extra', $sprinkleName);
            $this->addResource('factories', $sprinkleName);
            $this->addResource('locale', $sprinkleName);
            $this->addResource('routes', $sprinkleName);
            $this->addResource('schema', $sprinkleName);
            $this->addResource('sprinkles', $sprinkleName);
            $this->addResource('templates', $sprinkleName);
        }
    }

    /**
     * Takes the name of a Sprinkle, and creates an instance of the initializer object (if defined).
     *
     * Creates an object of a subclass of UserFrosting\System\Sprinkle\Sprinkle if defined for the sprinkle (converting to StudlyCase).
     * Otherwise, returns null.
     * @param $name The name of the Sprinkle to initialize.
     */
    public function bootSprinkle($name)
    {
        $className = Str::studly($name);
        $fullClassName = "\\UserFrosting\\Sprinkle\\$className\\$className";

        // Check that class exists.  If not, set to null
        if (class_exists($fullClassName)) {
            $sprinkle = new $fullClassName($this->ci);
            return $sprinkle;
        } else {
            return null;
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
     * @param string[] $baseSprinkleNames
     */
    public function init($sprinkleNames)
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
     * @param $name The name of the Sprinkle
     */
    public function isAvailable($name)
    {
        return in_array($name, $this->getSprinkleNames());
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
     */
    public function registerServices($name)
    {
        $className = Str::studly($name);
        $fullClassName = "\\UserFrosting\\Sprinkle\\$className\\ServicesProvider\\ServicesProvider";

        // Check that class exists, and register services
        if (class_exists($fullClassName)) {
            // Register core services
            $serviceProvider = new $fullClassName();
            $serviceProvider->register($this->ci);
        }
    }

    /**
     * Load list of Sprinkles from a JSON schema file (e.g. 'sprinkles.json').
     *
     * @param string $schemaPath
     * @return string[]
     */
    protected function loadSchema($schemaPath)
    {
        $sprinklesFile = @file_get_contents($schemaPath);

        if ($sprinklesFile === false) {
            $errorMessage = "Error: Unable to determine Sprinkle load order.  File '$schemaPath' not found or unable to read. Please create a 'sprinkles.json' file and try again.";
            throw new FileNotFoundException($errorMessage);
        }

        return json_decode($sprinklesFile);
    }
}
