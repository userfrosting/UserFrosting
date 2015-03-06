<?php

use \UserFrosting as UF;

// Handles account controller options
class AccountController extends BaseController {

    public function pageHome(){
        
        // 1. Forward the user to their default page if he/she is already logged in.  Middleware layer?
        
        // 2. Render
               
        $this->_app->render('pages/public/home.html', [
            'page' => [
                'author' =>         $this->_app->userfrosting['author'],
                'title' =>          "A secure, modern user management system based on UserCake, jQuery, and Bootstrap.",
                'description' =>    "Main landing page for public access to this website.",
                'schema' =>         $this->_page_schema,
                'active_page' =>    ""
            ]
        ]);
    }
    
    public function pageLogin(){
        $validators = new Fortress\ClientSideValidator($this->_app->config('schema.path') . "/forms/login.json");
        
        $this->_app->render('pages/public/login.html', [
            'page' => [
                'author' =>         $this->_app->userfrosting['author'],
                'title' =>          "Login",
                'description' =>    "Login to your UserFrosting account.",
                'schema' =>         $this->_page_schema,
                'alerts' =>         $this->_app->alerts->getAndClearMessages(),     // Starting to violate the Law of Demeter here...
                'active_page' =>    "account/login",
            ],
            'validators' => $validators->formValidationRulesJson()
        ]);
    }

    public function pageRegister($can_register = false){
        /*
        if (!userIdExists('1')){
            addAlert("danger", lang("MASTER_ACCOUNT_NOT_EXISTS"));
            header("Location: install/wizard_root_user.php");
            exit();
        }
        */        

        $validators = new Fortress\ClientSideValidator($this->_app->config('schema.path') . "/forms/register.json");

        $userfrosting = $this->_app->userfrosting;
        
        // If registration is disabled, send them back to the home page with an error message
        if (!$userfrosting['can_register']){
            $this->_app->alerts->addMessageTranslated("danger", "ACCOUNT_REGISTRATION_DISABLED");
            $this->_app->redirect('login');
        }
    
        $this->_app->render('pages/public/register.html', [
            'page' => [
                'author' =>         $this->_app->userfrosting['author'],
                'title' =>          "Register",
                'description' =>    "Register for a new UserFrosting account.",
                'schema' =>         $this->_page_schema, 
                'active_page' =>    "account/register"                
            ],
            'captcha_image' =>  $this->generateCaptcha(),
            'validators' => $validators->formValidationRulesJson()
        ]);
    }

    public function pageForgotPassword($token = null){
      
        $validators = new Fortress\ClientSideValidator($this->_app->config('schema.path') . "/forms/forgot-password.json");
        
       $this->_app->render('pages/public/forgot-password.html', [
            'page' => [
                'author' =>         $this->_app->userfrosting['author'],
                'title' =>          "Reset Password",
                'description' =>    "Reset your UserFrosting password.",
                'schema' =>         $this->_page_schema,
                'active_page' =>    ""
            ],
            'token' =>          $token,
            'confirm_ajax' =>   $token ? 1 : 0,
            'validators' => $validators->formValidationRulesJson()
        ]);
    }
    
    public function pageResendActivation(){
        $validators = new Fortress\ClientSideValidator($this->_app->config('schema.path') . "/forms/resend-activation.json");
         
        $this->_app->render('pages/public/resend-activation.html', [
            'page' => [
                'author' =>         $this->_app->userfrosting['author'],
                'title' =>          "Resend Activation",
                'description' =>    "Resend the activation email for your new UserFrosting account.",
                'schema' =>         $this->_page_schema,
                'active_page' =>    ""
            ],
            'validators' => $validators->formValidationRulesJson()
        ]);
    }
    
    public function login(){
        // Load the request schema
        $requestSchema = new Fortress\RequestSchema($this->_app->config('schema.path') . "/forms/login.json");
        
        // Get the alert message stream
        $ms = $this->_app->alerts; 
        
        /*
        //Forward the user to their default page if he/she is already logged in
        if(isUserLoggedIn()) {
            $ms->addMessageTranslated("danger", "LOGIN_ALREADY_COMPLETE");
            $rf->raiseFatalError();
        }
        */
        
        $rf = new Fortress\HTTPRequestFortress($ms, $requestSchema, $this->_app->request->post());
        // Remove csrf_token from the request data, if specified
        //$rf->removeFields(['csrf_token']);
        
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
        if ($isEmail && !$email_login){
            $ms->addMessageTranslated("danger", "ACCOUNT_USER_OR_PASS_INVALID");
            $this->_app->halt(403);
        }
        
        // Try to load the user data
        if($isEmail){
            if (emailExists($data['user_name'])){
                $userdetails = fetchUserAuthByEmail($data['user_name']);
            } else {
                $ms->addMessageTranslated("danger", "ACCOUNT_USER_OR_PASS_INVALID");
                $this->_app->halt(403);         
            }
            
        } else {
            if (usernameExists($data['user_name'])){
                $userdetails = fetchUserAuthByUserName($data['user_name']);
            } else {
                $ms->addMessageTranslated("danger", "ACCOUNT_USER_OR_PASS_INVALID");
                $this->_app->halt(403);
            }
        }
        
        // Check that the user's account is activated
        if ($userdetails["active"] == 0) {
            $ms->addMessageTranslated("danger", "ACCOUNT_INACTIVE");
            $this->_app->halt(403);
        }
        
        // Check that the user's account is enabled
        if ($userdetails["enabled"] == 0){
            $ms->addMessageTranslated("danger", "ACCOUNT_DISABLED");
            $this->_app->halt(403);
        }
        
        
        // Validate the password
        if(!passwordVerifyUF($data['password'], $userdetails["password"]))  {
            //Again, we know the password is at fault here, but lets not give away the combination incase of someone bruteforcing
            $ms->addMessageTranslated("danger", "ACCOUNT_USER_OR_PASS_INVALID");
            $this->_app->halt(403);
        }
        
        //Passwords match! we're good to go'
        
        //Construct a new logged in user object
        //Transfer some db data to the session object
        $loggedInUser = new loggedInUser();
        $loggedInUser->email = $userdetails["email"];
        $loggedInUser->user_id = $userdetails["id"];
        $loggedInUser->hash_pw = $userdetails["password"];
        $loggedInUser->title = $userdetails["title"];
        $loggedInUser->displayname = $userdetails["display_name"];
        $loggedInUser->username = $userdetails["user_name"];
        $loggedInUser->alerts = array();
        
        //Update last sign in
        $loggedInUser->updateLastSignIn();
        
        // Update password if we had encountered an outdated hash
        if (getPasswordHashTypeUF($userdetails["password"]) != "modern"){
            // Hash the user's password and update
            $password_hash = passwordHashUF($data['password']);
            if ($password_hash === null){
                error_log("Notice: outdated password hash could not be updated because the new hashing algorithm is not supported.  Are you running PHP >= 5.3.7?");
            } else {
                $loggedInUser->hash_pw = $password_hash;
                updateUserField($loggedInUser->user_id, 'password', $password_hash);
                error_log("Notice: outdated password hash has been automatically updated to modern hashing.");
            }
        }
        
        // Create the user's CSRF token
        $loggedInUser->csrf_token(true);
        
        $_SESSION["userCakeUser"] = $loggedInUser;
        
        $ms->addMessage("success", "Welcome back, " . $loggedInUser->displayname);

        restore_error_handler();
        
        $rf->raiseSuccess();

    }
    
    public function logout(){
        session_destroy();
    }

    /*
    generates a base 64 string to be placed inside the src attribute of an html image tag.
    @blame -r3wt
    */    
    public function generateCaptcha(){
    
        $md5_hash = md5(rand(0,99999));
        $security_code = substr($md5_hash, 25, 5);
        $enc = md5($security_code);
        // Store the generated captcha to the session
        $_SESSION['userfrosting']['captcha'] = $enc;
    
        $width = 150;
        $height = 30;
    
        $image = imagecreatetruecolor(150, 30);
    
        //color pallette
        $white = imagecolorallocate($image, 255, 255, 255);
        $black = imagecolorallocate($image, 0, 0, 0);
        $red = imagecolorallocate($image,255,0,0);
        $yellow = imagecolorallocate($image, 255, 255, 0);
        $dark_grey = imagecolorallocate($image, 64,64,64);
        $blue = imagecolorallocate($image, 0,0,255);
    
        //create white rectangle
        imagefilledrectangle($image,0,0,150,30,$white);
    
        //add some lines
        for($i=0;$i<2;$i++) {
            imageline($image,0,rand()%10,10,rand()%30,$dark_grey);
            imageline($image,0,rand()%30,150,rand()%30,$red);
            imageline($image,0,rand()%30,150,rand()%30,$yellow);
        }
    
        // RandTab color pallette
        $randc[0] = imagecolorallocate($image, 0, 0, 0);
        $randc[1] = imagecolorallocate($image,255,0,0);
        $randc[2] = imagecolorallocate($image, 255, 255, 0);
        $randc[3] = imagecolorallocate($image, 64,64,64);
        $randc[4] = imagecolorallocate($image, 0,0,255);
        
        //add some dots
        for($i=0;$i<1000;$i++) {
            imagesetpixel($image,rand()%200,rand()%50,$randc[rand()%5]);
        }    
        
        //calculate center of text
        $x = ( 150 - 0 - imagefontwidth( 5 ) * strlen( $security_code ) ) / 2 + 0 + 5;
    
        //write string twice
        ImageString($image,5, $x, 7, $security_code, $black);
        ImageString($image,5, $x, 7, $security_code, $black);
        //start ob
        ob_start();
        ImagePng($image);
    
        //get binary image data
        $data = ob_get_clean();
        //return base64
        return 'data:image/png;base64,'.chunk_split(base64_encode($data)); //return the base64 encoded image.
    }

}

?>
