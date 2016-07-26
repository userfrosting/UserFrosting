<?php
    
    /**
     * Entry point for the /public site.
     *
     * @package UserFrosting
     * @author Alex Weissman
     * @link http://www.userfrosting.com
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
        "account"
    ]);
    
    $sm->init();
    
    $app->run();
    