<?php

/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2013-2016 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */

/**
 * Entry point for the /public site.
 *
 * @author Alex Weissman (https://alexanderweissman.com)
 */

// First off, we'll grab the Composer dependencies
require_once '../app/vendor/autoload.php';

use Slim\App;
use Slim\Container;
use UserFrosting\Sprinkle\Core\Initialize\SprinkleManager;

// First, we create our DI container
$container = new Container;

// Set up sprinkle manager service and list our Sprinkles.  Core sprinkle does not need to be explicitly listed.
$container['sprinkleManager'] = function ($c) {
    return new SprinkleManager($c, [
        "account",
        "admin",
        "site-dev"
    ]);
};

// Now, run the sprinkle manager to boot up all our sprinkles
$container->sprinkleManager->init();

// Next, we'll instantiate the application.  Note that the application is required for the SprinkleManager to set up routes.
$app = new App($container);

// Set up all routes
$container->sprinkleManager->loadRoutes($app);

// Middleware
$app->add($container->csrf);

$app->run();
