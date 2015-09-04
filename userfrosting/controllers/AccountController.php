<?php

namespace UserFrosting;

/**
 * AccountController Class
 *
 * Controller class for /account/* URLs.  Handles account-related activities, including login, registration, password recovery, and account settings.
 *
 * @package UserFrosting
 * @author Alex Weissman
 * @link http://www.userfrosting.com/navigating/#structure
 */
class AccountController extends \UserFrosting\BaseController {

    /**
     * Create a new AccountController object.
     *
     * @param UserFrosting $app The main UserFrosting app.
     */
    public function __construct($app){
        $this->_app = $app;
    }

    /**
     * Renders the default home page for UserFrosting.
     *
     * By default, this is the page that non-authenticated users will first see when they navigate to your website's root.
     * Request type: GET
     */
    public function pageHome(){
        $this->_app->render('common/home.html', [
            'page' => [
                'author' =>         $this->_app->site->author,
                'title' =>          "A secure, modern user management system for PHP.",
                'description' =>    "Main landing page for public access to this website.",
                'alerts' =>         $this->_app->alerts->getAndClearMessages(), 
                'active_page' =>    ""
            ]
        ]);  
    }
    
    /**
     * Renders the login page for UserFrosting.
     * By definition, this is a "public page" (does not require authentication).
     * Request type: GET     
     */    
    public function pageLogin(){
        // Forward to home page if user is already logged in
        if (!$this->_app->user->isGuest()){
            $this->_app->redirect($this->_app->urlFor('uri_home'));
        }        
        
        $schema = new \Fortress\RequestSchema($this->_app->config('schema.path') . "/forms/login.json");
        $validators = new \Fortress\ClientSideValidator($schema, $this->_app->translator);
        
        $this->_app->render('common/login.html', [
            'page' => [
                'author' =>         $this->_app->site->author,
                'title' =>          "Login",
                'description' =>    "Login to your UserFrosting account.",
                'alerts' =>         $this->_app->alerts->getAndClearMessages(),     // Starting to violate the Law of Demeter here...
                'active_page' =>    "account/login",
            ],
            'validators' => $validators->formValidationRulesJson()
        ]);
    }

    /**
     * Attempts to render the account registration page for UserFrosting.
     *
     * This allows new (non-authenticated) users to create a new account for themselves on your website.
     * Request type: GET
     * @param bool $can_register Specify whether registration is enabled.  If registration is disabled, they will be redirected to the login page. 
     */       
    public function pageRegister($can_register = false){
        // Get the alert message stream
        $ms = $this->_app->alerts;
        
        // Prevent the user from registering if he/she is already logged in
        if(!$this->_app->user->isGuest()) {
            $ms->addMessageTranslated("danger", "ACCOUNT_REGISTRATION_LOGOUT");
            $this->_app->redirect($this->_app->urlFor('uri_home'));
        }

        // Security measure: do not allow registering new users until the master account has been created.        
        if (!UserLoader::exists($this->_app->config('user_id_master'))){
            $ms->addMessageTranslated("danger", "MASTER_ACCOUNT_NOT_EXISTS");
            $this->_app->redirect($this->_app->urlFor('uri_install'));
        }

        $schema = new \Fortress\RequestSchema($this->_app->config('schema.path') . "/forms/register.json");
        $validators = new \Fortress\ClientSideValidator($schema, $this->_app->translator);                

        $settings = $this->_app->site;
        
        // If registration is disabled, send them back to the login page with an error message
        if (!$settings->can_register){
            $this->_app->alerts->addMessageTranslated("danger", "ACCOUNT_REGISTRATION_DISABLED");
            $this->_app->redirect('login');
        }
    
        $this->_app->render('common/register.html', [
            'page' => [
                'author' =>         $settings->author,
                'title' =>          "Register",
                'description' =>    "Register for a new UserFrosting account.",
                'alerts' =>         $this->_app->alerts->getAndClearMessages(), 
                'active_page' =>    "account/register"                
            ],
            'captcha_image' =>  $this->generateCaptcha(),
            'validators' => $validators->formValidationRulesJson()
        ]);
    }

    /**
     * Render the "lost password" page.  
     *
     * This creates a simple form to allow users who forgot their password to have a time-limited password reset link emailed to them.
     * By default, this is a "public page" (does not require authentication).
     * Request type: GET
     */      
    public function pageForgotPassword(){
      
        $schema = new \Fortress\RequestSchema($this->_app->config('schema.path') . "/forms/forgot-password.json");
        $validators = new \Fortress\ClientSideValidator($schema, $this->_app->translator); 
        
       $this->_app->render('common/forgot-password.html', [
            'page' => [
                'author' =>         $this->_app->site->author,
                'title' =>          "Reset Password",
                'description' =>    "Reset your UserFrosting password.",
                'alerts' =>         $this->_app->alerts->getAndClearMessages(), 
                'active_page' =>    ""
            ],
            'validators' => $validators->formValidationRulesJson()
        ]);
    }

    /**
     * Render the "reset password" page.  
     *
     * This is the actual page that is linked to in the "forgot password" email.
     * By default, this is a "public page" (does not require authentication).     
     * Request type: GET
     */     
    public function pageResetPassword(){
      
        $schema = new \Fortress\RequestSchema($this->_app->config('schema.path') . "/forms/reset-password.json");
        $validators = new \Fortress\ClientSideValidator($schema, $this->_app->translator);         
        
       $this->_app->render('common/reset-password.html', [
            'page' => [
                'author' =>         $this->_app->site->author,
                'title' =>          "Choose a New Password",
                'description' =>    "Reset your UserFrosting password.",
                'alerts' =>         $this->_app->alerts->getAndClearMessages(), 
                'active_page' =>    ""
            ],
            'activation_token' => $this->_app->request->get()['activation_token'],
            'validators' => $validators->formValidationRulesJson()
        ]);
    }
    
    /**
     * Render the "resend account activation link" page.  
     *
     * This is a form that allows users who lost their account activation link to have the link resent to their email address.
     * By default, this is a "public page" (does not require authentication).
     * Request type: GET     
     */        
    public function pageResendActivation(){
    
        $schema = new \Fortress\RequestSchema($this->_app->config('schema.path') . "/forms/resend-activation.json");
        $validators = new \Fortress\ClientSideValidator($schema, $this->_app->translator);         
                 
        $this->_app->render('common/resend-activation.html', [
            'page' => [
                'author' =>         $this->_app->site->author,
                'title' =>          "Resend Activation",
                'description' =>    "Resend the activation email for your new UserFrosting account.",
                'alerts' =>         $this->_app->alerts->getAndClearMessages(), 
                'active_page' =>    ""
            ],
            'validators' => $validators->formValidationRulesJson()
        ]);
    }
    
    /**
     * Account settings page.
     *
     * Provides a form for users to modify various properties of their account, such as display name, email, locale, etc.
     * Any fields that the user does not have permission to modify will be automatically disabled.
     * This page requires authentication.
     * Request type: GET     
     */        
    public function pageAccountSettings(){
        // Access-controlled page
        if (!$this->_app->user->checkAccess('uri_account_settings')){
            $this->_app->notFound();
        }
        
        $schema = new \Fortress\RequestSchema($this->_app->config('schema.path') . "/forms/account-settings.json");
        $validators = new \Fortress\ClientSideValidator($schema, $this->_app->translator);         
        
        $this->_app->render('account-settings.html', [
            'page' => [
                'author' =>         $this->_app->site->author,
                'title' =>          "Account Settings",
                'description' =>    "Update your account settings, including email, display name, and password.",
                'alerts' =>         $this->_app->alerts->getAndClearMessages()
            ],
            "locales" => $this->_app->site->getLocales(),
            "validators" => $validators->formValidationRulesJson()
        ]);          
    }    
    
    /**
     * Processes an account login request.
     *
     * Processes the request from the form on the login page, checking that:
     * 1. The user is not already logged in.
     * 2. Email login is enabled, if an email address was used.
     * 3. The user account exists.
     * 4. The user account is enabled and active.
     * 5. The user entered a valid username/email and password.
     * This route, by definition, is "public access".
     * Request type: POST     
     */        
    public function login(){
        // Load the request schema
        $requestSchema = new \Fortress\RequestSchema($this->_app->config('schema.path') . "/forms/login.json");
        
        // Get the alert message stream
        $ms = $this->_app->alerts; 
        
        // Forward the user to their default page if he/she is already logged in
        if(!$this->_app->user->isGuest()) {
            $ms->addMessageTranslated("warning", "LOGIN_ALREADY_COMPLETE");
            $this->_app->halt(200);
        }
        
        // Set up Fortress to process the request
        $rf = new \Fortress\HTTPRequestFortress($ms, $requestSchema, $this->_app->request->post());
        
        // Sanitize data
        $rf->sanitize();
                
        // Validate, and halt on validation errors.
        if (!$rf->validate(true)) {
            $this->_app->halt(400);
        }
        
        // Get the filtered data
        $data = $rf->data();
        
        // Determine whether we are trying to log in with an email address or a username
        $isEmail = filter_var($data['user_name'], FILTER_VALIDATE_EMAIL);
        
        // If it's an email address, but email login is not enabled, raise an error.
        if ($isEmail && !$this->_app->site->email_login){
            $ms->addMessageTranslated("danger", "ACCOUNT_USER_OR_PASS_INVALID");
            $this->_app->halt(403);
        }
        
        // Load user by email address
        if($isEmail){
            $user = UserLoader::fetch($data['user_name'], 'email');
            if (!$user){
                $ms->addMessageTranslated("danger", "ACCOUNT_USER_OR_PASS_INVALID");
                $this->_app->halt(403);         
            }
        // Load user by user name    
        } else {
            $user = UserLoader::fetch($data['user_name'], 'user_name');
            if (!$user) {
                $ms->addMessageTranslated("danger", "ACCOUNT_USER_OR_PASS_INVALID");
                $this->_app->halt(403);
            }
        }
        
        // Check that the user's account is enabled
        if ($user->enabled == 0){
            $ms->addMessageTranslated("danger", "ACCOUNT_DISABLED");
            $this->_app->halt(403);
        }        
        
        // Check that the user's account is activated
        if ($user->active == 0) {
            $ms->addMessageTranslated("danger", "ACCOUNT_INACTIVE");
            $this->_app->halt(403);
        }
        
        // Here is my password.  May I please assume the identify of this user now?
        if ($user->verifyPassword($data['password']))  {
            $user->login();
            // Create the session
            $_SESSION["userfrosting"]["user"] = $user;
            $this->_app->user = $_SESSION["userfrosting"]["user"];
            $ms->addMessageTranslated("success", "ACCOUNT_WELCOME", $this->_app->user->export());
        } else {
            //Again, we know the password is at fault here, but lets not give away the combination in case of someone bruteforcing
            $ms->addMessageTranslated("danger", "ACCOUNT_USER_OR_PASS_INVALID");
            $this->_app->halt(403);
        }
        
    }
    
    /**
     * Processes an account logout request.
     *
     * Logs the current user out.
     * This route is "public access".
     * Request type: POST     
     */      
    public function logout(){
        session_destroy();
        $this->_app->redirect($this->_app->site->uri['public']);
    }

    /**
     * Processes an new account registration request.
     *
     * Processes the request from the form on the registration page, checking that:
     * 1. The honeypot was not modified;
     * 2. The master account has already been created (during installation);
     * 3. Account registration is enabled;
     * 4. The user is not already logged in;
     * 5. Valid information was entered;
     * 6. The captcha, if enabled, is correct;
     * 7. The username and email are not already taken.
     * Automatically sends an activation link upon success, if account activation is enabled.
     * This route is "public access".
     * Request type: POST     
     */      
    public function register(){
        // POST: user_name, display_name, email, title, password, passwordc, captcha, spiderbro, csrf_token
        $post = $this->_app->request->post();
        
        // Get the alert message stream
        $ms = $this->_app->alerts; 
        
        // Check the honeypot. 'spiderbro' is not a real field, it is hidden on the main page and must be submitted with its default value for this to be processed.
        if (!$post['spiderbro'] || $post['spiderbro'] != "http://"){
            error_log("Possible spam received:" . print_r($this->_app->request->post(), true));
            $ms->addMessage("danger", "Aww hellllls no!");
            $this->_app->halt(500);     // Don't let on about why the request failed ;-)
        }  
               
        // Load the request schema
        $requestSchema = new \Fortress\RequestSchema($this->_app->config('schema.path') . "/forms/register.json");
                   
        // Set up Fortress to process the request
        $rf = new \Fortress\HTTPRequestFortress($ms, $requestSchema, $post);        

        // Security measure: do not allow registering new users until the master account has been created.        
        if (!UserLoader::exists($this->_app->config('user_id_master'))){
            $ms->addMessageTranslated("danger", "MASTER_ACCOUNT_NOT_EXISTS");
            $this->_app->halt(403);
        }
          
        // Check if registration is currently enabled
        if (!$this->_app->site->can_register){
            $ms->addMessageTranslated("danger", "ACCOUNT_REGISTRATION_DISABLED");
            $this->_app->halt(403);
        }
          
        // Prevent the user from registering if he/she is already logged in
        if(!$this->_app->user->isGuest()) {
            $ms->addMessageTranslated("danger", "ACCOUNT_REGISTRATION_LOGOUT");
            $this->_app->halt(200);
        }
                
        // Sanitize data
        $rf->sanitize();
                
        // Validate, and halt on validation errors.
        $error = !$rf->validate(true);
        
        // Get the filtered data
        $data = $rf->data();        
        
        // Check captcha, if required
        if ($this->_app->site->enable_captcha == "1"){
            if (!$data['captcha'] || md5($data['captcha']) != $_SESSION['userfrosting']['captcha']){
                $ms->addMessageTranslated("danger", "CAPTCHA_FAIL");
                $error = true;
            }
        }
        
        // Remove captcha, password confirmation from object data
        $rf->removeFields(['captcha', 'passwordc']);
        
        // Perform desired data transformations.  Is this a feature we could add to Fortress?
        $data['user_name'] = strtolower(trim($data['user_name']));
        $data['display_name'] = trim($data['display_name']);
        $data['email'] = strtolower(trim($data['email']));
        $data['locale'] = $this->_app->site->default_locale;
        
        if ($this->_app->site->require_activation)
            $data['active'] = 0;
        else
            $data['active'] = 1;
        
        // Check if username or email already exists
        if (UserLoader::exists($data['user_name'], 'user_name')){
            $ms->addMessageTranslated("danger", "ACCOUNT_USERNAME_IN_USE", $data);
            $error = true;
        }

        if (UserLoader::exists($data['email'], 'email')){
            $ms->addMessageTranslated("danger", "ACCOUNT_EMAIL_IN_USE", $data);
            $error = true;
        }
        
        // Halt on any validation errors
        if ($error) {
            $this->_app->halt(400);
        }
    
        // Get default primary group (is_default = GROUP_DEFAULT_PRIMARY)
        $primaryGroup = GroupLoader::fetch(GROUP_DEFAULT_PRIMARY, "is_default");
        $data['primary_group_id'] = $primaryGroup->id;
        // Set default title for new users
        $data['title'] = $primaryGroup->new_user_title;
        // Hash password
        $data['password'] = Authentication::hashPassword($data['password']);
        
        // Create the user
        $user = new User($data);

        // Add user to default groups, including default primary group
        $defaultGroups = GroupLoader::fetchAll(GROUP_DEFAULT, "is_default");
        $user->addGroup($primaryGroup->id);
        foreach ($defaultGroups as $group_id => $group)
            $user->addGroup($group_id);    
        
        // Store new user to database
        $user->store();
        if ($this->_app->site->require_activation) {
            // Create and send activation email

            $mail = new \PHPMailer;
            
            $mail->From = $this->_app->site->admin_email;
            $mail->FromName = $this->_app->site->site_title;
            $mail->addAddress($user->email);     // Add a recipient
            $mail->addReplyTo($this->_app->site->admin_email, $this->_app->site->site_title);
            
            $mail->Subject = $this->_app->site->site_title . " - please activate your account";
            $mail->Body    = $this->_app->view()->render("common/mail/activate-new.html", [
                "user" => $user
            ]);
            
            $mail->isHTML(true);                                  // Set email format to HTML
            
            if(!$mail->send()) {
                $ms->addMessageTranslated("danger", "MAIL_ERROR");
                error_log('Mailer Error: ' . $mail->ErrorInfo);
                $this->_app->halt(500);
            }

            // Activation required
            $ms->addMessageTranslated("success", "ACCOUNT_REGISTRATION_COMPLETE_TYPE2");
        } else
            // No activation required
            $ms->addMessageTranslated("success", "ACCOUNT_REGISTRATION_COMPLETE_TYPE1");
        
    }
    
    /**
     * Processes an new account activation request.
     *
     * Processes the request from the account activation link that was emailed to the user, checking that:
     * 1. The token provided matches a user in the database;
     * 2. The user account is not already active;
     * This route is "public access".
     * Request type: GET     
     */          
    public function activate(){
        $data = $this->_app->request->get();
        
        // Load the request schema
        $requestSchema = new \Fortress\RequestSchema($this->_app->config('schema.path') . "/forms/account-activate.json");
        
        // Get the alert message stream
        $ms = $this->_app->alerts; 
        
        // Set up Fortress to validate the request
        $rf = new \Fortress\HTTPRequestFortress($ms, $requestSchema, $data);
        
        // Validate
        if (!$rf->validate()) {
            $this->_app->redirect($this->_app->urlFor('uri_home'));
        }    
        
        // Ok, try to find a user with the specified activation token
        $user = UserLoader::fetch($data['activation_token'], 'activation_token');
        
        if (!$user || $user->active == "1"){
            $ms->addMessageTranslated("danger", "ACCOUNT_TOKEN_NOT_FOUND");
            $this->_app->redirect($this->_app->urlFor('uri_home'));
        }
        
        $user->active = "1";
        $user->store();
        $ms->addMessageTranslated("success", "ACCOUNT_ACTIVATION_COMPLETE");
        
        // Forward to login page
        $this->_app->redirect($this->_app->urlFor('uri_home'));
    }
    
    /**
     * Processes a request to email a forgotten password reset link to the user.
     *
     * Processes the request from the form on the "forgot password" page, checking that:
     * 1. The provided username exists;
     * 2. The provided email address matches the username;
     * 3. The user doesn't already have an outstanding password reset request;
     * 4. The submitted data is valid.
     * This route is "public access".
     * Request type: POST     
     */         
    public function forgotPassword(){
        $data = $this->_app->request->post();
        
        // Load the request schema
        $requestSchema = new \Fortress\RequestSchema($this->_app->config('schema.path') . "/forms/forgot-password.json");
        
        // Get the alert message stream
        $ms = $this->_app->alerts; 
        
        // Set up Fortress to validate the request
        $rf = new \Fortress\HTTPRequestFortress($ms, $requestSchema, $data);
        
        // Validate
        if (!$rf->validate()) {
            $this->_app->halt(400);
        }    
        
        // Check that the username exists
        if(!UserLoader::exists($data['user_name'], 'user_name')) {
            $ms->addMessageTranslated("danger", "ACCOUNT_INVALID_USERNAME");
            $this->_app->halt(400);
        }
        
        // Load the user, by username
        $user = UserLoader::fetch($data['user_name'], 'user_name');
        
        // Check that the specified email is correct
        if ($user->email != $data['email']){
            $ms->addMessageTranslated("danger", "ACCOUNT_USER_OR_EMAIL_INVALID");
            $this->_app->halt(400);
        }
        
        // Check if the user has any outstanding lost password requests
        if($user->lost_password_request == 1) {
            $ms->addMessageTranslated("danger", "FORGOTPASS_REQUEST_EXISTS");
            $this->_app->halt(403);            
        }
        
        // Generate a new activation token.  This will also be used as the password reset token.
        $user->activation_token = UserLoader::generateActivationToken();
        $user->last_activation_request = date("Y-m-d H:i:s");
        $user->lost_password_request = "1";
        $user->lost_password_timestamp = date("Y-m-d H:i:s");
        
        // Email the user asking to confirm this change password request
        $mail = new \PHPMailer;
        
        $mail->From = $this->_app->site->admin_email;
        $mail->FromName = $this->_app->site->site_title;
        $mail->addAddress($user->email);     // Add a recipient
        $mail->addReplyTo($this->_app->site->admin_email, $this->_app->site->site_title);
        
        $mail->Subject = $this->_app->site->site_title . " - reset your password";
        $mail->Body    = $this->_app->view()->render("common/mail/password-reset.html", [
            "user" => $user,
            "request_date" => date("Y-m-d H:i:s")
        ]);
        
        $mail->isHTML(true);                                  // Set email format to HTML
        
        if(!$mail->send()) {
            $ms->addMessageTranslated("danger", "MAIL_ERROR");
            error_log('Mailer Error: ' . $mail->ErrorInfo);
            $this->_app->halt(500);
        }

        $user->store();
        $ms->addMessageTranslated("success", "FORGOTPASS_REQUEST_SUCCESS");
    }
    
    /**
     * Processes a request to reset a user's password.
     *
     * Processes the request from the password reset form, which should have the reset token embedded in it, checking that:
     * 1. The provided activation token is associated with an existing user account;
     * 2. The provided username matches the activation token;
     * 3. The user has a lost password request in progress;
     * 4. The token has not expired;
     * 5. The submitted data (new password) is valid.
     * This route is "public access".
     * Request type: POST     
     */       
    public function resetPassword(){
        $data = $this->_app->request->post();
        
        // Load the request schema
        $requestSchema = new \Fortress\RequestSchema($this->_app->config('schema.path') . "/forms/reset-password.json");
        
        // Get the alert message stream
        $ms = $this->_app->alerts; 
        
        // Set up Fortress to validate the request
        $rf = new \Fortress\HTTPRequestFortress($ms, $requestSchema, $data);
        
        // Validate
        if (!$rf->validate()) {
            $this->_app->halt(400);
        }
        
        // Fetch the user, by looking up the submitted activation token
        $user = UserLoader::fetch($data['activation_token'], 'activation_token');
        
        if (!$user){
            $ms->addMessageTranslated("danger", "FORGOTPASS_INVALID_TOKEN");
            $this->_app->halt(400);
        }
        
        // Check that the username matches the activation token
        if ($user->user_name != trim(strtolower($data['user_name']))){
            $ms->addMessageTranslated("danger", "ACCOUNT_INVALID_USERNAME");
            $this->_app->halt(400);
        }
 
        // Check that a lost password request is in progress and has not expired
        if ($user->lost_password_request == 0 || $user->lost_password_timestamp === null){
            $ms->addMessageTranslated("danger", "FORGOTPASS_INVALID_TOKEN");
            $this->_app->halt(400);
        }

        // Check the time to see if the token is still valid based on the timeout value. If not valid make the user restart the password request
        $current_time = new \DateTime("now");
        $last_request_time = new \DateTime($user->lost_password_timestamp);
        $current_token_life = $current_time->getTimestamp() - $last_request_time->getTimestamp();

        if($current_token_life >= $this->_app->site->reset_password_timeout || $current_token_life < 0){
            // Reset the password flag
            // TODO: should we do this here, or just when there is a new reset request?
            $user->lost_password_request = "0";
            $user->store();
            $ms->addMessageTranslated("danger", "FORGOTPASS_OLD_TOKEN");
            $this->_app->halt(400);
        }

        // Reset the password flag
        $user->lost_password_request = "0";
        
        // Hash the user's password and update
        $user->password = Authentication::hashPassword($data['password']);
        
		if (!$user->password){
			$ms->addMessageTranslated("danger", "PASSWORD_HASH_FAILED");
            $this->_app->halt(500);
		}		
		
        // Store the updated info
        $user->store();
        $ms->addMessageTranslated("success", "ACCOUNT_PASSWORD_UPDATED");
    }
    
    /**
     * Processes a request to cancel a password reset request.
     *
     * This is provided so that users can cancel a password reset request, if they made it in error or if it was not initiated by themselves.
     * Processes the request from the password reset link, checking that:
     * 1. The provided activation token is associated with an existing user account.
     * Request type: GET     
     */      
    public function denyResetPassword(){
        $data = $this->_app->request->get();
        
        // Load the request schema
        $requestSchema = new \Fortress\RequestSchema($this->_app->config('schema.path') . "/forms/deny-password.json");
        
        // Get the alert message stream
        $ms = $this->_app->alerts; 
        
        // Set up Fortress to validate the request
        $rf = new \Fortress\HTTPRequestFortress($ms, $requestSchema, $data);
        
        // Validate
        if (!$rf->validate()) {
            $this->_app->redirect($this->_app->urlFor('uri_home'));
        }
        
        // Fetch the user, by looking up the submitted activation token
        $user = UserLoader::fetch($data['activation_token'], 'activation_token');
        
        if (!$user){
            $ms->addMessageTranslated("danger", "FORGOTPASS_INVALID_TOKEN");
            $this->_app->redirect($this->_app->urlFor('uri_home'));
        }
        
        // Reset the password flag
        $user->lost_password_request = "0";	
		
        // Store the updated info
        $user->store();
        $ms->addMessageTranslated("success", "FORGOTPASS_REQUEST_CANNED");
        $this->_app->redirect($this->_app->urlFor('uri_home'));
    }
    
    /**
     * Processes a request to resend the activation email for a new user account.
     *
     * Processes the request from the resend activation email form, checking that:
     * 1. The provided username is associated with an existing user account;
     * 2. The provided email matches the user account;
     * 3. The user account is not already active;
     * 4. A request to resend the activation link wasn't already processed in the last X seconds (specified in site settings)
     * 5. The submitted data is valid.
     * This route is "public access".
     * Request type: POST     
     */         
    public function resendActivation(){
        $data = $this->_app->request->post();
        
        // Load the request schema
        $requestSchema = new \Fortress\RequestSchema($this->_app->config('schema.path') . "/forms/resend-activation.json");
        
        // Get the alert message stream
        $ms = $this->_app->alerts; 
        
        // Set up Fortress to validate the request
        $rf = new \Fortress\HTTPRequestFortress($ms, $requestSchema, $data);
        
        // Validate
        if (!$rf->validate()) {
            $this->_app->halt(400);
        }    
        
        // Check that the username exists
        if(!UserLoader::exists($data['user_name'], 'user_name')) {
            $ms->addMessageTranslated("danger", "ACCOUNT_INVALID_USERNAME");
            $this->_app->halt(400);
        }
        
        // Load the user, by username
        $user = UserLoader::fetch($data['user_name'], 'user_name');
        
        // Check that the specified email is correct
        if ($user->email != $data['email']){
            $ms->addMessageTranslated("danger", "ACCOUNT_USER_OR_EMAIL_INVALID");
            $this->_app->halt(400);
        }
        
        // Check if user's account is already active
        if ($user->active == "1") {
            $ms->addMessageTranslated("danger", "ACCOUNT_ALREADY_ACTIVE");
            $this->_app->halt(400);
        }
        
        // Check the time since the last activation request
        $current_time = new \DateTime("now");
        $last_request_time = new \DateTime($user->last_activation_request);
        $time_since_last_request = $current_time->getTimestamp() - $last_request_time->getTimestamp();

        // If an activation request has been sent too recently, they must wait
        if($time_since_last_request < $this->_app->site->resend_activation_threshold || $time_since_last_request < 0){
            $ms->addMessageTranslated("danger", "ACCOUNT_LINK_ALREADY_SENT", ["resend_activation_threshold" => $this->_app->site->resend_activation_threshold]);
            $this->_app->halt(429); // "Too many requests" code (http://tools.ietf.org/html/rfc6585#section-4)
        }
        
        // We're good to go - create a new activation token and send the email
        $user->activation_token = UserLoader::generateActivationToken();
        $user->last_activation_request = date("Y-m-d H:i:s");
        $user->lost_password_timestamp = date("Y-m-d H:i:s");
        
        // Email the user
        $mail = new \PHPMailer;
        
        $mail->From = $this->_app->site->admin_email;
        $mail->FromName = $this->_app->site->site_title;
        $mail->addAddress($user->email);     // Add a recipient
        $mail->addReplyTo($this->_app->site->admin_email, $this->_app->site->site_title);
        
        $mail->Subject = $this->_app->site->site_title . " - activate your account";
        $mail->Body    = $this->_app->view()->render("common/mail/resend-activation.html", [
            "user" => $user,
            "activation_token" => $user->activation_token
        ]);
        
        $mail->isHTML(true);                                  // Set email format to HTML
        
        if(!$mail->send()) {
            $ms->addMessageTranslated("danger", "MAIL_ERROR");
            error_log('Mailer Error: ' . $mail->ErrorInfo);
            $this->_app->halt(500);
        }

        $user->store();
        $ms->addMessageTranslated("success", "ACCOUNT_NEW_ACTIVATION_SENT");
    }
    
    /**
     * Processes a request to update a user's account information.
     *
     * Processes the request from the user account settings form, checking that:
     * 1. The user correctly input their current password;
     * 2. They have the necessary permissions to update the posted field(s);
     * 3. The submitted data is valid.
     * This route requires authentication.
     * Request type: POST     
     */         
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
    
    /**
     * Generates a new captcha.
     *
     * Wrapper for UserFrosting::generateCaptcha()
     * Request type: GET     
     */        
    public function captcha(){
        echo $this->generateCaptcha();
    }    
}
