<?php

/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @author    Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */
namespace UserFrosting\ServicesProvider;

use Cartalyst\Sentinel\Native\Facades\Sentinel;

use Dotenv\Dotenv;
use Dotenv\Exception\InvalidPathException;

use Illuminate\Database\Capsule\Manager as Capsule;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\ErrorLogHandler;

use RocketTheme\Toolbox\ResourceLocator\UniformResourceLocator;
use RocketTheme\Toolbox\StreamWrapper\ReadOnlyStream;
use RocketTheme\Toolbox\StreamWrapper\StreamBuilder;

use Slim\Http\Uri;

use UserFrosting\Assets\AssetManager;
use UserFrosting\Assets\AssetBundleSchema;
use UserFrosting\Util\CheckEnvironment;

use UserFrosting\Extension\UserFrostingExtension as UserFrostingExtension;

use UserFrosting\I18n\MessageTranslator;

// For sessions
use Symfony\Component\HttpFoundation\Session\Storage\Handler\NullSessionHandler;
use \Illuminate\Filesystem\Filesystem;
use Illuminate\Session\FileSessionHandler;
use Illuminate\Session\DatabaseSessionHandler;

use UserFrosting\Session\Session;

/**
 * Registers services for UserFrosting, such as config, database, asset manager, translator, etc.
 */
class UserFrostingServicesProvider
{
    /**
     * Register UserFrosting's default services.
     *
     * @param Container $container A DI container implementing ArrayAccess and container-interop.
     */
    public function register($container)
    {
        // Site config object (separate from Slim settings)
        if (!isset($container['config'])){
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
            
                // TODO: add search paths for config files in third-party packages
                $config->setPaths([
                    \UserFrosting\APP_DIR . '/' . \UserFrosting\CORE_DIR_NAME . '/' . \UserFrosting\CONFIG_DIR_NAME
                ]);
                
                // Get configuration mode from environment
                $mode = getenv("UF_MODE") ?: "";
                $config->loadConfigurationFiles($mode);
                
                // Set some PHP parameters, if specified in config
                
                // Determines if uncatchable errors will be rendered in the response
                if (isset($config['display_errors']))
                    ini_set("display_errors", $config['display_errors']);
                
                // Configure error-reporting
                if (isset($config['error_reporting']))
                    error_reporting($config['error_reporting']);
                
                // Configure time zone
                if (isset($config['timezone']))
                    date_default_timezone_set($config['timezone']);
                    
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
        }
        
        if (!isset($container['session'])){         
            $container['session'] = function ($c) {
                $config = $c->get('config');
                
                // Create appropriate handler based on config
                
                
                
                //$fs = new FileSystem;
                //$handler = new FileSessionHandler($fs, \UserFrosting\APP_DIR . "/sessions");
                
                
                $connection = $c->get('db')->connection();
                $table = 'session';
                // Table must exist, otherwise an exception will be thrown
                $handler = new DatabaseSessionHandler($connection, $table, $config['session.minutes']);
                
                // Create and return a new wrapper for $_SESSION
                return new Session($handler, $config['session']);
            };
        }        
        
        if (!isset($container['alerts'])){
            // Set up persistent message stream for alerts.
            $container['alerts'] = function ($c) {
                // Message stream depends on translator.  TODO: inject as dependency into MessageStream
                $c->get('translator');
                
                $session = $c->get('session');
                
                if (!$session['site.alerts'])
                    $session['site.alerts'] = new \UserFrosting\MessageStream();
                    
                return $session['site.alerts'];
            };
        }        
        
        if (!isset($container['assets'])){
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
                $as->loadRawSchemaFile(\UserFrosting\ROOT_DIR . '/' . \UserFrosting\BUILD_DIR_NAME . '/bundle.config.json');
                $as->loadCompiledSchemaFile(\UserFrosting\ROOT_DIR . '/' . \UserFrosting\BUILD_DIR_NAME . '/bundle.result.json');
                
                $am->setBundleSchema($as);
                
                return $am;
            };
        }

        if (!isset($container['db'])){
            /**
             * Initialize Eloquent Capsule
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
        }
        
        if (!isset($container['translator'])){        
            // Set up translator.
            $container['translator'] = function ($container) {
                $translator = new MessageTranslator();
                
                // Set the translation path and default language path.
                // TODO: we should rewrite MessageTranslator for extendability.  So, we would set a default translation table and then recursively merge in the target language table.
                $translator->setTranslationTable(\UserFrosting\APP_DIR . '/' . \UserFrosting\CORE_DIR_NAME . '/' . \UserFrosting\LOCALE_DIR_NAME . "/en_US.php");
                $translator->setDefaultTable(\UserFrosting\APP_DIR . '/' .  \UserFrosting\CORE_DIR_NAME . '/' . \UserFrosting\LOCALE_DIR_NAME . "/en_US.php");
                
                // Register translator with MessageStream
                \UserFrosting\MessageStream::setTranslator($translator);
                
                return $translator;
            };
        }
        
        if (!isset($container['locator'])){        
            // Initialize locator
            $container['locator'] = function ($c) {
                $locator = new UniformResourceLocator(\UserFrosting\APP_DIR . '/' . \UserFrosting\CORE_DIR_NAME);
                
                // TODO: set in config or defines.php
                $locator->addPath('core', '', '');                
                $locator->addPath('assets', '', \UserFrosting\ASSET_DIR_NAME);
                $locator->addPath('schema', '', \UserFrosting\SCHEMA_DIR_NAME);
                
                // Use locator to initialize streams
                ReadOnlyStream::setLocator($locator);
                $sb = new StreamBuilder([
                    'core' => '\\RocketTheme\\Toolbox\\StreamWrapper\\ReadOnlyStream',
                    'assets' => '\\RocketTheme\\Toolbox\\StreamWrapper\\ReadOnlyStream',
                    'schema' => '\\RocketTheme\\Toolbox\\StreamWrapper\\ReadOnlyStream'
                ]);
                
                return $locator;
            };
        }
        
        if (!isset($container['view'])){        
            // Register Twig component on Slim container
            $container['view'] = function ($container) {
                $view = new \Slim\Views\Twig(\UserFrosting\APP_DIR . '/' . \UserFrosting\CORE_DIR_NAME . '/' . \UserFrosting\TEMPLATE_DIR_NAME, [
                    //'cache' => ''
                ]);
                
                // Register Twig as a view extension
                $view->addExtension(new \Slim\Views\TwigExtension(
                    $container['router'],
                    $container['request']->getUri()
                ));
                
                $twig = $view->getEnvironment();
                
                // Register the UserFrosting extension with Twig  
                $twig_extension = new UserFrostingExtension($container);
                $twig->addExtension($twig_extension);   
                    
                return $view;
            };  
        }
            
        if (!isset($container['shutdownHandler'])){         
            // Custom shutdown handler, for dealing with fatal errors
            $container['shutdownHandler'] = function ($c) {
                $alerts = $c->get('alerts');
                $translator = $c->get('translator');
                $request = $c->get('request');
                $response = $c->get('response');
                
                return new \UserFrosting\Handler\ShutdownHandler($request, $response, $alerts, $translator);
            };
        }
        
        // Custom error-handler
        $container['errorHandler'] = function ($c) {
            $alerts = $c->get('alerts');
            $config = $c->get('config');
            $view = $c->get('view');
            $settings = $c->get('settings');
            $errorLogger = $c->get('errorLogger');
               
            return new \UserFrosting\Handler\UserFrostingErrorHandler($config, $alerts, $view, $errorLogger, $settings['displayErrorDetails']);
        };        
    
        // Custom 404 handler.  TODO: handle xhr case, just like errorHandler
        $container['notFoundHandler'] = function ($c) {
            return function ($request, $response) use ($c) {
                return $c->view->render($response, 'pages/error/404.html.twig') 
                    ->withStatus(404)
                    ->withHeader('Content-Type', 'text/html');
            };
        };
        
        // Error logging with Monolog
        $container['errorLogger'] = function ($c) {
            $log = new Logger('errors');
            $handler = new StreamHandler(\UserFrosting\APP_DIR . '/' . \UserFrosting\CORE_DIR_NAME . '/' . \UserFrosting\LOG_DIR_NAME . '/errors.log', Logger::WARNING);
            $log->pushHandler($handler);
            return $log;
        };
        
        // Check environment middleware
        $container['checkEnvironment'] = function ($c) {
            $checkEnvironment = new CheckEnvironment($c->get('view'));
            return $checkEnvironment;
        };        
    }
}
