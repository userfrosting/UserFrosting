<?php

    use UserFrosting\ServicesProvider\UserFrostingServicesProvider as UserFrostingServicesProvider;
    
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
    
    // Feel free to register any additional services here
    
    $config = $container->get('config');
    
    // Get shutdownHandler set up.  This needs to be constructed explicitly because it's invoked natively by PHP.
    $container['shutdownHandler'];     
    
    $container['db'];
    
    // Finally, include all defined routes in route directory
    $route_files = glob(UserFrosting\APP_DIR . '/' . UserFrosting\CORE_DIR_NAME . '/' . UserFrosting\ROUTE_DIR_NAME . "/*.php");
    foreach ($route_files as $route_file){
        require_once $route_file;
    }
    
    $app->run();
    