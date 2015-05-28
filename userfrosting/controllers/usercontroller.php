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
                'author' =>         $this->_app->site->author,
                'title' =>          "Dashboard",
                'description' =>    "Your user dashboard.",
                'alerts' =>         $this->_app->alerts->getAndClearMessages(), 
                'schema' =>         $this->_page_schema
            ]
        ]);          
    }

    public function pageAccountSettings(){
        // Access-controlled page
        if (!$this->_app->user->checkAccess('uri_account_settings')){
            $this->_app->notFound();
        }
        
        $validators = new \Fortress\ClientSideValidator($this->_app->config('schema.path') . "/forms/account-settings.json");
        
        $this->_app->render('account-settings.html', [
            'page' => [
                'author' =>         $this->_app->site->author,
                'title' =>          "Account Settings",
                'description' =>    "Update your account settings, including email, display name, and password.",
                'alerts' =>         $this->_app->alerts->getAndClearMessages(), 
                'schema' =>         PageSchema::load("loggedin-simple", $this->_app->config('schema.path') . "/pages/pages.json")
            ],
            "locales" => $this->_app->site->getLocales(),
            "validators" => $validators->formValidationRulesJson()
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
                'author' =>         $this->_app->site->author,
                'title' =>          "Zerg",
                'description' =>    "Dedicated to the pursuit of genetic perfection, the zerg relentlessly hunt down and assimilate advanced species across the galaxy, incorporating useful genetic code into their own.",
                'alerts' =>         $this->_app->alerts->getAndClearMessages(), 
                'schema' =>         $this->_page_schema,
                'active_page' =>    "zerg"
            ]
        ]);          
    }
    
    public function accountSettings(){
        // Load the request schema
        $requestSchema = new \Fortress\RequestSchema($this->_app->config('schema.path') . "/forms/account-settings.json");
        
        // Get the alert message stream
        $ms = $this->_app->alerts; 
        
        // Access control for entire page
        if (!$this->_app->user->checkAccess('uri_account_settings')){
            $ms->addMessageTranslated("danger", "ACCESS_DENIED");
            $this->_app->halt(403);
        }
        
        $data = $this->_app->request->post();
                       
        // Remove csrf_token
        unset($data['csrf_token']);
                        
        // Check current password
        if (!isset($data['passwordcheck']) || !$this->_app->user->verifyPassword($data['passwordcheck'])){
            $ms->addMessageTranslated("danger", "ACCOUNT_PASSWORD_INVALID");
            $this->_app->halt(403);
        }        
                
        // Validate new email, if specified
        if (isset($data['email']) && $data['email'] != $this->_app->user->email){
            // Check authorization
            if (!$this->_app->user->checkAccess('update_account_setting', ['user' => $this->_app->user, 'property' => 'email'])){
                $ms->addMessageTranslated("danger", "ACCESS_DENIED");
                $this->_app->halt(403);
            }
            // Check if address is in use
            if (UserLoader::exists($data['email'], 'email')){
                $ms->addMessageTranslated("danger", "ACCOUNT_EMAIL_IN_USE", $data);
                $this->_app->halt(400);
            }            
        } else {
            $data['email'] = $this->_app->user->email;
        }
            
        // Validate locale, if specified
        if (isset($data['locale']) && $data['locale'] != $this->_app->user->locale){
            // Check authorization
            if (!$this->_app->user->checkAccess('update_account_setting', ['user' => $this->_app->user, 'property' => 'locale'])){
                $ms->addMessageTranslated("danger", "ACCESS_DENIED");
                $this->_app->halt(403);
            }
            // Validate locale
            if (!in_array($data['locale'], $this->_app->site->getLocales())){
                $ms->addMessageTranslated("danger", "ACCOUNT_SPECIFY_LOCALE");
                $this->_app->halt(400);
            }
        } else {
            $data['locale'] = $this->_app->user->locale;
        }
    
        // Validate display_name, if specified
        if (isset($data['display_name']) && $data['display_name'] != $this->_app->user->display_name){
            // Check authorization
            if (!$this->_app->user->checkAccess('update_account_setting', ['user' => $this->_app->user, 'property' => 'display_name'])){
                $ms->addMessageTranslated("danger", "ACCESS_DENIED");
                $this->_app->halt(403);
            }
        } else {
            $data['display_name'] = $this->_app->user->display_name;
        }
    
        // Validate password, if specified and not empty
        if (isset($data['password']) && !empty($data['password'])){
            // Check authorization
            if (!$this->_app->user->checkAccess('update_account_setting', ['user' => $this->_app->user, 'property' => 'password'])){
                $ms->addMessageTranslated("danger", "ACCESS_DENIED");
                $this->_app->halt(403);
            }
        } else {
            // Do not pass to model if no password is specified
            unset($data['password']);
            unset($data['passwordc']);
        }  
        
        // Set up Fortress to validate the request
        $rf = new \Fortress\HTTPRequestFortress($ms, $requestSchema, $data);
        
        // Validate
        if (!$rf->validate()) {
            $this->_app->halt(400);
        }    
        
        // If a new password was specified, hash it
        if (isset($data['password']))
            $data['password'] = Authentication::hashPassword($data['password']);
        
        // Remove passwordc, passwordcheck
        unset($data['passwordc']);
        unset($data['passwordcheck']);
        
        // Looks good, let's update with new values!
        foreach ($data as $name => $value){
            $this->_app->user->$name = $value;
        }
        
        $this->_app->user->store();
        
        $ms->addMessageTranslated("success", "ACCOUNT_SETTINGS_UPDATED");
    }
    
}
?>