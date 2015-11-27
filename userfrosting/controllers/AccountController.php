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
        $this->_app->render('home.twig');  
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
        $this->_app->jsValidator->setSchema($schema);
        
        $this->_app->render('account/login.twig', [
            'validators' => $this->_app->jsValidator->rules()
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
        if (!User::find($this->_app->config('user_id_master'))){
            $ms->addMessageTranslated("danger", "MASTER_ACCOUNT_NOT_EXISTS");
            $this->_app->redirect($this->_app->urlFor('uri_install'));
        }

        $schema = new \Fortress\RequestSchema($this->_app->config('schema.path') . "/forms/register.json");
        $this->_app->jsValidator->setSchema($schema);       

        $settings = $this->_app->site;
        
        // If registration is disabled, send them back to the login page with an error message
        if (!$settings->can_register){
            $this->_app->alerts->addMessageTranslated("danger", "ACCOUNT_REGISTRATION_DISABLED");
            $this->_app->redirect('login');
        }
    
        $this->_app->render('account/register.twig', [
            'captcha_image' =>  $this->generateCaptcha(),
            'validators' => $this->_app->jsValidator->rules()
        ]);
    }

    /**
     * Render the "forgot password" page.  
     *
     * This creates a simple form to allow users who forgot their password to have a time-limited password reset link emailed to them.
     * By default, this is a "public page" (does not require authentication).
     * Request type: GET
     */      
    public function pageForgotPassword(){
      
        $schema = new \Fortress\RequestSchema($this->_app->config('schema.path') . "/forms/forgot-password.json");
        $this->_app->jsValidator->setSchema($schema);  
        
        $this->_app->render('account/forgot-password.twig', [
            'validators' => $this->_app->jsValidator->rules()
        ]);
    }
    
    /**
     * Render the "set password" page.  
     *
     * If $flag_new_user is set to true, this renders the page where new users who have had accounts created
     * for them by another user, can set their password.  If set to false, this renders the new password page for password reset requests.
     * By default, this is a "public page" (does not require authentication).
     * Request type: GET
     * @param bool $flag_new_user Set to true if this is for a new user who doesn't yet have a password.
     */      
    public function pageSetPassword($flag_new_user = false){
        // Look up the user for the secret token
        $token = $this->_app->request->get()['secret_token'];  
      
        $schema = new \Fortress\RequestSchema($this->_app->config('schema.path') . "/forms/set-password.json");
        $this->_app->jsValidator->setSchema($schema);  
        
        if ($flag_new_user)
            $template = 'account/create-password.twig';
        else
            $template = 'account/reset-password.twig';
        
        $this->_app->render($template, [
            'secret_token' => $token,
            'validators' => $this->_app->jsValidator->rules()
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
        $this->_app->jsValidator->setSchema($schema);         
                 
        $this->_app->render('account/resend-activation.twig', [
            'validators' => $this->_app->jsValidator->rules()
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
        $this->_app->jsValidator->setSchema($schema);         
        
        $this->_app->render('account/account-settings.twig', [
            "locales" => $this->_app->site->getLocales(),
            "validators" => $this->_app->jsValidator->rules()
        ]);          
    }    
    
    /**
     * Account compromised page.
     *
     * Warns the user that their account may have been compromised due to a stolen "remember me" cookie.
     * This page is "public access".
     * Request type: GET     
     */   
    public function pageAccountCompromised(){
        $this->_app->render('errors/compromised.twig');
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
            $user = User::where('email', $data['user_name'])->first();
            if (!$user){
                $ms->addMessageTranslated("danger", "ACCOUNT_USER_OR_PASS_INVALID");
                $this->_app->halt(403);         
            }
        // Load user by user name    
        } else {
            $user = User::where('user_name', $data['user_name'])->first();
            if (!$user) {
                $ms->addMessageTranslated("danger", "ACCOUNT_USER_OR_PASS_INVALID");
                $this->_app->halt(403);
            }
        }
        
        // Check that the user has a password set (so, rule out newly created accounts without a password)
        if (!$user->password) {
            $ms->addMessageTranslated("danger", "ACCOUNT_USER_OR_PASS_INVALID");
            $this->_app->halt(403);
        }
        
        // Check that the user's account is enabled
        if ($user->flag_enabled == 0){
            $ms->addMessageTranslated("danger", "ACCOUNT_DISABLED");
            $this->_app->halt(403);
        }        
        
        // Check that the user's account is activated
        if ($user->flag_verified == 0) {
            $ms->addMessageTranslated("danger", "ACCOUNT_INACTIVE");
            $this->_app->halt(403);
        }
        
        // Here is my password.  May I please assume the identify of this user now?
        if ($user->verifyPassword($data['password']))  {
            $this->_app->login($user, !empty($data['rememberme']));       
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
     * Logs out the currently logged in user.
     * This route is "public access".
     * Request type: POST     
     */      
    public function logout($complete = false){
        $this->_app->logout($complete);    
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
        if (!User::find($this->_app->config('user_id_master'))){
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
        $data['display_name'] = trim($data['display_name']);
        $data['locale'] = $this->_app->site->default_locale;
        
        if ($this->_app->site->require_activation)
            $data['flag_verified'] = 0;
        else
            $data['flag_verified'] = 1;
        
        // Check if username or email already exists
        if (User::where('user_name', $data['user_name'])->first()){
            $ms->addMessageTranslated("danger", "ACCOUNT_USERNAME_IN_USE", $data);
            $error = true;
        }

        if (User::where('email', $data['email'])->first()){
            $ms->addMessageTranslated("danger", "ACCOUNT_EMAIL_IN_USE", $data);
            $error = true;
        }
        
        // Halt on any validation errors
        if ($error) {
            $this->_app->halt(400);
        }
    
        // Get default primary group (is_default = GROUP_DEFAULT_PRIMARY)
        $primaryGroup = Group::where('is_default', GROUP_DEFAULT_PRIMARY)->first();
        
        // Check that a default primary group is actually set
        if (!$primaryGroup){
            $ms->addMessageTranslated("danger", "ACCOUNT_REGISTRATION_BROKEN");
            error_log("Account registration is not working because a default primary group has not been set.");
            $this->_app->halt(500);
        }
        
        $data['primary_group_id'] = $primaryGroup->id;
        // Set default title for new users
        $data['title'] = $primaryGroup->new_user_title;
        // Hash password
        $data['password'] = Authentication::hashPassword($data['password']);
        
        // Create the user
        $user = new User($data);

        // Add user to default groups, including default primary group
        $defaultGroups = Group::where('is_default', GROUP_DEFAULT)->get();
        $user->addGroup($primaryGroup->id);
        foreach ($defaultGroups as $group)
            $user->addGroup($group->id);    
        
        // Create sign-up event
        $user->newEventSignUp();
        
        // Store new user to database
        $user->save();
        
        if ($this->_app->site->require_activation) {
            // Create verification request event
            $user->newEventVerificationRequest();
            $user->save();      // Re-save with verification event      
            
            // Create and send verification email
            $twig = $this->_app->view()->getEnvironment();
            $template = $twig->loadTemplate("mail/activate-new.twig");        
            $notification = new Notification($template);
            $notification->fromWebsite();      // Automatically sets sender and reply-to
            $notification->addEmailRecipient($user->email, $user->display_name, [
                "user" => $user
            ]);
            
            try {
                $notification->send();
            } catch (\phpmailerException $e){
                $ms->addMessageTranslated("danger", "MAIL_ERROR");
                error_log('Mailer Error: ' . $e->errorMessage());
                $this->_app->halt(500);
            }
            
            $ms->addMessageTranslated("success", "ACCOUNT_REGISTRATION_COMPLETE_TYPE2");
        } else
            // No activation required
            $ms->addMessageTranslated("success", "ACCOUNT_REGISTRATION_COMPLETE_TYPE1");
    } 
    
    /**
     * Processes an new email verification request.
     *
     * Processes the request from the email verification link that was emailed to the user, checking that:
     * 1. The token provided matches a user in the database;
     * 2. The user account is not already verified;
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
        
        // Ok, try to find an unverified user with the specified secret token
        $user = User::where('secret_token', $data['secret_token'])
                    ->where('flag_verified', '0')->first();
        
        if (!$user){
            $ms->addMessageTranslated("danger", "ACCOUNT_TOKEN_NOT_FOUND");
            $this->_app->redirect($this->_app->urlFor('uri_home'));
        }
        
        $user->flag_verified = "1";
        $user->store();
        $ms->addMessageTranslated("success", "ACCOUNT_ACTIVATION_COMPLETE");
        
        // Forward to login page
        $this->_app->redirect($this->_app->urlFor('uri_home'));
    }
    
    /**
     * Processes a request to email a forgotten password reset link to the user.
     *
     * Processes the request from the form on the "forgot password" page, checking that:
     * 1. The provided email address belongs to a registered account;
     * 2. The submitted data is valid.
     * Note that we have removed the requirement that a password reset request not already be in progress.
     * This is because we need to allow users to re-request a reset, even if they lose the first reset email.
     * This route is "public access".
     * Request type: POST
     * @todo rate-limit forgotten password requests, to prevent password-reset spamming
     * @todo require additional user information
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
        
        // Load the user, by the specified email address
        $user = User::where('email', $data['email'])->first();
        
        // Check that the email exists.
        // On failure, we should still pretend like we succeeded, to prevent account enumeration
        if(!$user) {
            $ms->addMessageTranslated("success", "FORGOTPASS_REQUEST_SUCCESS");
            $this->_app->halt(200);
        }
        
        // TODO: rate-limit the number of password reset requests for a given user
        
        // Generate a new password reset request.  This will also generate a new secret token for the user.
        $user->newEventPasswordReset();
        
        // Email the user asking to confirm this change password request
        $twig = $this->_app->view()->getEnvironment();
        $template = $twig->loadTemplate("mail/password-reset.twig");        
        $notification = new Notification($template);
        $notification->fromWebsite();      // Automatically sets sender and reply-to
        $notification->addEmailRecipient($user->email, $user->display_name, [
            "user" => $user,
            "request_date" => date("Y-m-d H:i:s")
        ]);
        
        try {
            $notification->send();
        } catch (\phpmailerException $e){
            $ms->addMessageTranslated("danger", "MAIL_ERROR");
            error_log('Mailer Error: ' . $e->errorMessage());
            $this->_app->halt(500);
        }
        
        $user->save();
        $ms->addMessageTranslated("success", "FORGOTPASS_REQUEST_SUCCESS");
    }
    
    /**
     * Processes a request to reset a user's password, or set the password for a new user.
     *
     * Processes the request from the password create/reset form, which should have the secret token embedded in it, checking that:
     * 1. The provided secret token is associated with an existing user account;
     * 2. The user has a lost password request in progress;
     * 3. The token has not expired;
     * 4. The submitted data (new password) is valid.
     * This route is "public access".
     * Request type: POST     
     */       
    public function setPassword($flag_new_user = false){
        $data = $this->_app->request->post();
        
        // Load the request schema
        $requestSchema = new \Fortress\RequestSchema($this->_app->config('schema.path') . "/forms/set-password.json");
        
        // Get the alert message stream
        $ms = $this->_app->alerts; 
        
        // Set up Fortress to validate the request
        $rf = new \Fortress\HTTPRequestFortress($ms, $requestSchema, $data);
        
        // Validate
        if (!$rf->validate()) {
            $this->_app->halt(400);
        }
        
        // Fetch the user, by looking up the submitted secret token
        $user = User::where('secret_token', $data['secret_token'])->first();
        
        // If no user exists for this token, just say the token is invalid.
        if (!$user){
            $ms->addMessageTranslated("danger", "FORGOTPASS_INVALID_TOKEN");
            $this->_app->halt(400);
        }
        
        // Get the most recent password reset request time
        $last_password_reset_time = $user->lastEventTime('password_reset_request');
        
        // Check that a lost password request is in progress and has not expired
        if ($user->flag_password_reset == 0 || $last_password_reset_time === null){
            $ms->addMessageTranslated("danger", "FORGOTPASS_INVALID_TOKEN");
            $this->_app->halt(400);
        }

        // Check the time to see if the token is still valid based on the timeout value. If not valid, make the user restart the password request
        $current_time = new \DateTime("now");
        $last_password_reset_datetime = new \DateTime($last_password_reset_time);
        $current_token_life = $current_time->getTimestamp() - $last_password_reset_datetime->getTimestamp();
        
        // Compare to appropriate expiration time
        if ($flag_new_user)
            $expiration = $this->_app->site->create_password_expiration;
        else
            $expiration = $this->_app->site->reset_password_timeout;
            
        if($current_token_life >= $expiration|| $current_token_life < 0){
            // Reset the password reset flag so that they'll be able to submit another request
            $user->flag_password_reset = "0";
            $user->store();
            $ms->addMessageTranslated("danger", "FORGOTPASS_OLD_TOKEN");
            $this->_app->halt(400);
        }

        // Reset the password flag
        $user->flag_password_reset = "0";
        
        // Hash the user's new password and update
        $user->password = Authentication::hashPassword($data['password']);
        
		if (!$user->password){
			$ms->addMessageTranslated("danger", "PASSWORD_HASH_FAILED");
            $this->_app->halt(500);
		}		
		
        // Store the updated info
        $user->store();        
        
        // Log out any existing user, and create a new session
        if (!$this->_app->user->isGuest()) {
            $this->_app->logout(true);
            // Restart session
            $this->_app->startSession();
        }
        
        // Auto-login the user      
        $this->_app->login($user);
        
        $ms = $this->_app->alerts; 
        $ms->addMessageTranslated("success", "ACCOUNT_WELCOME", $this->_app->user->export());
        $ms->addMessageTranslated("success", "ACCOUNT_PASSWORD_UPDATED");        
    }
    
    /**
     * Processes a request to cancel a password reset request.
     *
     * This is provided so that users can cancel a password reset request, if they made it in error or if it was not initiated by themselves.
     * Processes the request from the password reset link, checking that:
     * 1. The provided secret token is associated with an existing user account.
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
        
        // Fetch the user with the specified secret token and who has a pending password reset request
        $user = User::where('secret_token', $data['secret_token'])
                    ->where('flag_password_reset', "1")->first();
        
        if (!$user){
            $ms->addMessageTranslated("danger", "FORGOTPASS_INVALID_TOKEN");
            $this->_app->redirect($this->_app->urlFor('uri_home'));
        }
        
        // Reset the password flag
        $user->flag_password_reset = "0";	
		
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
     * @todo Again, just like with password reset - do we really need to get the user's user_name to do this?
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
        
        // Load the user, by username
        $user = User::where('user_name', $data['user_name'])->first();        
        
        // Check that the username exists
        if(!$user) {
            $ms->addMessageTranslated("danger", "ACCOUNT_INVALID_USERNAME");
            $this->_app->halt(400);
        }
        
        // Check that the specified email is correct
        if (strtolower($user->email) != strtolower($data['email'])){
            $ms->addMessageTranslated("danger", "ACCOUNT_USER_OR_EMAIL_INVALID");
            $this->_app->halt(400);
        }
        
        // Check if user's account is already active
        if ($user->flag_verified == "1") {
            $ms->addMessageTranslated("danger", "ACCOUNT_ALREADY_ACTIVE");
            $this->_app->halt(400);
        }
        
        // Get the most recent account verification request time
        $last_verification_request_time = $user->lastEventTime('verification_request');
        $last_verification_request_time = $last_verification_request_time ? $last_verification_request_time : "0000-00-00 00:00:00";
        
        // Check the time since the last activation request
        $current_time = new \DateTime("now");
        $last_verification_request_datetime = new \DateTime($last_verification_request_time);
        $time_since_last_request = $current_time->getTimestamp() - $last_verification_request_datetime->getTimestamp();

        // If an activation request has been sent too recently, they must wait
        if($time_since_last_request < $this->_app->site->resend_activation_threshold || $time_since_last_request < 0){
            $ms->addMessageTranslated("danger", "ACCOUNT_LINK_ALREADY_SENT", ["resend_activation_threshold" => $this->_app->site->resend_activation_threshold]);
            $this->_app->halt(429); // "Too many requests" code (http://tools.ietf.org/html/rfc6585#section-4)
        }
        
        // We're good to go - create a new verification request and send the email
        $user->newEventVerificationRequest();
        
        // Email the user
        $twig = $this->_app->view()->getEnvironment();
        $template = $twig->loadTemplate("mail/resend-activation.twig");        
        $notification = new Notification($template);
        $notification->fromWebsite();      // Automatically sets sender and reply-to
        $notification->addEmailRecipient($user->email, $user->display_name, [
            "user" => $user,
            "secret_token" => $user->secret_token
        ]);
        
        try {
            $notification->send();
        } catch (\phpmailerException $e){
            $ms->addMessageTranslated("danger", "MAIL_ERROR");
            error_log('Mailer Error: ' . $e->errorMessage());
            $this->_app->halt(500);
        }

        $user->save();
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
            if (User::where('email', $data['email'])->first()){
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
        
        // If a new password was specified, hash it.
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
