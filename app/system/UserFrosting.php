<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2013-2016 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */
namespace UserFrosting\System;

use RocketTheme\Toolbox\Event\EventDispatcher;
use RocketTheme\Toolbox\Event\Event;
use Slim\App;
use Slim\Container;
use UserFrosting\Sprinkle\Core\Facades\Facade;
use UserFrosting\Support\Exception\FileNotFoundException;

class UserFrosting
{
    protected $ci;

    protected $app;

    public function __construct()
    {
        // First, we create our DI container
        $this->ci = new Container;

        // Set up facade reference to container.
        Facade::setFacadeContainer($this->ci);
    }

    /**
     * Initialize the application.  Register core services and resources and load all sprinkles.
     */
    public function run()
    {
        // Register system services
        $serviceProvider = new ServicesProvider();
        $serviceProvider->register($this->ci);

        // Expected path to `sprinkles.json`
        $schemaPath = \UserFrosting\APP_DIR . '/' . \UserFrosting\SPRINKLES_DIR_NAME . '/sprinkles.json';

        // Boot the Sprinkle manager, which creates Sprinkle classes and subscribes them to the event dispatcher
        $sprinkleManager = $this->ci->sprinkleManager;

        try {
            $sprinkleManager->initFromSchema($schemaPath);
        } catch (FileNotFoundException $e) {
            $this->renderSprinkleErrorPage($e->getMessage());
        }

        $this->fireEvent('onSprinklesInitialized');

        // Add Sprinkle resources (assets, templates, etc) to locator
        $sprinkleManager->addResources();
        $this->fireEvent('onSprinklesAddResources');

        // Register Sprinkle services
        $sprinkleManager->registerAllServices();
        $this->fireEvent('onSprinklesRegisterServices');

        // Set the configuration settings for Slim in the 'settings' service
        $this->ci->settings = $this->ci->config['settings'];

        // Get shutdownHandler set up.  This needs to be constructed explicitly because it's invoked natively by PHP.
        $this->ci->shutdownHandler;

        // Next, we'll instantiate the Slim application.  Note that the application is required for the SprinkleManager to set up routes.
        $this->app = new App($this->ci);

        $slimAppEvent = new SlimAppEvent($this->app);

        $this->fireEvent('onAppInitialize', $slimAppEvent);

        // Set up all routes
        $this->loadRoutes();

        // Add global middleware
        $this->fireEvent('onAddGlobalMiddleware', $slimAppEvent);

        $this->app->run();
    }

    /**
     * Fires an event with optional parameters.
     *
     * @param  string $eventName
     * @param  Event  $event
     *
     * @return Event
     */
    public function fireEvent($eventName, Event $event = null)
    {
        /** @var EventDispatcher $events */
        $eventDispatcher = $this->ci->eventDispatcher;
        
        return $eventDispatcher->dispatch($eventName, $event);
    }

    /**
     * Include all defined routes in route stream.
     *
     * Include them in reverse order to allow higher priority routes to override lower priority.
     */
    public function loadRoutes()
    {
        // Since routes aren't encapsulated in a class yet, we need this workaround :(
        global $app;
        $app = $this->app;

        $routePaths = array_reverse($this->ci->locator->findResources('routes://', true, true));
        foreach ($routePaths as $path) {
            $routeFiles = glob($path . '/*.php');
            foreach ($routeFiles as $routeFile) {
                require_once $routeFile;
            }
        }
    }

    protected function renderSprinkleErrorPage($errorMessage = "")
    {
        ob_clean();
        $title = "UserFrosting Application Error";
        $errorMessage = "Unable to start site. Contact owner.<br/><br/>" .
            "Version: UserFrosting ".\UserFrosting\VERSION."<br/>" .
            $errorMessage;
        $output = sprintf(
            "<html><head><meta http-equiv='Content-Type' content='text/html; charset=utf-8'>" .
            "<title>%s</title><style>body{margin:0;padding:30px;font:12px/1.5 Helvetica,Arial,Verdana," .
            "sans-serif;}h1{margin:0;font-size:48px;font-weight:normal;line-height:48px;}strong{" .
            "display:inline-block;width:65px;}</style></head><body><h1>%s</h1>%s</body></html>",
            $title,
            $title,
            $errorMessage
        );
        exit($output);
    }
}
