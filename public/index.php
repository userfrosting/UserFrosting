<?php
    require_once "../userfrosting/config-userfrosting.php";
    

    use UserFrosting as UF;

    // Auth layer
    $app->add(new UF\Authorization());
    
    // Front page
    $app->get('/', function () use ($app) {
        $controller = new AccountController($app);
        $controller->pageHome();
    });

    // Alert stream
    $app->get('/alerts', function () use ($app) {
        $controller = new BaseController($app);
        $controller->alerts();
    });
    
    // JS Config
    $app->get('/js/config.js', function () use ($app) {
        $controller = new BaseController($app);
        $controller->configJS();
    });
    
    // Account management pages
    $app->get('/account/:action', function ($action) use ($app) {    
        $controller = new AccountController($app);
    
        switch ($action) {
            case "login":               return $controller->pageLogin();
            case "logout":              return $controller->logout();        
            case "register":            return $controller->pageRegister();
            case "resend-activation":   return $controller->pageResendActivation();
            case "forgot-password":     return $controller->pageForgotPassword($app->request()->get('token'));    
            default:                    return $controller->page404();   
        }
    });

    $app->post('/account/:action', function ($action) use ($app) {    
        $controller = new AccountController($app);
    
        switch ($action) {
            case "login":               return $controller->login();     
            case "register":            return $controller->pageRegister();
            case "resend-activation":   return $controller->pageResendActivation();
            case "forgot-password":     return $controller->pageForgotPassword($app->request()->get('token'));    
            default:                    return $controller->page404();   
        }
    });    
    
    // Not found page (404)
    $app->notFound(function () use ($app) {
        $controller = new BaseController($app);
        $controller->page404();
    });

    
    // Slim info page (debug mode only)
    $app->get('/sliminfo', function () use ($app) {
        echo "<pre>";
        print_r($app->environment());
        echo "</pre>";
    });

    // PHP info page (debug mode only)
    $app->get('/phpinfo', function () use ($app) {
        echo "<pre>";
        print_r(phpinfo());
        echo "</pre>";
    });
    
    $app->run();


?>
