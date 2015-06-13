<?php

namespace UserFrosting;

/*******

/config/*

*******/

// Handles admin-related activities, including site settings, user management, etc
class AdminController extends \UserFrosting\BaseController {

    public function __construct($app){
        $this->_app = $app;
    }

    public function pageSiteSettings(){
        // Access-controlled page
        if (!$this->_app->user->checkAccess('uri_site_settings')){
            $this->_app->notFound();
        }
        
        // Hook for core and plugins to register their settings
        $this->_app->applyHook("settings.register");
        
        $this->_app->render('site-settings.html', [
            'page' => [
                'author' =>         $this->_app->site->author,
                'title' =>          "Site Settings",
                'description' =>    "Global settings for the site, including registration and activation settings, site title, admin emails, and default languages.",
                'alerts' =>         $this->_app->alerts->getAndClearMessages()
            ],
            'settings' => $this->_app->site->getRegisteredSettings(),
            'info'     => $this->_app->site->getSystemInfo(),
            'error_log'=> $this->_app->site->getLog(50)
        ]);    
    }
    
    public function siteSettings(){
        // Get the alert message stream
        $ms = $this->_app->alerts;
        
        $post = $this->_app->request->post();
        
        // Remove CSRF token
        if (isset($post['csrf_token']))
            unset($post['csrf_token']);
            
        // Access-controlled page
        if (!$this->_app->user->checkAccess('update_site_settings')){
            $ms->addMessageTranslated("danger", "ACCESS_DENIED");
            $this->_app->halt(403);
        }
        
        // Hook for core and plugins to register their settings
        $this->_app->applyHook("settings.register");
        
        // Get registered settings
        $registered_settings = $this->_app->site->getRegisteredSettings();
        
        // Ok, check that all posted settings are registered
        foreach ($post as $plugin => $settings){
            if (!isset($registered_settings[$plugin])){
                $ms->addMessageTranslated("danger", "CONFIG_PLUGIN_INVALID", ["plugin" => $plugin]);
                $this->_app->halt(400);
            }
            foreach ($settings as $name => $value){
                if (!isset($registered_settings[$plugin][$name])){
                    $ms->addMessageTranslated("danger", "CONFIG_SETTING_INVALID", ["plugin" => $plugin, "name" => $name]);
                    $this->_app->halt(400);
                }
            }
        }
        
        // TODO: validate setting syntax
        
        // If validation passed, then update
        foreach ($post as $plugin => $settings){
            foreach ($settings as $name => $value){
                $this->_app->site->set($plugin, $name, $value);
            }
        }
        $this->_app->site->store();
    }
    
}
?>