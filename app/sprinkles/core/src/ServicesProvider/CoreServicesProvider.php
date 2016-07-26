<?php

/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @author    Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */
namespace UserFrosting\Sprinkle\Core\ServicesProvider;

use Dotenv\Dotenv;
use Dotenv\Exception\InvalidPathException;

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Session\DatabaseSessionHandler;
use Illuminate\Session\FileSessionHandler;

use Monolog\Handler\ErrorLogHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

use Slim\Http\Uri;

use Symfony\Component\HttpFoundation\Session\Storage\Handler\NullSessionHandler;

use UserFrosting\Assets\AssetManager;
use UserFrosting\Assets\AssetBundleSchema;
use UserFrosting\I18n\MessageTranslator;
use UserFrosting\Session\Session;
use UserFrosting\Sprinkle\Core\Extension\UserFrostingExtension;
use UserFrosting\Sprinkle\Core\Handler\ShutdownHandler;
use UserFrosting\Sprinkle\Core\Handler\UserFrostingErrorHandler;
use UserFrosting\Sprinkle\Core\MessageStream;
use UserFrosting\Sprinkle\Core\Util\CheckEnvironment;

/**
 * Registers core services for UserFrosting, such as config, database, asset manager, translator, etc.
 */
class CoreServicesProvider
{
    /**
     * Register UserFrosting's core services.
     *
     * @param Container $container A DI container implementing ArrayAccess and container-interop.
     */
    public function register($container)
    {
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
         * Site config service (separate from Slim settings).
         *
         * Will attempt to automatically determine which config file(s) to use based on the value of the UF_MODE environment variable.
         */
        $container['config'] = function ($c) {
        
            // Grab any relevant dotenv variables from the .env file
            try {
                $dotenv = new Dotenv(\UserFrosting\APP_DIR);
                $dotenv->load();
            } catch (InvalidPathException $e){
                // Skip loading the environment config file if it doesn't exist.
            }
            
            // Create and inject new config item
            $config = new \UserFrosting\Config\Config();
        
            // Add search paths for all config files
            $configPaths = $c->get('locator')->findResources('config://', true, true);
            
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
            
            return $config;
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
         * Flash messaging service.
         *
         * Persists error/success messages between requests in the session.
         */
        $container['alerts'] = function ($c) {
            // Message stream depends on translator.  TODO: inject as dependency into MessageStream
            $c->get('translator');
            
            $session = $c->get('session');
            
            if (!$session['site.alerts'])
                $session['site.alerts'] = new \UserFrosting\Sprinkle\Core\MessageStream();
                
            return $session['site.alerts'];
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
            
            return $capsule;
        };
        
        /**
         * Translation service, for translating message tokens.
         */
        $container['translator'] = function ($container) {
            $translator = new MessageTranslator();
            
            // Set the translation path and default language path.
            // TODO: we should rewrite MessageTranslator for extendability.  So, we would set a default translation table and then recursively merge in the target language table using something like `addTranslationTable`.
            $translator->setTranslationTable('locale://en_US.php');
            $translator->setDefaultTable('locale://en_US.php');
            
            // Register translator with MessageStream
            MessageStream::setTranslator($translator);
            
            return $translator;
        };
        
        /**
         * Set up Twig as the view, adding template paths for all sprinkles and the Slim Twig extension.
         *
         * Also adds the UserFrosting core Twig extension, which provides additional functions, filters, global variables, etc.
         */
        $container['view'] = function ($c) {
            $templatePaths = $c->get('locator')->findResources('templates://', true, true);
            
            $view = new \Slim\Views\Twig($templatePaths, [
                //'cache' => ''
            ]);
            
            // Register Twig as a view extension
            $view->addExtension(new \Slim\Views\TwigExtension(
                $c['router'],
                $c['request']->getUri()
            ));
            
            $twig = $view->getEnvironment();
            
            /* TODO: enable Twig caching?
            $view = $app->view();
            $view->parserOptions = array(
                'debug' => true,
                'cache' => 'cache://twig'
            );
            */
            
            // Register the UserFrosting extension with Twig  
            $twig_extension = new UserFrostingExtension($c);
            $twig->addExtension($twig_extension);   
                
            return $view;
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
         * Custom error-handler for recoverable errors.
         */
        $container['errorHandler'] = function ($c) {
            $alerts = $c->get('alerts');
            $config = $c->get('config');
            $view = $c->get('view');
            $settings = $c->get('settings');
            $errorLogger = $c->get('errorLogger');
               
            return new UserFrostingErrorHandler($config, $alerts, $view, $errorLogger, $settings['displayErrorDetails']);
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
         * Middleware to check environment.
         *
         * @todo We should cache the results of this, the first time that it succeeds.
         */
        $container['checkEnvironment'] = function ($c) {
            $checkEnvironment = new CheckEnvironment($c->get('view'), $c->get('locator'));
            return $checkEnvironment;
        };        
    }
}
