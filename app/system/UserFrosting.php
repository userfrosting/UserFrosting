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
        $sprinkleManager->initFromSchema($schemaPath);
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
        global $app;
        $app = new App($this->ci);
        $this->app = $app;

        $slimAppEvent = new SlimAppEvent($this->app);

        $this->fireEvent('onAppInitialize', $slimAppEvent);

        // Set up all routes
        $sprinkleManager->loadRoutes($this->app);

        // Add global middleware
        $this->fireEvent('onAddGlobalMiddleware', $slimAppEvent);

        $app->run();
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
}
