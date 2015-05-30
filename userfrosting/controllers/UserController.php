<?php

namespace UserFrosting;

/*******

/users/*

*******/

// Handles user-related activities
class UserController extends \UserFrosting\BaseController {

    public function __construct($app){
        $this->_app = $app;
        
        // Load account pages schema.  You may override this in individual pages.
        $this->_page_schema = PageSchema::load("user", $this->_app->config('schema.path') . "/pages/pages.json");
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
    
    public function updateUser($user_id){
        // Load the request schema
        $requestSchema = new \Fortress\RequestSchema($this->_app->config('schema.path') . "/forms/user-update.json");
        
        // Get the alert message stream
        $ms = $this->_app->alerts; 
        
        // Get the target user
        $target_user = UserLoader::fetch($user_id);
        
        /*
        // Access control for entire page
        if (!$this->_app->user->checkAccess('uri_update_user')){
            $ms->addMessageTranslated("danger", "ACCESS_DENIED");
            $this->_app->halt(403);
        }
        */
        
        // Only the master account can edit the master account!
        if (($target_user->id == $this->_app->config('user_id_master')) && $this->_app->user->id != $this->_app->config('user_id_master')) {
            $ms->addMessageTranslated("danger", "ACCESS_DENIED");
            $this->_app->halt(403);
        }
        
        $post = $this->_app->request->post();
                       
        // Remove csrf_token
        unset($post['csrf_token']);
                                
        // Check authorization for submitted fields, if the value has been changed
        foreach ($post as $name => $value) {
            if (isset($target_user->$name) && $post[$name] != $target_user->$name){
                // Check authorization
                if (!$this->_app->user->checkAccess('update_account_setting', ['user' => $target_user, 'property' => $name])){
                    $ms->addMessageTranslated("danger", "ACCESS_DENIED");
                    $this->_app->halt(403);
                }
            }
        }

        // Check that we are not disabling the master account
        if (($target_user->id == $this->_app->config('user_id_master')) && isset($post['enabled']) && $post['enabled'] == "0"){
            $ms->addMessageTranslated("danger", "ACCOUNT_DISABLE_MASTER");
            $this->_app->halt(403);
        }
        
        // Set up Fortress to process the request
        $rf = new \Fortress\HTTPRequestFortress($ms, $requestSchema, $post);                    
    
        // Sanitize
        $rf->sanitize();
    
        // Validate, and halt on validation errors.
        if (!$rf->validate()) {
            $this->_app->halt(400);
        }   
               
        // Get the filtered data
        $data = $rf->data();
        
        // Update the user and generate success messages
        foreach ($data as $name => $value){
            if ($value != $target_user->$name){
                $target_user->$name = $value;
                // Custom success messages (optional)
                if ($name == "enabled") {
                    if ($value == "1")
                        $ms->addMessageTranslated("success", "ACCOUNT_ENABLE_SUCCESSFUL", ["user_name" => $target_user->user_name]);
                    else
                        $ms->addMessageTranslated("success", "ACCOUNT_DISABLE_SUCCESSFUL", ["user_name" => $target_user->user_name]);
                }
                
            }
        }
        
        $target_user->store();        
        
    }
    
}
?>