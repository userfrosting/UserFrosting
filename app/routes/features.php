<?php

    /**
     * Routes for feature pages.  Basically, any pages that aren't part of the core user management system go here.
     *
     * @package UserFrosting
     * @author Alex Weissman
     */
    
    use UserFrosting as UF;
    
    global $app;    
    
    $app->get('/dashboard/?', function () use ($app) {    
        // Access-controlled page
        if (!$app->user->checkAccess('uri_dashboard')){
            $app->notFound();
        }
        
        $app->render('dashboard.twig');          
    });
    
    $app->get('/zerg/?', function () use ($app) {    
        // Access-controlled page
        if (!$app->user->checkAccess('uri_zerg')){
            $app->notFound();
        }
        
        $app->render('users/zerg.twig'); 
    });
    