<?php

    /**
     * Routes for /config/* URLs.  Handles admin-related activities, including site settings, etc
     *
     * @package UserFrosting
     * @author Alex Weissman
     */
    
    use UserFrosting as UF;
    
    global $app;
    
    // Build the minified, concatenated CSS and JS
    $app->get('/config/build', function() use ($app){
        // Access-controlled page
        if (!$app->user->checkAccess('uri_minify')){
            $app->notFound();
        }
        
        $app->schema->build(true);
        $app->alerts->addMessageTranslated("success", "MINIFICATION_SUCCESS");
        $app->redirect($app->urlFor('uri_settings'));
    });      
    
    /**
     * Renders the site settings page.
     *
     * This page provides an interface for modifying site settings, especially those handled by the SiteSettings class.
     * It also shows some basic configuration information for the site, along with a nicely formatted readout of the PHP error log.
     * This page requires authentication (and should generally be limited to the root user).
     * Request type: GET
     */    
    $app->get('/config/settings/?', function () use ($app) {
        // Access-controlled page
        if (!$app->user->checkAccess('uri_site_settings')){
            $app->notFound();
        }
        
        // Hook for core and plugins to register their settings
        $app->applyHook("settings.register");
        
        $app->render('config/site-settings.twig', [
            'settings' => $app->site->getRegisteredSettings(),
            'info'     => $app->site->getSystemInfo(),
            'error_log'=> $app->site->getLog(50)
        ]);
    })->name('uri_settings');
        
    // Error log page
    $app->get('/errorlog/?', function () use ($app) {
        // Access-controlled page
        if (!$app->user->checkAccess('uri_error_log')){
            $app->notFound();
        }
        $log = $app->site->getLog();
        echo "<pre>";
        echo implode("<br>",$log['messages']);
        echo "</pre>";
    });    
    
    // PHP info page
    $app->get('/phpinfo/?', function () use ($app) {
        // Access-controlled page
        if (!$app->user->checkAccess('uri_php_info')){
            $app->notFound();
        }    
        echo "<pre>";
        print_r(phpinfo());
        echo "</pre>";
    });
    
    // Slim info page
    $app->get('/sliminfo/?', function () use ($app) {
        // Access-controlled page
        if (!$app->user->checkAccess('uri_slim_info')){
            $app->notFound();
        }
        echo "<pre>";
        print_r($app->environment());
        echo "</pre>";
    });
    
    /**
     * Processes a request to update the site settings.
     *
     * Processes the request from the site settings form, checking that:
     * 1. The setting name has been registered with the SiteSettings object.
     * This route requires authentication.
     * Request type: POST
     * @todo validate setting syntax
     */
    $app->post('/config/settings/?', function () use ($app) {
        // Get the alert message stream
        $ms = $app->alerts;

        $post = $app->request->post();

        // Remove CSRF token
        if (isset($post['csrf_token']))
            unset($post['csrf_token']);

        // Access-controlled page
        if (!$app->user->checkAccess('update_site_settings')){
            $ms->addMessageTranslated("danger", "ACCESS_DENIED");
            $app->halt(403);
        }

        // Hook for core and plugins to register their settings
        $app->applyHook("settings.register");

        // Get registered settings
        $registered_settings = $app->site->getRegisteredSettings();

        // Ok, check that all posted settings are registered
        foreach ($post as $plugin => $settings){
            if (!isset($registered_settings[$plugin])){
                $ms->addMessageTranslated("danger", "CONFIG_PLUGIN_INVALID", ["plugin" => $plugin]);
                $app->halt(400);
            }
            foreach ($settings as $name => $value){
                if (!isset($registered_settings[$plugin][$name])){
                    $ms->addMessageTranslated("danger", "CONFIG_SETTING_INVALID", ["plugin" => $plugin, "name" => $name]);
                    $app->halt(400);
                }
            }
        }

        // TODO: validate setting syntax

        // If validation passed, then update
        foreach ($post as $plugin => $settings){
            foreach ($settings as $name => $value){
                $app->site->set($plugin, $name, $value);
            }
        }
        $app->site->store();     
    });
    