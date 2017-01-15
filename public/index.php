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

// Attempt to fetch list of Sprinkles
// TODO: move this to a separate class?
$sprinklesFile = file_get_contents('../app/sprinkles/sprinkles.json');
if ($sprinklesFile === false) {
    ob_clean();
    $title = "UserFrosting Application Error";
    $errorMessage = "Unable to start site. Contact owner.<br/><br/>" .
        "Version: UserFrosting 4 Pre-Alpha<br/>Error: Unable to determine Sprinkle load order.";
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
$sprinkles = json_decode($sprinklesFile)->base;


// Set up sprinkle manager service and list our Sprinkles.  Core sprinkle does not need to be explicitly listed.
$container['sprinkleManager'] = function ($c) use ($sprinkles) {
    return new SprinkleManager($c, $sprinkles);
};

// Now, run the sprinkle manager to boot up all our sprinkles
$container->sprinkleManager->init();

// Next, we'll instantiate the application.  Note that the application is required for the SprinkleManager to set up routes.
$app = new App($container);

// Set up all routes
$container->sprinkleManager->loadRoutes($app);

// Middleware
// Hacky fix to prevent sessions from being hit too much: ignore CSRF middleware for requests for raw assets ;-)
$request = $container->request;
$path = $request->getUri()->getPath();
$csrfBlacklist = [
    $container->config['assets.raw.path']
];

if (!$path || !starts_with($path, $csrfBlacklist)) {
    $app->add($container->csrf);
}

$app->run();
