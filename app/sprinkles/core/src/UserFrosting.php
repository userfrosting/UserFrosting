<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core;

use RocketTheme\Toolbox\Event\Event;
use RocketTheme\Toolbox\Event\EventDispatcher;
use Slim\App;
use Slim\Container;
use UserFrosting\Sprinkle\Core\Sprinkle\SprinkleManager;
use UserFrosting\Support\Exception\FileNotFoundException;

/**
 * UserFrosting Main Class.
 */
class UserFrosting
{
    /**
     * @var Container The global container object, which holds all your services.
     */
    protected $ci;

    /**
     * @var App The Slim application instance.
     */
    protected $app;

    /**
     * Create the UserFrosting application instance.
     *
     * @param bool $cli Is the app in CLI mode. Set to false if setting up in an HTTP/web environment, true if setting up for CLI scripts.
     */
    public function __construct(bool $cli = false)
    {
        // First, we create our DI container
        $this->ci = new Container();

        // Setup UF App
        $this->setupApp($cli);
    }

    /**
     * Fires an event with optional parameters.
     *
     * @param string     $eventName
     * @param Event|null $event
     *
     * @return Event
     */
    public function fireEvent($eventName, Event $event = null)
    {
        /** @var EventDispatcher $eventDispatcher */
        $eventDispatcher = $this->ci->eventDispatcher;

        return $eventDispatcher->dispatch($eventName, $event);
    }

    /**
     * Return the underlying Slim App instance, if available.
     *
     * @return App
     */
    public function getApp(): App
    {
        return $this->app;
    }

    /**
     * Return the DI container.
     *
     * @return Container
     */
    public function getContainer(): Container
    {
        return $this->ci;
    }

    /**
     * Initialize the application.  Set up Sprinkles and the Slim app, define routes, register global middleware, and run Slim.
     */
    public function run(): void
    {
        $this->app->run();
    }

    /**
     * Register system services, load all sprinkles, and add their resources and services.
     */
    protected function setupSprinkles(): void
    {
        // Boot the Sprinkle manager, which creates Sprinkle classes and subscribes them to the event dispatcher
        /** @var SprinkleManager */
        $sprinkleManager = $this->ci->sprinkleManager;

        try {
            $sprinkleManager->initFromSchema(\UserFrosting\SPRINKLES_SCHEMA_FILE);
        } catch (FileNotFoundException $e) {
            if (!$this->ci->cli) {
                $this->renderSprinkleErrorPage($e->getMessage());
            } else {
                $this->renderSprinkleErrorCli($e->getMessage());
            }
        }

        $this->fireEvent('onSprinklesInitialized');

        /**
         * @deprecated 4.5.0 Use `onSprinklesInitialized` event instead
         */
        $this->fireEvent('onSprinklesAddResources');
        $this->fireEvent('onSprinklesRegisterServices');
    }

    /**
     * @param bool $cli
     */
    protected function setupBasicServices(bool $cli): void
    {
        /*
         * Service to tell if the app is currently running in CLI mode.
         *
         * @return bool
         */
        $this->ci['cli'] = function () use ($cli) {
            return $cli;
        };

        /*
         * Set up sprinkle manager service.
         *
         * @return \UserFrosting\Sprinkle\Core\Sprinkle\SprinkleManager
         */
        $this->ci['sprinkleManager'] = function ($c) {
            return new SprinkleManager($c);
        };

        /*
         * Set up the event dispatcher, required by Sprinkles to hook into the UF lifecycle.
         *
         * @return \RocketTheme\Toolbox\Event\EventDispatcher
         */
        $this->ci['eventDispatcher'] = function ($c) {
            return new EventDispatcher();
        };
    }

    /**
     * Setup UserFrosting App, load sprinkles, load routes, etc.
     *
     * @param bool $cli
     */
    protected function setupApp(bool $cli): void
    {
        // Set up facade reference to container.
        Facade::setFacadeContainer($this->ci);

        // Register basic sevices
        $this->setupBasicServices($cli);

        // Setup sprinkles
        $this->setupSprinkles();

        // Set the configuration settings for Slim in the 'settings' service
        $this->ci->settings = $this->ci->config['settings'];

        // Next, we'll instantiate the Slim application.  Note that the application is required for the SprinkleManager to set up routes.
        $this->app = new App($this->ci);

        $slimAppEvent = new SlimAppEvent($this->app);

        $this->fireEvent('onAppInitialize', $slimAppEvent);

        // Add global middleware
        $this->fireEvent('onAddGlobalMiddleware', $slimAppEvent);
    }

    /**
     * Render a basic error page for problems with loading Sprinkles.
     *
     * @param string $errorMessage Message to display [Default ""]
     */
    protected function renderSprinkleErrorPage(string $errorMessage = '')
    {
        ob_clean();
        $title = 'UserFrosting Application Error';
        $errorMessage = 'Unable to start site. Contact owner.<br/><br/>' .
            'Version: UserFrosting ' . \UserFrosting\VERSION . '<br/>' .
            $errorMessage;
        $output = sprintf(
            "<html><head><meta http-equiv='Content-Type' content='text/html; charset=utf-8'>" .
            '<title>%s</title><style>body{margin:0;padding:30px;font:12px/1.5 Helvetica,Arial,Verdana,' .
            'sans-serif;}h1{margin:0;font-size:48px;font-weight:normal;line-height:48px;}strong{' .
            'display:inline-block;width:65px;}</style></head><body><h1>%s</h1>%s</body></html>',
            $title,
            $title,
            $errorMessage
        );
        exit($output);
    }

    /**
     * Render a CLI error message for problems with loading Sprinkles.
     *
     * @param string $errorMessage Message to display [Default ""]
     */
    protected function renderSprinkleErrorCli(string $errorMessage = '')
    {
        exit($errorMessage . PHP_EOL);
    }
}
