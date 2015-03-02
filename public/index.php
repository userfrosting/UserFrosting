<?php
    require '../vendor/autoload.php';

    $app = new \Slim\Slim();

    //Define a HTTP GET route:
    
    $app->get('/hello/:name', function ($name) {
        echo "Hello, $name";
    });
    
    //Run the Slim application:
    
    $app->run();


?>
