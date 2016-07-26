<?php

    use UserFrosting\Core\ServicesProvider\UserFrostingServicesProvider;
    
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
    
    // Register default UserFrosting services
    $ufServicesProvider = new UserFrostingServicesProvider();
    $ufServicesProvider->register($container);
    
    // Set up the locator
    $locator = $container->get('locator');
    
    // Feel free to register any additional services here
    
    $config = $container->get('config');
    
    // Get shutdownHandler set up.  This needs to be constructed explicitly because it's invoked natively by PHP.
    $container['shutdownHandler'];     
    
    // Load the account sprinkle.  This should be moved to a sprinkle manager class, which will automatically run init for all these classes.
    $accountSprinkle = new \UserFrosting\Account\Account($container);
    $accountSprinkle->init();
    
    //$container['db'];
    
    // Finally, include all defined routes in route directory.  Include them in reverse order to allow higher priority routes to override lower priority.
    $routePaths = array_reverse($locator->findResources('routes://', true, true));
    foreach ($routePaths as $path) {
        $routeFiles = glob($path . '/*.php');
        foreach ($routeFiles as $routeFile){
            require_once $routeFile;
        }
    }
    
    $app->run();
    