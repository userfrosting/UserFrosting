<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2013-2016 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */
namespace UserFrosting\Sprinkle\Core\Initialize;

use Illuminate\Support\Str;
use Interop\Container\ContainerInterface;
use UserFrosting\Sprinkle\Core\Facades\Facade;
use UserFrosting\Sprinkle\Core\ServicesProvider\CoreServicesProvider;

/**
 * Sprinkle manager class.
 *
 * Loads a series of sprinkles, running their bootstrapping code and including their routes.
 * @author Alex Weissman (https://alexanderweissman.com)
 */
class SprinkleManager
{
    /**
     * @var ContainerInterface The global container object, which holds all your services.
     */
    protected $ci;

    /**
     * @var string[] An array of sprinkle names.
     */
    protected $sprinkles = [];

    /**
     * @var string The full absolute base path to the sprinkles directory.
     */
    protected $sprinklesPath;

    /**
     * Create a new SprinkleManager object.
     *
     * @param ContainerInterface $ci The global container object, which holds all your services.
     * @param string[] $sprinkles An array of sprinkle names.
     */
    public function __construct(ContainerInterface $ci, $sprinkles = [])
    {
        $this->sprinklesPath = \UserFrosting\APP_DIR_NAME . \UserFrosting\DS . \UserFrosting\SPRINKLES_DIR_NAME . \UserFrosting\DS;
        $this->ci = $ci;
        $this->setSprinkles($sprinkles);
    }

    /**
     * Initialize the application.  Register core services and resources and load all sprinkles.
     */
    public function init()
    {
        // Set up facade reference to container.
        Facade::setFacadeContainer($this->ci);

        // Register core services
        $serviceProvider = new CoreServicesProvider();
        $serviceProvider->register($this->ci);

        // Register core resources
        $this->addSprinkleResources('core');

        // Initialize the core sprinkle
        $sprinkle = $this->initializeSprinkle('core');

        // For each sprinkle (other than Core), register its resources and then run its initializer
        foreach ($this->sprinkles as $name) {
            $this->addSprinkleResources($name);

            // Initialize the sprinkle
            $sprinkle = $this->initializeSprinkle($name);
        }

        // Set the configuration settings for Slim in the 'settings' service
        $this->ci->settings = $this->ci->config['settings'];

        // Get shutdownHandler set up.  This needs to be constructed explicitly because it's invoked natively by PHP.
        $this->ci->shutdownHandler;
    }

    /**
     * Adds assets for a specified Sprinkle to the assets (assets://) stream.
     *
     * @param string $name
     * @return string|bool The full path to the Sprinkle's assets (if found).
     */
    public function addAssets($name)
    {
        $path = $this->sprinklesPath . $name . \UserFrosting\DS . \UserFrosting\ASSET_DIR_NAME;

        $this->ci->locator->addPath('assets', '', $path);

        return $this->ci->locator->findResource('assets://', true, false);
    }

    /**
     * Adds config for a specified Sprinkle to the config (config://) stream.
     *
     * @param string $name
     * @return string|bool The full path to the Sprinkle's config (if found).
     */
    public function addConfig($name)
    {
        $path = $this->sprinklesPath . $name . \UserFrosting\DS . \UserFrosting\CONFIG_DIR_NAME;

        $this->ci->locator->addPath('config', '', $path);

        return $this->ci->locator->findResource('config://', true, false);
    }

    /**
     * Adds extras for a specified Sprinkle to the locale (extra://) stream.
     *
     * @param string $name
     * @return string|bool The full path to the Sprinkle's extras (if found).
     */
    public function addExtras($name)
    {
        $path = $this->sprinklesPath . $name . \UserFrosting\DS . \UserFrosting\EXTRA_DIR_NAME;

        $this->ci->locator->addPath('extra', '', $path);

        return $this->ci->locator->findResource('extra://', true, false);
    }

    /**
     * Adds locales for a specified Sprinkle to the locale (locale://) stream.
     *
     * @param string $name
     * @return string|bool The full path to the Sprinkle's locales (if found).
     */
    public function addLocale($name)
    {
        $path = $this->sprinklesPath . $name . \UserFrosting\DS . \UserFrosting\LOCALE_DIR_NAME;

        $this->ci->locator->addPath('locale', '', $path);

        return $this->ci->locator->findResource('locale://', true, false);
    }

    /**
     * Adds paths to routes for a specified Sprinkle to the routes (routes://) stream.
     *
     * @param string $name
     * @return string|bool The full path to the Sprinkle's routes (if found).
     */
    public function addRoutes($name)
    {
        $path = $this->sprinklesPath . $name . \UserFrosting\DS . \UserFrosting\ROUTE_DIR_NAME;

        $this->ci->locator->addPath('routes', '', $path);

        return $this->ci->locator->findResource('routes://', true, false);
    }

    /**
     * Adds Fortress schema for a specified Sprinkle to the schema (schema://) stream.
     *
     * @param string $name
     * @return string|bool The full path to the Sprinkle's schema (if found).
     */
    public function addSchema($name)
    {
        $path = $this->sprinklesPath . $name . \UserFrosting\DS . \UserFrosting\SCHEMA_DIR_NAME;

        $this->ci->locator->addPath('schema', '', $path);

        return $this->ci->locator->findResource('schema://', true, false);
    }

    /**
     * Adds templates for a specified Sprinkle to the templates (templates://) stream.
     *
     * @param string $name
     * @return string|bool The full path to the Sprinkle's templates (if found).
     */
    public function addTemplates($name)
    {
        $path = $this->sprinklesPath . $name . \UserFrosting\DS . \UserFrosting\TEMPLATE_DIR_NAME;

        $this->ci->locator->addPath('templates', '', $path);

        return $this->ci->locator->findResource('templates://', true, false);
    }

    /**
     * Register resource streams for a specified sprinkle.
     */
    public function addSprinkleResources($name)
    {
        $this->addConfig($name);
        $this->addAssets($name);
        $this->addExtras($name);
        $this->addLocale($name);
        $this->addRoutes($name);
        $this->addSchema($name);
        $this->addTemplates($name);

        /* This would allow a stream to subnavigate to a specific sprinkle (e.g. "templates://core/")
           Not sure if we need this.
           $locator->addPath('templates', '$name', $sprinklesDirFragment . '/' . \UserFrosting\TEMPLATE_DIR_NAME);
         */
    }

    /**
     * Include all defined routes in route stream.
     *
     * Include them in reverse order to allow higher priority routes to override lower priority.
     */
    public function loadRoutes($app)
    {
        $routePaths = array_reverse($this->ci->locator->findResources('routes://', true, true));
        foreach ($routePaths as $path) {
            $routeFiles = glob($path . '/*.php');
            foreach ($routeFiles as $routeFile) {
                require_once $routeFile;
            }
        }
    }

    /**
     * Sets the list of sprinkles.
     *
     * @param string[] $sprinkles An array of sprinkle names.
     */
    public function setSprinkles($sprinkles)
    {
        $this->sprinkles = $sprinkles;
        return $this;
    }

    /**
     * Returns a list of available sprinkles.
     *
     * @return string[]
     * @todo Should this automatically prepend the 'core' Sprinkle as well?
     */
    public function getSprinkles()
    {
        return $this->sprinkles;
    }

    /**
     * Takes the name of a Sprinkle, and creates an instance of the initializer object (if defined).
     *
     * Creates an object of a subclass of UserFrosting\Sprinkle\Core\Initialize\Sprinkle if defined for the sprinkle (converting to StudlyCase).
     * Otherwise, returns null.
     * @param $name The name of the Sprinkle to initialize.
     */
    public function initializeSprinkle($name)
    {
        $className = Str::studly($name);
        $fullClassName = "\\UserFrosting\\Sprinkle\\$className\\$className";

        // Check that class exists.  If not, set to null
        if (class_exists($fullClassName)) {
            $sprinkle = new $fullClassName($this->ci);
            $sprinkle->init();
            return $sprinkle;
        } else {
            return null;
        }
    }

    /**
     * Return if a Sprinkle is available
     * Can be used by other Sprinkles to test if their dependencies are met
     *
     * @param $name The name of the Sprinkle
     */
    public function isAvailable($name)
    {
        return in_array($name, $this->getSprinkles());
    }
}
