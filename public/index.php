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

use UserFrosting\Sprinkle\Core\Initialize\SprinkleManager;

// Now, we'll instantiate the application
$app = new \Slim\App([
    'settings' => [
        'displayErrorDetails' => false
    ]
]);       


// Now, we build all of our app dependencies and inject them into the DI container
$container = $app->getContainer();

// Now, run the sprinkle manager to boot up all our sprinkles - core is implied
$sm = new SprinkleManager($container, [
    "account",
    "site"
]);

$sm->init();

// Middleware
$app->add($container->get('csrf'));


$app->run();
