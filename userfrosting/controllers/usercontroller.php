<?php

namespace UserFrosting;

// Handles a user's activities, such as user account settings, user dashboard, etc.
class UserController extends \UserFrosting\BaseController {

    public function __construct($app){
        $this->_app = $app;
        
        // Load account pages schema.  You may override this in individual pages.
        $this->_page_schema = PageSchema::load("user", $this->_app->config('schema.path') . "/pages/pages.json");
    }

    public function pageDashboard(){
        // Access-controlled page
        if (!$this->_app->user->checkAccess('uri_dashboard')){
            $this->_app->notFound();
        }
        
        $this->_app->render('dashboard.html', [
            'page' => [
                'author' =>         $this->_app->userfrosting['author'],
                'title' =>          "Dashboard",
                'description' =>    "Your user dashboard.",
                'alerts' =>         $this->_app->alerts->getAndClearMessages(), 
                'schema' =>         $this->_page_schema,
                'active_page' =>    ""
            ]
        ]);          
    }

    public function pageZerg(){
        // Access-controlled page
        if (!$this->_app->user->checkAccess('uri_zerg')){
            $this->_app->notFound();
        }
        
        $this->_page_schema = PageSchema::load("starcraft", $this->_app->config('schema.path') . "/pages/pages.json");
        $this->_app->render('zerg.html', [
            'page' => [
                'author' =>         $this->_app->userfrosting['author'],
                'title' =>          "Zerg",
                'description' =>    "Dedicated to the pursuit of genetic perfection, the zerg relentlessly hunt down and assimilate advanced species across the galaxy, incorporating useful genetic code into their own.",
                'alerts' =>         $this->_app->alerts->getAndClearMessages(), 
                'schema' =>         $this->_page_schema,
                'active_page' =>    "zerg"
            ]
        ]);          
    }
}
?>