<?php

    use Illuminate\Database\Capsule\Manager as Capsule;
    use Illuminate\Database\Schema\Blueprint;

    use \Psr\Http\Message\ServerRequestInterface as Request;
    use \Psr\Http\Message\ResponseInterface as Response;

    // Front page
    $app->get('/', function (Request $request, Response $response, $args) {
        $config = $this->config;
        
        return $this->view->render($response, 'pages/index.html.twig');
    })->add('checkEnvironment');

    $app->group('/account', function () {
        $this->get('/register', function (Request $request, Response $response, $args) {
            
            return "Nothing";   
        })->add('checkEnvironment');
    });
    
    $app->get('/install', function (Request $request, Response $response, $args) {

    });
    
    // About page
    $app->get('/about', function (Request $request, Response $response, $args) {
        return $this->view->render($response, 'pages/about.html.twig');     
    })->add('checkEnvironment');      
    
    // Flash alert stream
    $app->get('/alerts', function (Request $request, Response $response, $args) {
        return $response->withJson($this->alerts->getAndClearMessages());
    });   
    