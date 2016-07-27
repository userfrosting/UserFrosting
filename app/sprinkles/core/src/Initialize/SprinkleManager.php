<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2013-2016 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */
namespace UserFrosting\Sprinkle\Core\Initialize;

use Interop\Container\ContainerInterface;
use RocketTheme\Toolbox\ResourceLocator\UniformResourceLocator;
use RocketTheme\Toolbox\StreamWrapper\ReadOnlyStream;
use RocketTheme\Toolbox\StreamWrapper\StreamBuilder;

/**
 * Sprinkle manager class.
 *
 * Loads a series of sprinkles, running their bootstrapping code and including their routes.
 * @author Alex Weissman (https://alexanderweissman.com)
 */
class SprinkleManager
{

    protected $ci;
    
    protected $sprinkles;
    
    /**
     * Create a new SprinkleManager object.
     *
     * @param ContainerInterface $ci The global container object, which holds all your services.
     */
    public function __construct(ContainerInterface $ci, $sprinkles = [])
    {
        $this->ci = $ci;
        $this->setupLocator();
        $this->setSprinkles($sprinkles);
    }
   
    public function setSprinkles($sprinkles)
    {
        // Create core sprinkle
        $this->sprinkles['core'] = new \UserFrosting\Sprinkle\Core\Core($this->ci);
    
        // Create other sprinkle objects
        foreach ($sprinkles as $name) {
            $className = ucfirst($name);
            $fullClassName = "UserFrosting\\Sprinkle\\$className\\$className";
            // TODO: check that class exists
            
            $this->sprinkles[$name] = new $fullClassName($this->ci);
        }
    }
    
    public function setupLocator()
    {
        // Initialize locator
        $this->ci['locator'] = function ($c) {
            $locator = new UniformResourceLocator(\UserFrosting\ROOT_DIR);
            
            $locator->addPath('build', '', \UserFrosting\BUILD_DIR_NAME);
            $locator->addPath('log', '', \UserFrosting\APP_DIR_NAME . '/' . \UserFrosting\LOG_DIR_NAME);    
            $locator->addPath('cache', '', \UserFrosting\APP_DIR_NAME . '/' . \UserFrosting\CACHE_DIR_NAME);
            $locator->addPath('session', '', \UserFrosting\APP_DIR_NAME . '/' . \UserFrosting\SESSION_DIR_NAME);
            $locator->addPath('sprinkles', '', \UserFrosting\APP_DIR_NAME . '/' . \UserFrosting\SPRINKLES_DIR_NAME);
            
            // Use locator to initialize streams
            ReadOnlyStream::setLocator($locator);
            $sb = new StreamBuilder([
                'build' => '\\RocketTheme\\Toolbox\\StreamWrapper\\Stream',
                'log' => '\\RocketTheme\\Toolbox\\StreamWrapper\\Stream',
                'cache' => '\\RocketTheme\\Toolbox\\StreamWrapper\\Stream',
                'session' => '\\RocketTheme\\Toolbox\\StreamWrapper\\Stream',
                'sprinkles' => '\\RocketTheme\\Toolbox\\StreamWrapper\\ReadOnlyStream',
                'assets' => '\\RocketTheme\\Toolbox\\StreamWrapper\\ReadOnlyStream',
                'schema' => '\\RocketTheme\\Toolbox\\StreamWrapper\\ReadOnlyStream',
                'templates' => '\\RocketTheme\\Toolbox\\StreamWrapper\\ReadOnlyStream',
                'locale' => '\\RocketTheme\\Toolbox\\StreamWrapper\\ReadOnlyStream',
                'config' => '\\RocketTheme\\Toolbox\\StreamWrapper\\ReadOnlyStream',
                'routes' => '\\RocketTheme\\Toolbox\\StreamWrapper\\ReadOnlyStream'
            ]);
            
            return $locator;
        };
    }
    
    public function init()
    {
        $locator = $this->ci['locator'];
        
        foreach ($this->sprinkles as $name => $sprinkle) {        
            // Add locator services for each sprinkle
            
            $sprinklesDirFragment = \UserFrosting\APP_DIR_NAME . '/' . \UserFrosting\SPRINKLES_DIR_NAME . "/$name/";
            
            $locator->addPath('assets', '', $sprinklesDirFragment . \UserFrosting\ASSET_DIR_NAME);
            $locator->addPath('schema', '', $sprinklesDirFragment . \UserFrosting\SCHEMA_DIR_NAME);
            $locator->addPath('templates', '', $sprinklesDirFragment . \UserFrosting\TEMPLATE_DIR_NAME);
            $locator->addPath('locale', '', $sprinklesDirFragment . \UserFrosting\LOCALE_DIR_NAME);
            $locator->addPath('config', '', $sprinklesDirFragment . \UserFrosting\CONFIG_DIR_NAME);
            $locator->addPath('routes', '', $sprinklesDirFragment . \UserFrosting\ROUTE_DIR_NAME);
                
            /* These are streams that can be subnavigated to core or specific sprinkles (e.g. "templates://core/")
               This would allow specifically selecting core or a particular sprinkle.  Not sure if we need this.
               $locator->addPath('templates', 'core', $coreDirFragment . '/' . \UserFrosting\TEMPLATE_DIR_NAME);
            */
            
            // Initialize the sprinkle
            $sprinkle->init();            
        }
        
        // Set some PHP parameters, if specified in config
        $config = $this->ci['config'];
        
        if (isset($config['display_errors']))
            ini_set("display_errors", $config['display_errors']);
        
        // Configure error-reporting
        if (isset($config['error_reporting']))
            error_reporting($config['error_reporting']);
        
        // Configure time zone
        if (isset($config['timezone']))
            date_default_timezone_set($config['timezone']);
        
        // Get shutdownHandler set up.  This needs to be constructed explicitly because it's invoked natively by PHP.
        $this->ci['shutdownHandler'];         
        
        // Finally, include all defined routes in route directory.  Include them in reverse order to allow higher priority routes to override lower priority.
        global $app;
        $routePaths = array_reverse($locator->findResources('routes://', true, true));
        foreach ($routePaths as $path) {
            $routeFiles = glob($path . '/*.php');
            foreach ($routeFiles as $routeFile){
                require_once $routeFile;
            }
        }
    }
}
