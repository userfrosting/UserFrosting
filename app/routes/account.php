<?php
       
    /**
     * Routes for /account/* URLs.  Handles account-related activities, including login, registration, password recovery, and account settings.
     *
     * @package UserFrosting
     * @author Alex Weissman
     */
    
    use UserFrosting as UF;
    
    global $app;
    
    /*
        TODO: put this into middleware?
            // Forward to installation if not complete
        if (!isset($app->site->install_status) || $app->site->install_status == "pending"){
            $app->redirect($app->urlFor('uri_install'));
        }
     */
       
    /**
     * Generates a new captcha.
     *
     * Wrapper for Utils::generateCaptcha()
     * Request type: GET
     */        
    $app->get('/account/captcha/?', function () use ($app) {   
        $app->response->headers->set("Content-Type", "image/png");
        echo UF\Utils::generateCaptcha();
    });       
       
    /**
     * Render the "forgot password" page.
     *
     * This creates a simple form to allow users who forgot their password to have a time-limited password reset link emailed to them.
     * By default, this is a "public page" (does not require authentication).
     * Request type: GET
     */
    $app->get('/account/forgot-password/?', function () use ($app) {       
        $schema = $app->loadRequestSchema("forms/forgot-password.json");
        $app->jsValidator->setSchema($schema);

        $app->render('account/forgot-password.twig', [
            'validators' => $app->jsValidator->rules()
        ]);
    });   
    
    /**
     * Renders the login page for UserFrosting.
     * By definition, this is a "public page" (does not require authentication).
     * Request type: GET
     */
    $app->get('/account/login/?', function () use ($app) {    
        // Forward to home page if user is already logged in
        if (!$app->user->isGuest()){
            $app->redirect($app->urlFor('uri_home'));
        }

        $schema = $app->loadRequestSchema("forms/login.json");
        $app->jsValidator->setSchema($schema);

        $app->render('account/login.twig', [
            'validators' => $app->jsValidator->rules()
        ]);
    });
    
    /**
     * Processes an account logout request.
     *
     * Logs out the currently logged in user.
     * This route is "public access".
     * Request type: GET
     */
    $app->get('/account/logout/?', function () use ($app) {    
        $app->logout(true);
        $app->redirect($app->site->uri['public']);
    });
    
    /**
     * Attempts to render the account registration page for UserFrosting.
     *
     * This allows new (non-authenticated) users to create a new account for themselves on your website.
     * Request type: GET
     * @param bool $can_register Specify whether registration is enabled.  If registration is disabled, they will be redirected to the login page.
     */  
    $app->get('/account/register/?', function () use ($app) {    
        // Get the alert message stream
        $ms = $app->alerts;

        // Prevent the user from registering if he/she is already logged in
        if(!$app->user->isGuest()) {
            $ms->addMessageTranslated("danger", "ACCOUNT_REGISTRATION_LOGOUT");
            $app->redirect($app->urlFor('uri_home'));
        }

        // Security measure: do not allow registering new users until the master account has been created.
        if (!UF\User::find($app->config('user_id_master'))){
            $ms->addMessageTranslated("danger", "MASTER_ACCOUNT_NOT_EXISTS");
            $app->redirect($app->urlFor('uri_install'));
        }

        $schema = $app->loadRequestSchema("forms/register.json");
        $app->jsValidator->setSchema($schema);

        $settings = $app->site;

        // If registration is disabled, send them back to the login page with an error message
        if (!$settings->can_register){
            $app->alerts->addMessageTranslated("danger", "ACCOUNT_REGISTRATION_DISABLED");
            $app->redirect('login');
        }

        $app->render('account/register.twig', [
            'captcha_image' =>  UF\Utils::generateCaptcha(),
            'validators' => $app->jsValidator->rules()
        ]);
    });
    
    /**
     * Render the "resend account activation link" page.
     *
     * This is a form that allows users who lost their account activation link to have the link resent to their email address.
     * By default, this is a "public page" (does not require authentication).
     * Request type: GET
     */    
    $app->get('/account/resend-activation/?', function () use ($app) {   
        $schema = $app->loadRequestSchema("forms/resend-activation.json");
        $app->jsValidator->setSchema($schema);

        $app->render('account/resend-activation.twig', [
            'validators' => $app->jsValidator->rules()
        ]);
    });

    /**
     * Render the "reset password" page.
     *
     * This renders the new password page for password reset requests.
     * By default, this is a "public page" (does not require authentication).
     * Request type: GET
     */
    $app->get('/account/reset-password/confirm/?', function () use ($app) {
        $get = $app->request->get();
        
        // Look up the user for the secret token
        $token = $get['secret_token'];

        $schema = $app->loadRequestSchema("forms/set-password.json");
        $app->jsValidator->setSchema($schema);

        if ($flag_new_user)
            $template = 'account/create-password.twig';
        else
            $template = 'account/reset-password.twig';

        $app->render($template, [
            'secret_token' => $token,
            'validators' => $app->jsValidator->rules()
        ]);
    });

    /**
     * Processes a request to cancel a password reset request.
     *
     * This is provided so that users can cancel a password reset request, if they made it in error or if it was not initiated by themselves.
     * Processes the request from the password reset link, checking that:
     * 1. The provided secret token is associated with an existing user account.
     * Request type: GET
     */
    $app->get('/account/reset-password/deny/?', function () use ($app) {
        $data = $app->request->get();

        // Load the request schema
        $requestSchema = $app->loadRequestSchema("forms/deny-password.json");

        // Get the alert message stream
        $ms = $app->alerts;

        // Set up Fortress to validate the request
        $rf = new \Fortress\HTTPRequestFortress($ms, $requestSchema, $data);

        // Validate
        if (!$rf->validate()) {
            $app->redirect($app->urlFor('uri_home'));
        }

        // Fetch the user with the specified secret token and who has a pending password reset request
        $user = UF\User::where('secret_token', $data['secret_token'])
                    ->where('flag_password_reset', "1")->first();

        if (!$user){
            $ms->addMessageTranslated("danger", "FORGOTPASS_INVALID_TOKEN");
            $app->redirect($app->urlFor('uri_home'));
        }

        // Reset the password flag
        $user->flag_password_reset = "0";

        // Store the updated info
        $user->store();
        $ms->addMessageTranslated("success", "FORGOTPASS_REQUEST_CANNED");
        $app->redirect($app->urlFor('uri_home'));
    }); 
    
    /**
     * Render the "set password" page.
     *
     * This renders the page where new users who have had accounts created for them by another user, can set their password.  
     * By default, this is a "public page" (does not require authentication).
     * Request type: GET
     */
    $app->get('/account/set-password/?', function () use ($app) {
        // Look up the user for the secret token
        $token = $app->request->get()['secret_token'];
        
        $schema = $app->loadRequestSchema("forms/set-password.json");
        $app->jsValidator->setSchema($schema);
        
        $template = 'account/create-password.twig';
        
        $app->render($template, [
            'secret_token' => $token,
            'validators' => $app->jsValidator->rules()
        ]);
    });    
    
    /**
     * Account settings page.
     *
     * Provides a form for users to modify various properties of their account, such as display name, email, locale, etc.
     * Any fields that the user does not have permission to modify will be automatically disabled.
     * This page requires authentication.
     * Request type: GET
     */
    $app->get('/account/settings/?', function () use ($app) { 
        // Access-controlled page
        if (!$app->user->checkAccess('uri_account_settings')){
            $app->notFound();
        }

        $schema = $app->loadRequestSchema("forms/account-settings.json");
        $app->jsValidator->setSchema($schema);

        $app->render('account/account-settings.twig', [
            "locales" => $app->site->getLocales(),
            "validators" => $app->jsValidator->rules()
        ]);
    });    
    
    /**
     * Processes an new email verification request.
     *
     * Processes the request from the email verification link that was emailed to the user, checking that:
     * 1. The token provided matches a user in the database;
     * 2. The user account is not already verified;
     * This route is "public access".
     * Request type: GET
     */    
    $app->get('/account/verify/?', function () use ($app) {      
        $data = $app->request->get();

        // Load the request schema
        $requestSchema = $app->loadRequestSchema("forms/account-activate.json");

        // Get the alert message stream
        $ms = $app->alerts;

        // Set up Fortress to validate the request
        $rf = new \Fortress\HTTPRequestFortress($ms, $requestSchema, $data);

        // Validate
        if (!$rf->validate()) {
            $app->redirect($app->urlFor('uri_home'));
        }

        // Ok, try to find an unverified user with the specified secret token
        $user = UF\User::where('secret_token', $data['secret_token'])
                    ->where('flag_verified', '0')->first();

        if (!$user){
            $ms->addMessageTranslated("danger", "ACCOUNT_TOKEN_NOT_FOUND");
            $app->redirect($app->urlFor('uri_home'));
        }

        $user->flag_verified = "1";
        $user->store();
        $ms->addMessageTranslated("success", "ACCOUNT_ACTIVATION_COMPLETE");

        // Forward to login page
        $app->redirect($app->urlFor('uri_home'));
    });   
    
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
    $app->post('/account/forgot-password/?', function () use ($app) {  
        $data = $app->request->post();

        // Load the request schema
        $requestSchema = $app->loadRequestSchema("forms/forgot-password.json");

        // Get the alert message stream
        $ms = $app->alerts;

        // Set up Fortress to validate the request
        $rf = new \Fortress\HTTPRequestFortress($ms, $requestSchema, $data);

        // Validate
        if (!$rf->validate()) {
            $app->halt(400);
        }

        // Load the user, by the specified email address
        $user = UF\User::where('email', $data['email'])->first();

        // Check that the email exists.
        // On failure, we should still pretend like we succeeded, to prevent account enumeration
        if(!$user) {
            $ms->addMessageTranslated("success", "FORGOTPASS_REQUEST_SUCCESS");
            $app->halt(200);
        }

        // TODO: rate-limit the number of password reset requests for a given user

        // Generate a new password reset request.  This will also generate a new secret token for the user.
        $user->newEventPasswordReset();

        // Email the user asking to confirm this change password request
        $twig = $app->view()->getEnvironment();
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
            $app->halt(500);
        }

        $user->save();
        $ms->addMessageTranslated("success", "FORGOTPASS_REQUEST_SUCCESS");
    });    
    
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
    $app->post('/account/login/?', function () use ($app) {  
        // Load the request schema
        $requestSchema = $app->loadRequestSchema("forms/login.json");

        // Get the alert message stream
        $ms = $app->alerts;

        // Forward the user to their default page if he/she is already logged in
        if(!$app->user->isGuest()) {
            $ms->addMessageTranslated("warning", "LOGIN_ALREADY_COMPLETE");
            $app->halt(200);
        }

        // Set up Fortress to process the request
        $rf = new \Fortress\HTTPRequestFortress($ms, $requestSchema, $app->request->post());

        // Sanitize data
        $rf->sanitize();

        // Validate, and halt on validation errors.
        if (!$rf->validate(true)) {
            $app->halt(400);
        }

        // Get the filtered data
        $data = $rf->data();

        // Determine whether we are trying to log in with an email address or a username
        $isEmail = filter_var($data['user_name'], FILTER_VALIDATE_EMAIL);

        // If it's an email address, but email login is not enabled, raise an error.
        if ($isEmail && !$app->site->email_login){
            $ms->addMessageTranslated("danger", "ACCOUNT_USER_OR_PASS_INVALID");
            $app->halt(403);
        }

        // Load user by email address
        if($isEmail){
            $user = UF\User::where('email', $data['user_name'])->first();
            if (!$user){
                $ms->addMessageTranslated("danger", "ACCOUNT_USER_OR_PASS_INVALID");
                $app->halt(403);
            }
        // Load user by user name
        } else {
            $user = UF\User::where('user_name', $data['user_name'])->first();
            if (!$user) {
                $ms->addMessageTranslated("danger", "ACCOUNT_USER_OR_PASS_INVALID");
                $app->halt(403);
            }
        }

        // Check that the user has a password set (so, rule out newly created accounts without a password)
        if (!$user->password) {
            $ms->addMessageTranslated("danger", "ACCOUNT_USER_OR_PASS_INVALID");
            $app->halt(403);
        }

        // Check that the user's account is enabled
        if ($user->flag_enabled == 0){
            $ms->addMessageTranslated("danger", "ACCOUNT_DISABLED");
            $app->halt(403);
        }

        // Check that the user's account is activated
        if ($user->flag_verified == 0) {
            $ms->addMessageTranslated("danger", "ACCOUNT_INACTIVE");
            $app->halt(403);
        }

        // Here is my password.  May I please assume the identify of this user now?
        if ($user->verifyPassword($data['password']))  {
            $app->login($user, !empty($data['rememberme']));
            $ms->addMessageTranslated("success", "ACCOUNT_WELCOME", $app->user->export());
        } else {
            //Again, we know the password is at fault here, but lets not give away the combination in case of someone bruteforcing
            $ms->addMessageTranslated("danger", "ACCOUNT_USER_OR_PASS_INVALID");
            $app->halt(403);
        }
    });

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
     * Returns the User Object for the user record that was created.
     */
    $app->post('/account/register/?', function () use ($app) {  
        // POST: user_name, display_name, email, title, password, passwordc, captcha, spiderbro, csrf_token
        $post = $app->request->post();

        // Get the alert message stream
        $ms = $app->alerts;

        // Check the honeypot. 'spiderbro' is not a real field, it is hidden on the main page and must be submitted with its default value for this to be processed.
        if (!$post['spiderbro'] || $post['spiderbro'] != "http://"){
            error_log("Possible spam received:" . print_r($app->request->post(), true));
            $ms->addMessage("danger", "Aww hellllls no!");
            $app->halt(500);     // Don't let on about why the request failed ;-)
        }

        // Load the request schema
        $requestSchema = $app->loadRequestSchema("forms/register.json");

        // Set up Fortress to process the request
        $rf = new \Fortress\HTTPRequestFortress($ms, $requestSchema, $post);

        // Security measure: do not allow registering new users until the master account has been created.
        if (!UF\User::find($app->config('user_id_master'))){
            $ms->addMessageTranslated("danger", "MASTER_ACCOUNT_NOT_EXISTS");
            $app->halt(403);
        }

        // Check if registration is currently enabled
        if (!$app->site->can_register){
            $ms->addMessageTranslated("danger", "ACCOUNT_REGISTRATION_DISABLED");
            $app->halt(403);
        }

        // Prevent the user from registering if he/she is already logged in
        if(!$app->user->isGuest()) {
            $ms->addMessageTranslated("danger", "ACCOUNT_REGISTRATION_LOGOUT");
            $app->halt(200);
        }

        // Sanitize data
        $rf->sanitize();

        // Validate, and halt on validation errors.
        $error = !$rf->validate(true);

        // Get the filtered data
        $data = $rf->data();

        // Check captcha, if required
        if ($app->site->enable_captcha == "1"){
            if (!$data['captcha'] || md5($data['captcha']) != $_SESSION['userfrosting']['captcha']){
                $ms->addMessageTranslated("danger", "CAPTCHA_FAIL");
                $error = true;
            }
        }

        // Remove captcha, password confirmation from object data
        $rf->removeFields(['captcha', 'passwordc']);

        // Perform desired data transformations.  Is this a feature we could add to Fortress?
        $data['display_name'] = trim($data['display_name']);
        $data['locale'] = $app->site->default_locale;

        if ($app->site->require_activation)
            $data['flag_verified'] = 0;
        else
            $data['flag_verified'] = 1;

        // Check if username or email already exists
        if (UF\User::where('user_name', $data['user_name'])->first()){
            $ms->addMessageTranslated("danger", "ACCOUNT_USERNAME_IN_USE", $data);
            $error = true;
        }

        if (UF\User::where('email', $data['email'])->first()){
            $ms->addMessageTranslated("danger", "ACCOUNT_EMAIL_IN_USE", $data);
            $error = true;
        }

        // Halt on any validation errors
        if ($error) {
            $app->halt(400);
        }

        // Get default primary group (is_default = GROUP_DEFAULT_PRIMARY)
        $primaryGroup = Group::where('is_default', GROUP_DEFAULT_PRIMARY)->first();

        // Check that a default primary group is actually set
        if (!$primaryGroup){
            $ms->addMessageTranslated("danger", "ACCOUNT_REGISTRATION_BROKEN");
            error_log("Account registration is not working because a default primary group has not been set.");
            $app->halt(500);
        }

        $data['primary_group_id'] = $primaryGroup->id;
        // Set default title for new users
        $data['title'] = $primaryGroup->new_user_title;
        // Hash password
        $data['password'] = Authentication::hashPassword($data['password']);

        // Create the user
        $user = new UF\User($data);

        // Add user to default groups, including default primary group
        $defaultGroups = UF\Group::where('is_default', GROUP_DEFAULT)->get();
        $user->addGroup($primaryGroup->id);
        foreach ($defaultGroups as $group)
            $user->addGroup($group->id);

        // Create sign-up event
        $user->newEventSignUp();

        // Store new user to database
        $user->save();

        if ($app->site->require_activation) {
            // Create verification request event
            $user->newEventVerificationRequest();
            $user->save();      // Re-save with verification event

            // Create and send verification email
            $twig = $app->view()->getEnvironment();
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
                $app->halt(500);
            }

            $ms->addMessageTranslated("success", "ACCOUNT_REGISTRATION_COMPLETE_TYPE2");
        } else
            // No activation required
            $ms->addMessageTranslated("success", "ACCOUNT_REGISTRATION_COMPLETE_TYPE1");
    });

    /**
     * Processes a request to resend the verification email for a new user account.
     *
     * Processes the request from the resend verification email form, checking that:
     * 1. The provided username is associated with an existing user account;
     * 2. The provided email matches the user account;
     * 3. The user account is not already verified;
     * 4. A request to resend the verification link wasn't already processed in the last X seconds (specified in site settings)
     * 5. The submitted data is valid.
     * This route is "public access".
     * Request type: POST
     * @todo Again, just like with password reset - do we really need to get the user's user_name to do this?
     */
    $app->post('/account/resend-activation/?', function () use ($app) {
        $data = $app->request->post();

        // Load the request schema
        $requestSchema = $app->loadRequestSchema("forms/resend-activation.json");

        // Get the alert message stream
        $ms = $app->alerts;

        // Set up Fortress to validate the request
        $rf = new \Fortress\HTTPRequestFortress($ms, $requestSchema, $data);

        // Validate
        if (!$rf->validate()) {
            $app->halt(400);
        }

        // Load the user, by username
        $user = UF\User::where('user_name', $data['user_name'])->first();

        // Check that the username exists
        if(!$user) {
            $ms->addMessageTranslated("danger", "ACCOUNT_INVALID_USERNAME");
            $app->halt(400);
        }

        // Check that the specified email is correct
        if (strtolower($user->email) != strtolower($data['email'])){
            $ms->addMessageTranslated("danger", "ACCOUNT_USER_OR_EMAIL_INVALID");
            $app->halt(400);
        }

        // Check if user's account is already active
        if ($user->flag_verified == "1") {
            $ms->addMessageTranslated("danger", "ACCOUNT_ALREADY_ACTIVE");
            $app->halt(400);
        }

        // Get the most recent account verification request time
        $last_verification_request_time = $user->lastEventTime('verification_request');
        $last_verification_request_time = $last_verification_request_time ? $last_verification_request_time : "0000-00-00 00:00:00";

        // Check the time since the last activation request
        $current_time = new \DateTime("now");
        $last_verification_request_datetime = new \DateTime($last_verification_request_time);
        $time_since_last_request = $current_time->getTimestamp() - $last_verification_request_datetime->getTimestamp();

        // If an activation request has been sent too recently, they must wait
        if($time_since_last_request < $app->site->resend_activation_threshold || $time_since_last_request < 0){
            $ms->addMessageTranslated("danger", "ACCOUNT_LINK_ALREADY_SENT", ["resend_activation_threshold" => $app->site->resend_activation_threshold]);
            $app->halt(429); // "Too many requests" code (http://tools.ietf.org/html/rfc6585#section-4)
        }

        // We're good to go - create a new verification request and send the email
        $user->newEventVerificationRequest();

        // Email the user
        $twig = $app->view()->getEnvironment();
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
            $app->halt(500);
        }

        $user->save();
        $ms->addMessageTranslated("success", "ACCOUNT_NEW_ACTIVATION_SENT");
    });

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
    $app->post('/account/:modify-password-mode/?', function ($modify_password_mode) use ($app) {     
        $data = $app->request->post();

        // Load the request schema
        $requestSchema = $app->loadRequestSchema("forms/set-password.json");

        // Get the alert message stream
        $ms = $app->alerts;

        // Set up Fortress to validate the request
        $rf = new \Fortress\HTTPRequestFortress($ms, $requestSchema, $data);

        // Validate
        if (!$rf->validate()) {
            $app->halt(400);
        }

        // Fetch the user, by looking up the submitted secret token
        $user = UF\User::where('secret_token', $data['secret_token'])->first();

        // If no user exists for this token, just say the token is invalid.
        if (!$user){
            $ms->addMessageTranslated("danger", "FORGOTPASS_INVALID_TOKEN");
            $app->halt(400);
        }

        // Get the most recent password reset request time
        $last_password_reset_time = $user->lastEventTime('password_reset_request');

        // Check that a lost password request is in progress and has not expired
        if ($user->flag_password_reset == 0 || $last_password_reset_time === null){
            $ms->addMessageTranslated("danger", "FORGOTPASS_INVALID_TOKEN");
            $app->halt(400);
        }

        // Check the time to see if the token is still valid based on the timeout value. If not valid, make the user restart the password request
        $current_time = new \DateTime("now");
        $last_password_reset_datetime = new \DateTime($last_password_reset_time);
        $current_token_life = $current_time->getTimestamp() - $last_password_reset_datetime->getTimestamp();

        // Compare to appropriate expiration time
        if ($modify_password_mode == "create-password")
            $expiration = $app->site->create_password_expiration;
        else
            $expiration = $app->site->reset_password_timeout;

        if($current_token_life >= $expiration|| $current_token_life < 0){
            // Reset the password reset flag so that they'll be able to submit another request
            $user->flag_password_reset = "0";
            $user->store();
            $ms->addMessageTranslated("danger", "FORGOTPASS_OLD_TOKEN");
            $app->halt(400);
        }

        // Reset the password flag
        $user->flag_password_reset = "0";

        // Hash the user's new password and update
        $user->password = Authentication::hashPassword($data['password']);

		if (!$user->password){
			$ms->addMessageTranslated("danger", "PASSWORD_HASH_FAILED");
            $app->halt(500);
		}

        // Store the updated info
        $user->store();

        // Log out any existing user, and create a new session
        if (!$app->user->isGuest()) {
            $app->logout(true);
            // Restart session
            $app->startSession();
        }

        // Auto-login the user
        $app->login($user);

        $ms = $app->alerts;
        $ms->addMessageTranslated("success", "ACCOUNT_WELCOME", $app->user->export());
        $ms->addMessageTranslated("success", "ACCOUNT_PASSWORD_UPDATED");
    })->conditions(array("modify-password-mode" => "(set-password|reset-password)"));
    
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
    $app->post('/account/settings/?', function () use ($app) { 
        // Load the request schema
        $requestSchema = $app->loadRequestSchema("forms/account-settings.json");

        // Get the alert message stream
        $ms = $app->alerts;

        // Access control for entire page
        if (!$app->user->checkAccess('uri_account_settings')){
            $ms->addMessageTranslated("danger", "ACCESS_DENIED");
            $app->halt(403);
        }

        $data = $app->request->post();

        // Remove csrf_token
        unset($data['csrf_token']);

        // Check current password
        if (!isset($data['passwordcheck']) || !$app->user->verifyPassword($data['passwordcheck'])){
            $ms->addMessageTranslated("danger", "ACCOUNT_PASSWORD_INVALID");
            $app->halt(403);
        }

        // Validate new email, if specified
        if (isset($data['email']) && $data['email'] != $app->user->email){
            // Check authorization
            if (!$app->user->checkAccess('update_account_setting', ['user' => $app->user, 'property' => 'email'])){
                $ms->addMessageTranslated("danger", "ACCESS_DENIED");
                $app->halt(403);
            }
            // Check if address is in use
            if (UF\User::where('email', $data['email'])->first()){
                $ms->addMessageTranslated("danger", "ACCOUNT_EMAIL_IN_USE", $data);
                $app->halt(400);
            }
        } else {
            $data['email'] = $app->user->email;
        }

        // Validate locale, if specified
        if (isset($data['locale']) && $data['locale'] != $app->user->locale){
            // Check authorization
            if (!$app->user->checkAccess('update_account_setting', ['user' => $app->user, 'property' => 'locale'])){
                $ms->addMessageTranslated("danger", "ACCESS_DENIED");
                $app->halt(403);
            }
            // Validate locale
            if (!in_array($data['locale'], $app->site->getLocales())){
                $ms->addMessageTranslated("danger", "ACCOUNT_SPECIFY_LOCALE");
                $app->halt(400);
            }
        } else {
            $data['locale'] = $app->user->locale;
        }

        // Validate display_name, if specified
        if (isset($data['display_name']) && $data['display_name'] != $app->user->display_name){
            // Check authorization
            if (!$app->user->checkAccess('update_account_setting', ['user' => $app->user, 'property' => 'display_name'])){
                $ms->addMessageTranslated("danger", "ACCESS_DENIED");
                $app->halt(403);
            }
        } else {
            $data['display_name'] = $app->user->display_name;
        }

        // Validate password, if specified and not empty
        if (isset($data['password']) && !empty($data['password'])){
            // Check authorization
            if (!$app->user->checkAccess('update_account_setting', ['user' => $app->user, 'property' => 'password'])){
                $ms->addMessageTranslated("danger", "ACCESS_DENIED");
                $app->halt(403);
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
            $app->halt(400);
        }

        // If a new password was specified, hash it.
        if (isset($data['password']))
            $data['password'] = Authentication::hashPassword($data['password']);

        // Remove passwordc, passwordcheck
        unset($data['passwordc']);
        unset($data['passwordcheck']);

        // Looks good, let's update with new values!
        foreach ($data as $name => $value){
            $app->user->$name = $value;
        }

        $app->user->store();

        $ms->addMessageTranslated("success", "ACCOUNT_SETTINGS_UPDATED");
    });
    