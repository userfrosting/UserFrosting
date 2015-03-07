<?php

namespace UserFrosting;

// Handles account controller options
class UserController extends \UserFrosting\BaseController {

    public function __construct($app){
        $this->_app = $app;
        
        // Load account pages schema.  You may override this in individual pages.
        $this->_page_schema = PageSchema::load("user", $this->_app->config('schema.path') . "/pages/pages.json");
    }

    public function pageDashboard(){
        $this->_app->render('pages/user/dashboard.html', [
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
}
?>