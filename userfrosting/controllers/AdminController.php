<?php

namespace UserFrosting;

// Handles admin-related activities, including site settings, user management, etc
class AdminController extends \UserFrosting\BaseController {

    public function __construct($app){
        $this->_app = $app;
        
        // Load account pages schema.  You may override this in individual pages.
        $this->_page_schema = PageSchema::load("default", $this->_app->config('schema.path') . "/pages/pages.json");
    }

    public function pageUsers(){
        // Access-controlled page
        if (!$this->_app->user->checkAccess('uri_users')){
            $this->_app->notFound();
        }
        
        $users = UserLoader::fetchAll();
        
        $this->_app->render('users.html', [
            'page' => [
                'author' =>         $this->_app->site->author,
                'title' =>          "Users",
                'description' =>    "A listing of the users for your site.  Provides management tools including the ability to edit user details, manually activate users, enable/disable users, and more.",
                'alerts' =>         $this->_app->alerts->getAndClearMessages(), 
                'schema' =>         $this->_page_schema
            ],
            "users" => $users
        ]);          
    }    
}
?>