<?php
    
    // First off, we'll grab the Composer dependencies
    require_once '../app/vendor/autoload.php';
    
    // Now, we'll instantiate the application
    $app = new \Slim\App([
        'settings' => [
            'displayErrorDetails' => false
        ]
    ]);       
    
    // Now, we build all of our app dependencies and inject them into the DI container
    $container = $app->getContainer();
    
    // Now, run the sprinkle manager to boot up all our sprinkles - core is implied
    $sm = new \UserFrosting\Core\Sprinkle\SprinkleManager($container, [
        "account"
    ]);
    
    $sm->init();
    
    $app->run();
    