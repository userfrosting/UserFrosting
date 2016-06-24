<?php

namespace UserFrosting;

/**
 * AdminController Class
 *
 * Controller class for /config/* URLs.  Handles admin-related activities, including site settings, etc
 *
 * @package UserFrosting
 * @author Alex Weissman
 * @link http://www.userfrosting.com/navigating/#structure
 */
class AdminController extends \UserFrosting\BaseController {

    /**
     * Create a new AdminController object.
     *
     * @param UserFrosting $app The main UserFrosting app.
     */
    public function __construct($app){
        $this->_app = $app;
    }

    /**
     * Renders the site settings page.
     *
     * This page provides an interface for modifying site settings, especially those handled by the SiteSettings class.
     * It also shows some basic configuration information for the site, along with a nicely formatted readout of the PHP error log.
     * This page requires authentication (and should generally be limited to the root user).
     * Request type: GET
     */
    public function pageSiteSettings(){
        // Access-controlled page
        if (!$this->_app->user->checkAccess('uri_site_settings')){
            $this->_app->notFound();
        }

        // Hook for core and plugins to register their settings
        $this->_app->applyHook("settings.register");

        $this->_app->render('config/site-settings.twig', [
            'settings' => $this->_app->site->getRegisteredSettings(),
            'info'     => $this->_app->site->getSystemInfo(),
            'error_log'=> SiteSettings::getLog(50)
        ]);
    }

    /**
     * Processes a request to update the site settings.
     *
     * Processes the request from the site settings form, checking that:
     * 1. The setting name has been registered with the SiteSettings object.
     * This route requires authentication.
     * Request type: POST
     * @todo validate setting syntax
     */
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
