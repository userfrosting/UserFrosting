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
use Illuminate\Container\Container;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Events\Dispatcher;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Session\DatabaseSessionHandler;
use Illuminate\Session\FileSessionHandler;
use Interop\Container\ContainerInterface;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\ErrorLogHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use RocketTheme\Toolbox\ResourceLocator\UniformResourceLocator;
use RocketTheme\Toolbox\StreamWrapper\ReadOnlyStream;
use RocketTheme\Toolbox\StreamWrapper\StreamBuilder;
use Slim\Csrf\Guard;
use Slim\Http\Uri;
use Slim\Views\Twig;
use Slim\Views\TwigExtension;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\NullSessionHandler;
use UserFrosting\Assets\AssetBundleSchema;
use UserFrosting\Assets\AssetLoader;
use UserFrosting\Assets\AssetManager;
use UserFrosting\Assets\UrlBuilder\AssetUrlBuilder;
use UserFrosting\Assets\UrlBuilder\CompiledAssetUrlBuilder;
use UserFrosting\I18n\MessageTranslator;
use UserFrosting\Session\Session;
use UserFrosting\Sprinkle\Core\Twig\CoreExtension;
use UserFrosting\Sprinkle\Core\Handler\ShutdownHandler;
use UserFrosting\Sprinkle\Core\Handler\CoreErrorHandler;
use UserFrosting\Sprinkle\Core\Log\MixedFormatter;
use UserFrosting\Sprinkle\Core\Mail\Mailer;
use UserFrosting\Sprinkle\Core\MessageStream;
use UserFrosting\Sprinkle\Core\Router;
use UserFrosting\Sprinkle\Core\Throttle\Throttler;
use UserFrosting\Sprinkle\Core\Throttle\ThrottleRule;
use UserFrosting\Sprinkle\Core\Util\CheckEnvironment;
use UserFrosting\Sprinkle\Core\Util\ClassMapper;
use UserFrosting\Sprinkle\Core\Util\CacheHelper;
use UserFrosting\Support\Exception\BadRequestException;

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
            return new MessageStream($c->session['cache'], $c->config['session.keys.alerts'], $c->translator);
        };

        /**
         * Asset loader service.
         *
         * Loads assets from a specified relative location.
         * Assets are Javascript, CSS, image, and other files used by your site.
         */
        $container['assetLoader'] = function ($c) {
            $basePath = \UserFrosting\APP_DIR . \UserFrosting\DS . \UserFrosting\SPRINKLES_DIR_NAME;
            $pattern = "/^[A-Za-z0-9_\-]+\/assets\//";

            $al = new AssetLoader($basePath, $pattern);
            return $al;
        };

        /**
         * Asset manager service.
         *
         * Loads raw or compiled asset information from your bundle.config.json schema file.
         * Assets are Javascript, CSS, image, and other files used by your site.
         */
        $container['assets'] = function ($c) {
            $config = $c->config;
            $locator = $c->locator;

            // Load asset schema
            if ($config['assets.use_raw']) {
                $baseUrl = $config['site.uri.public'] . '/' . $config['assets.raw.path'];
                $removePrefix = \UserFrosting\APP_DIR_NAME . \UserFrosting\DS . \UserFrosting\SPRINKLES_DIR_NAME;
                $aub = new AssetUrlBuilder($locator, $baseUrl, $removePrefix, 'assets');

                $as = new AssetBundleSchema($aub);
                $as->loadRawSchemaFile($locator->findResource("sprinkles://core/" . $config['assets.raw.schema'], true, true));

                // Extend for loaded sprinkles
                $sprinkles = $c->sprinkleManager->getSprinkles();
                foreach ($sprinkles as $sprinkle) {
                    $resource = $locator->findResource("sprinkles://$sprinkle/" . $config['assets.raw.schema'], true, true);
                    if (file_exists($resource)) {
                        $as->loadRawSchemaFile($resource);
                    }
                }
            } else {
                $baseUrl = $config['site.uri.public'] . '/' . $config['assets.compiled.path'];
                $aub = new CompiledAssetUrlBuilder($baseUrl);

                $as = new AssetBundleSchema($aub);
                $as->loadCompiledSchemaFile($locator->findResource("build://" . $config['assets.compiled.schema'], true, true));
            }

            $am = new AssetManager($aub, $as);

            return $am;
        };

        /**
         * Cache service.
         *
         * @todo Create an option somewhere to flush the cache
         */
        $container['cache'] = function ($c) {
            return CacheHelper::getInstance("_global", $c->config, $c->locator);
        };

        /**
         * Middleware to check environment.
         *
         * @todo We should cache the results of this, the first time that it succeeds.
         */
        $container['checkEnvironment'] = function ($c) {
            $checkEnvironment = new CheckEnvironment($c->view, $c->locator, $c->cache);
            return $checkEnvironment;
        };

        /**
         * Class mapper.
         *
         * Creates an abstraction on top of class names to allow extending them in sprinkles.
         */
        $container['classMapper'] = function ($c) {
            $classMapper = new ClassMapper();
            $classMapper->setClassMapping('throttle', 'UserFrosting\Sprinkle\Core\Model\Throttle');
            return $classMapper;
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
            $configPaths = array_reverse($c->locator->findResources('config://', true, true));

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

            if (isset($config['display_errors'])) {
                ini_set("display_errors", $config['display_errors']);
            }

            // Configure error-reporting
            if (isset($config['error_reporting'])) {
                error_reporting($config['error_reporting']);
            }

            // Configure time zone
            if (isset($config['timezone'])) {
                date_default_timezone_set($config['timezone']);
            }

            return $config;
        };

        /**
         * Initialize CSRF guard middleware.
         *
         * @see https://github.com/slimphp/Slim-Csrf
         */
        $container['csrf'] = function ($c) {
            $csrfKey = $c->config['session.keys.csrf'];

            // Workaround so that we can pass storage into CSRF guard.
            // If we tried to directly pass the indexed portion of `session` (for example, $c->session['site.csrf']),
            // we would get an 'Indirect modification of overloaded element of UserFrosting\Session\Session' error.
            // If we tried to assign an array and use that, PHP would only modify the local variable, and not the session.
            // Since ArrayObject is an object, PHP will modify the object itself, allowing it to persist in the session.
            if (!$c->session->has($csrfKey)) {
                $c->session[$csrfKey] = new \ArrayObject();
            }
            $csrfStorage = $c->session[$csrfKey];

            $onFailure = function ($request, $response, $next) {
                $e = new BadRequestException("The CSRF code was invalid or not provided.");
                $e->addUserMessage('CSRF_MISSING');
                throw $e;

                return $next($request, $response);
            };

            return new Guard($c->config['csrf.name'], $csrfStorage, $onFailure, $c->config['csrf.storage_limit'], $c->config['csrf.strength'], $c->config['csrf.persistent_token']);
        };

        /**
         * Initialize Eloquent Capsule, which provides the database layer for UF.
         *
         * @todo construct the individual objects rather than using the facade
         */
        $container['db'] = function ($c) {
            $config = $c->config;

            $capsule = new Capsule;

            foreach ($config['db'] as $name => $dbConfig) {
                $capsule->addConnection($dbConfig, $name);
            }

            $capsule->setEventDispatcher(new Dispatcher(new Container));

            // Register as global connection
            $capsule->setAsGlobal();

            // Start Eloquent
            $capsule->bootEloquent();

            return $capsule;
        };

        /**
         * Debug logging with Monolog.
         *
         * Extend this service to push additional handlers onto the 'debug' log stack.
         */
        $container['debugLogger'] = function ($c) {
            $logger = new Logger('debug');

            $logFile = $c->locator->findResource('log://debug.log', true, true);

            $handler = new StreamHandler($logFile);

            $formatter = new MixedFormatter(null, null, true);

            $handler->setFormatter($formatter);
            $logger->pushHandler($handler);

            return $logger;
        };

        /**
         * Custom error-handler for recoverable errors.
         */
        $container['errorHandler'] = function ($c) {
            $settings = $c->settings;

            $handler = new CoreErrorHandler($c, $settings['displayErrorDetails']);

            // Register the HttpExceptionHandler.
            $handler->registerHandler('\UserFrosting\Support\Exception\HttpException', '\UserFrosting\Sprinkle\Core\Handler\HttpExceptionHandler');

            // Register the PDOExceptionHandler.
            $handler->registerHandler('\PDOException', '\UserFrosting\Sprinkle\Core\Handler\PDOExceptionHandler');

            // Register the PhpMailerExceptionHandler.
            $handler->registerHandler('\phpmailerException', '\UserFrosting\Sprinkle\Core\Handler\PhpMailerExceptionHandler');

            return $handler;
        };

        /**
         * Error logging with Monolog.
         *
         * Extend this service to push additional handlers onto the 'error' log stack.
         */
        $container['errorLogger'] = function ($c) {
            $log = new Logger('errors');

            $logFile = $c->locator->findResource('log://errors.log', true, true);

            $handler = new StreamHandler($logFile, Logger::WARNING);

            $formatter = new LineFormatter(null, null, true);

            $handler->setFormatter($formatter);
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
         * Mail service.
         */
        $container['mailer'] = function ($c) {
            $mailer = new Mailer($c->mailLogger, $c->config['mail']);

            // Use UF debug settings to override any service-specific log settings.
            if (!$c->config['debug.smtp']) {
                $mailer->getPhpMailer()->SMTPDebug = 0;
            }

            return $mailer;
        };

        /**
         * Mail logging service.
         *
         * PHPMailer will use this to log SMTP activity.
         * Extend this service to push additional handlers onto the 'mail' log stack.
         */
        $container['mailLogger'] = function ($c) {
            $log = new Logger('mail');

            $logFile = $c->locator->findResource('log://mail.log', true, true);

            $handler = new StreamHandler($logFile);
            $formatter = new LineFormatter(null, null, true);

            $handler->setFormatter($formatter);
            $log->pushHandler($handler);

            return $log;
        };

        /**
         * Custom 404 handler.
         *
         * @todo Is it possible to integrate this into the common error-handling system?
         */
        $container['notFoundHandler'] = function ($c) {
            return function ($request, $response) use ($c) {
                if ($request->isXhr()) {
                    $c->alerts->addMessageTranslated("danger", "ERROR.404.TITLE");

                    return $response->withStatus(404);
                } else {
                // Render a custom error page, if it exists
                try {
                    $template = $c->view->getEnvironment()->loadTemplate("pages/error/404.html.twig");
                } catch (\Twig_Error_Loader $e) {
                    $template = $c->view->getEnvironment()->loadTemplate("pages/error/default.html.twig");
                }

                return $response->withStatus(404)
                                ->withHeader('Content-Type', 'text/html')
                                ->write($template->render([]));
                }
            };
        };

        /**
         * Override Slim's default router with the UF router.
         */
        $container['router'] = function ($c) {
            $routerCacheFile = false;
            if (isset($c->settings['routerCacheFile'])) {
                $routerCacheFile = $c->settings['routerCacheFile'];
            }

            return (new Router)->setCacheFile($routerCacheFile);
        };

        /**
         * Start the PHP session, with the name and parameters specified in the configuration file.
         */
        $container['session'] = function ($c) {
            $config = $c->config;

            // Create appropriate handler based on config
            if ($config['session.handler'] == 'file') {
                $fs = new FileSystem;
                $handler = new FileSessionHandler($fs, $c->locator->findResource('session://'), $config['session.minutes']);
            } else if ($config['session.handler'] == 'database') {
                $connection = $c->db->connection();
                // Table must exist, otherwise an exception will be thrown
                $handler = new DatabaseSessionHandler($connection, $config['session.database.table'], $config['session.minutes']);
            } else {
                throw new \Exception("Bad session handler type '{$config['session.handler']}' specified in configuration file.");
            }

            // Create, start and return a new wrapper for $_SESSION
            $session = new Session($handler, $config['session']);
            $session->start();

            // Create the session cache
            $session['cache'] = CacheHelper::getInstance("_s".session_id(), $c->config, $c->locator);

            return $session;
        };

        /**
         * Custom shutdown handler, for dealing with fatal errors.
         */
        $container['shutdownHandler'] = function ($c) {
            // This takes the entire container, so we don't have to initialize any other services unless absolutely necessary.
            return new ShutdownHandler($c);
        };

        /**
         * Request throttler.
         *
         * Throttles (rate-limits) requests of a predefined type, with rules defined in site config.
         */
        $container['throttler'] = function ($c) {
            $throttler = new Throttler($c->classMapper);

            $config = $c->config;

            if ($config->has('throttles') && ($config['throttles'] !== null)) {
                foreach ($config['throttles'] as $type => $rule) {
                    if ($rule) {
                        $throttleRule = new ThrottleRule($rule['method'], $rule['interval'], $rule['delays']);
                        $throttler->addThrottleRule($type, $throttleRule);
                    } else {
                        $throttler->addThrottleRule($type, null);
                    }
                }
            }

            return $throttler;
        };

        /**
         * Translation service, for translating message tokens.
         */
        $container['translator'] = function ($c) {

            // Create and inject new config item
            $translator = new MessageTranslator();

            // Add search paths for all locale files.  Include them in reverse order to allow locale files added later to override earlier files.
            $localePaths = array_reverse($c->locator->findResources('locale://', true, true));

            $translator->setPaths($localePaths);

            $config = $c->config;

            // Make sure the locale config is a valid string
            if (!is_string($config['site.locales.default']) || $config['site.locales.default'] == "") {
                throw new \UnexpectedValueException("The locale config is not a valid string.");
            }

            // Load the base locale file(s) as specified in the configuration
            $locales = explode(',', $config['site.locales.default']);
            foreach ($locales as $locale) {

                // Make sure it's a valid string before loading
                if (is_string($locale) && $locale != "") {
                    $translator->loadLocaleFiles(trim($locale));
                }
            }

            return $translator;
        };

        /**
         * Set up Twig as the view, adding template paths for all sprinkles and the Slim Twig extension.
         *
         * Also adds the UserFrosting core Twig extension, which provides additional functions, filters, global variables, etc.
         */
        $container['view'] = function ($c) {
            $templatePaths = $c->locator->findResources('templates://', true, true);

            $view = new Twig($templatePaths);

            $loader = $view->getLoader();

            $sprinkles = $c->sprinkleManager->getSprinkles();
            $sprinkles[] = 'core';

            // Add other Sprinkles' templates namespaces
            foreach ($sprinkles as $sprinkle) {
                if ($path = $c->locator->findResource('sprinkles://'.$sprinkle.'/templates/', true, false)) {
                    $loader->addPath($path, $sprinkle);
                }
            }

            $twig = $view->getEnvironment();

            if ($c->config['cache.twig']) {
                $twig->setCache($c->locator->findResource('cache://twig', true, true));
            }

            if ($c->config['debug.twig']) {
                $twig->enableDebug();
            }

            // Register the Slim extension with Twig
            $slimExtension = new TwigExtension(
                $c['router'],
                $c['request']->getUri()
            );
            $view->addExtension($slimExtension);

            // Register the core UF extension with Twig
            $coreExtension = new CoreExtension($c);
            $view->addExtension($coreExtension);

            return $view;
        };
    }
}
