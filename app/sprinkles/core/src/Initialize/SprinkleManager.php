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
     * @var array[null|UserFrosting\Sprinkle\Core\Initialize\Sprinkle] An array of sprinkle initialization objects.
     */
    protected $sprinkles = [];
        
    /**
     * Create a new SprinkleManager object.
     *
     * @param ContainerInterface $ci The global container object, which holds all your services.
     */
    public function __construct(ContainerInterface $ci, $sprinkles = [])
    {
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
        $this->registerSprinkleResources('core');
        
        // For each sprinkle (other than Core), register its resources and then run its initializer
        foreach ($this->sprinkles as $name => $sprinkle) {        
            $this->registerSprinkleResources($name);
            
            // Initialize the sprinkle
            if ($sprinkle)
                $sprinkle->init();            
        }
        
        // Set the configuration settings for Slim in the 'settings' service
        $this->ci->settings = $this->ci->config['settings'];
        
        // Get shutdownHandler set up.  This needs to be constructed explicitly because it's invoked natively by PHP.
        $this->ci->shutdownHandler;
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

    public function registerAssets($name)
    {
        $sprinklesDirFragment = \UserFrosting\APP_DIR_NAME . '/' . \UserFrosting\SPRINKLES_DIR_NAME . "/$name/";
        $path = $sprinklesDirFragment . \UserFrosting\ASSET_DIR_NAME;

        $this->ci->locator->addPath('assets', '', $path);

        return $this->ci->locator->getBase() . $path;
    }

    public function registerTemplates($name)
    {
        $sprinklesDirFragment = \UserFrosting\APP_DIR_NAME . '/' . \UserFrosting\SPRINKLES_DIR_NAME . "/$name/";
        $path = $sprinklesDirFragment . \UserFrosting\TEMPLATE_DIR_NAME;

        $this->ci->locator->addPath('templates', '', $path);

        return $this->ci->locator->getBase() . DIRECTORY_SEPARATOR . $path;
    }

    /**
     * Register resource streams for a specified sprinkle.
     */
    public function registerSprinkleResources($name)
    {
        $locator = $this->ci->locator;
        
        $sprinklesDirFragment = \UserFrosting\APP_DIR_NAME . '/' . \UserFrosting\SPRINKLES_DIR_NAME . "/$name/";
        
        $locator->addPath('schema', '', $sprinklesDirFragment . \UserFrosting\SCHEMA_DIR_NAME);
        $locator->addPath('locale', '', $sprinklesDirFragment . \UserFrosting\LOCALE_DIR_NAME);
        $locator->addPath('config', '', $sprinklesDirFragment . \UserFrosting\CONFIG_DIR_NAME);
        $locator->addPath('routes', '', $sprinklesDirFragment . \UserFrosting\ROUTE_DIR_NAME);

        $this->registerAssets($name);
        $this->registerTemplates($name);

        /* This would allow a stream to subnavigate to a specific sprinkle (e.g. "templates://core/")
           Not sure if we need this.
           $locator->addPath('templates', '$name', $sprinklesDirFragment . '/' . \UserFrosting\TEMPLATE_DIR_NAME);
         */
    }
   
    /**
     * Takes a list of sprinkle names, and creates a new sprinkle initializer object for each one (if defined).
     *
     * Creates an object of a subclass of UserFrosting\Sprinkle\Core\Initialize\Sprinkle if defined for the sprinkle (converting to StudlyCase).
     * Otherwise, sets the entry to null in $this->sprinkles.
     * @param array[string] An array of sprinkle names.
     */
    public function setSprinkles($sprinkles)
    {
        // Create other sprinkle objects
        foreach ($sprinkles as $name) {
            $className = Str::studly($name);
            $fullClassName = "\\UserFrosting\\Sprinkle\\$className\\$className";
            // Check that class exists.  If not, set to null
            if (class_exists($fullClassName)) {
                $this->sprinkles[$name] = new $fullClassName($this->ci);
            } else {
                $this->sprinkles[$name] = null;
            }
        }
    }    
}
