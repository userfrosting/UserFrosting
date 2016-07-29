<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2013-2016 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */
namespace UserFrosting\Sprinkle\Core\ServicesProvider;

use Dotenv\Dotenv;
use Dotenv\Exception\InvalidPathException;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Session\DatabaseSessionHandler;
use Illuminate\Session\FileSessionHandler;
use Interop\Container\ContainerInterface;
use Monolog\Handler\ErrorLogHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use RocketTheme\Toolbox\ResourceLocator\UniformResourceLocator;
use RocketTheme\Toolbox\StreamWrapper\ReadOnlyStream;
use RocketTheme\Toolbox\StreamWrapper\StreamBuilder;
use Slim\Http\Uri;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\NullSessionHandler;
use UserFrosting\Assets\AssetManager;
use UserFrosting\Assets\AssetBundleSchema;
use UserFrosting\I18n\MessageTranslator;
use UserFrosting\Session\Session;
use UserFrosting\Sprinkle\Core\Twig\CoreExtension;
use UserFrosting\Sprinkle\Core\Handler\ShutdownHandler;
use UserFrosting\Sprinkle\Core\Handler\CoreErrorHandler;
use UserFrosting\Sprinkle\Core\MessageStream;
use UserFrosting\Sprinkle\Core\Model\UFModel;
use UserFrosting\Sprinkle\Core\Util\CheckEnvironment;

/**
 * UserFrosting core services provider.
 *
 * Registers core services for UserFrosting, such as config, database, asset manager, translator, etc.
 * @author Alex Weissman (https://alexanderweissman.com)
 */
class CoreServicesProvider
{
    /**
     * Register UserFrosting's core services.
     *
     * @param ContainerInterface $container A DI container implementing ArrayAccess and container-interop.
     */
    public function register(ContainerInterface $container)
    { 
        /**
         * Flash messaging service.
         *
         * Persists error/success messages between requests in the session.
         */
        $container['alerts'] = function ($c) {
            return new \UserFrosting\Sprinkle\Core\MessageStream($c->get('session'), 'site.alerts', $c->get('translator'));
        };       
    
        /**
         * Asset manager service.
         *
         * Loads raw or compiled asset information from your bundle.config.json schema file.
         * Assets are Javascript, CSS, image, and other files used by your site.
         */
        $container['assets'] = function ($c) {
            $config = $c->get('config');
            
            // TODO: map stream identifier ("asset://") to desired relative URLs?
            $base_url = $config['site.uri.public'];
            $raw_assets_path = $config['site.uri.assets-raw'];
            $use_raw_assets = $config['use_raw_assets'];
            
            $am = new AssetManager($base_url, $use_raw_assets);
            $am->setRawAssetsPath($raw_assets_path);
            $am->setCompiledAssetsPath($config['site.uri.assets']);
            
            // Load asset schema
            $as = new AssetBundleSchema();
            $as->loadRawSchemaFile($c->get('locator')->findResource('build://bundle.config.json', true, true));
            $as->loadCompiledSchemaFile($c->get('locator')->findResource('build://bundle.result.json', true, true));
            
            $am->setBundleSchema($as);
            
            return $am;
        };
        
        /**
         * Middleware to check environment.
         *
         * @todo We should cache the results of this, the first time that it succeeds.
         */
        $container['checkEnvironment'] = function ($c) {
            $checkEnvironment = new CheckEnvironment($c->get('view'), $c->get('locator'));
            return $checkEnvironment;
        };
        
        /**
         * Site config service (separate from Slim settings).
         *
         * Will attempt to automatically determine which config file(s) to use based on the value of the UF_MODE environment variable.
         */
        $container['config'] = function ($c) {
            // Grab any relevant dotenv variables from the .env file
            try {
                $dotenv = new Dotenv(\UserFrosting\APP_DIR);
                $dotenv->load();
            } catch (InvalidPathException $e) {
                // Skip loading the environment config file if it doesn't exist.
            }
            
            // Create and inject new config item
            $config = new \UserFrosting\Config\Config();
        
            // Add search paths for all config files.  Include them in reverse order to allow config files added later to override earlier files.
            $configPaths = array_reverse($c->get('locator')->findResources('config://', true, true));
            
            $config->setPaths($configPaths);
            
            // Get configuration mode from environment
            $mode = getenv("UF_MODE") ?: "";
            $config->loadConfigurationFiles($mode);
                
            // Construct base url from components, if not explicitly specified
            if (!isset($config['site.uri.public'])) {
                $base_uri = $config['site.uri.base'];
                
                $public = new Uri(
                    $base_uri['scheme'],
                    $base_uri['host'],
                    $base_uri['port'],
                    $base_uri['path']
                );
                
                // Slim\Http\Uri likes to add trailing slashes when the path is empty, so this fixes that.
                $config['site.uri.public'] = trim($public, '/');
            }
            
            if (isset($config['display_errors']))
                ini_set("display_errors", $config['display_errors']);
            
            // Configure error-reporting
            if (isset($config['error_reporting']))
                error_reporting($config['error_reporting']);
            
            // Configure time zone
            if (isset($config['timezone']))
                date_default_timezone_set($config['timezone']);
            
            return $config;
        };
        
        /**
         * Initialize Eloquent Capsule, which provides the database layer for UF.
         *
         * @todo construct the individual objects rather than using the facade
         */
        $container['db'] = function ($c) {
            $config = $c->get('config');
            
            $capsule = new Capsule;
            $capsule->addConnection([
                'driver'    => $config['db.driver'],
                'host'      => $config['db.host'],
                'database'  => $config['db.database'],
                'username'  => $config['db.username'],
                'password'  => $config['db.password'],
                'charset'   => $config['db.charset'],
                'collation' => $config['db.collation'],
                'prefix'    => $config['db.prefix']
            ]);
            
            // Register as global connection
            $capsule->setAsGlobal();
            
            // Start Eloquent
            $capsule->bootEloquent();
            
            // Set container for data model
            UFModel::$ci = $c;
            
            return $capsule;
        };
        
        /**
         * Custom error-handler for recoverable errors.
         */
        $container['errorHandler'] = function ($c) {
            $settings = $c->get('settings');
            
            $handler = new CoreErrorHandler($c, $settings['displayErrorDetails']);
            
            // Register the HttpExceptionHandler.
            $handler->registerHandler('\UserFrosting\Support\Exception\HttpException', '\UserFrosting\Sprinkle\Core\Handler\HttpExceptionHandler');
            
            return $handler;
        };
        
        /**
         * Error logging with Monolog.
         */
        $container['errorLogger'] = function ($c) {
            $log = new Logger('errors');
            
            $logFile = $c->get('locator')->findResource('log://errors.log', true, true);
            
            $handler = new StreamHandler($logFile, Logger::WARNING);
            $log->pushHandler($handler);
            return $log;
        };
        
        /**
         * Path/file locator service.
         *
         * Register custom streams for the application, and add paths for app-level streams.
         */
        $container['locator'] = function ($c) {
           
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
        
        /**
         * Custom 404 handler.
         *
         * @todo Handle xhr case, just like we do in errorHandler
         */
        $container['notFoundHandler'] = function ($c) {
            return function ($request, $response) use ($c) {
                return $c->view->render($response, 'pages/error/404.html.twig') 
                    ->withStatus(404)
                    ->withHeader('Content-Type', 'text/html');
            };
        };
        
        /**
         * Override Slim's default router with the UF router.
         */
        $container['router'] = function ($c) {
            $routerCacheFile = false;
            if (isset($c->get('settings')['routerCacheFile'])) {
                $routerCacheFile = $c->get('settings')['routerCacheFile'];
            }
            
            return (new \UserFrosting\Sprinkle\Core\Router)->setCacheFile($routerCacheFile);
        };
        
        /**
         * Start the PHP session, with the name and parameters specified in the configuration file.
         */                
        $container['session'] = function ($c) {
            $config = $c->get('config');
            
            // Create appropriate handler based on config
            if ($config['session.handler'] == 'file') {
                $fs = new FileSystem;
                $handler = new FileSessionHandler($fs, $c->get('locator')->findResource('session://'), $config['session.minutes']);
            } else if ($config['session.handler'] == 'database') {
                $connection = $c->get('db')->connection();
                $table = 'session';
                // Table must exist, otherwise an exception will be thrown
                $handler = new DatabaseSessionHandler($connection, $table, $config['session.minutes']);
            } else {
                throw new \Exception("Bad session handler type '{$config['session.handler']}' specified in configuration file.");
            }
            
            // Create and return a new wrapper for $_SESSION
            return new Session($handler, $config['session']);
        };    
        
        /**
         * Custom shutdown handler, for dealing with fatal errors.
         */
        $container['shutdownHandler'] = function ($c) {
            $alerts = $c->get('alerts');
            $translator = $c->get('translator');
            $request = $c->get('request');
            $response = $c->get('response');
            
            return new ShutdownHandler($request, $response, $alerts, $translator);
        };
        
        /**
         * Translation service, for translating message tokens.
         */
        $container['translator'] = function ($c) {
            $translator = new MessageTranslator();
            
            // Set the translation path and default language path.
            // TODO: we should rewrite MessageTranslator for extendability.  So, we would set a default translation table and then recursively merge in the target language table using something like `addTranslationTable`.
            $translator->setTranslationTable('locale://en_US.php');
            $translator->setDefaultTable('locale://en_US.php');
                
            return $translator;
        };
        
        /**
         * Set up Twig as the view, adding template paths for all sprinkles and the Slim Twig extension.
         *
         * Also adds the UserFrosting core Twig extension, which provides additional functions, filters, global variables, etc.
         */
        $container['view'] = function ($c) {
            $templatePaths = $c->locator->findResources('templates://', true, true);
            
            $view = new \Slim\Views\Twig($templatePaths);
            
            $twig = $view->getEnvironment();
            
            if ($c->config['cache.twig']) {
                $twig->setCache($c->locator->findResource('cache://twig', true, true));
            }
            
            if ($c->config['debug.twig']) {
                $twig->enableDebug();
            }    
            
            // Register Twig as a view extension
            $view->addExtension(new \Slim\Views\TwigExtension(
                $c['router'],
                $c['request']->getUri()
            ));
                
            // Register the core UF extension with Twig  
            $coreExtension = new CoreExtension($c);
            $view->addExtension($coreExtension);   
                
            return $view;
        };
    }
}
