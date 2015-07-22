<?php

namespace UserFrosting;

class UserSession extends \Slim\Middleware {

    /**
     * Call middleware.
     *
     * @return void
     */
    public function call() {
        // Attach as hook.
        $this->app->hook('slim.before', array($this, 'setup'));

        // Call next middleware.
        $this->next->call();
    }
    
    public function setup(){       
        // Test database connection
        try {          
            $storage = new \Birke\Rememberme\Storage\PDO($this->app->remember_me_table);
            $storage->setConnection(Database::connection());
            $this->app->remember_me = new \Birke\Rememberme\Authenticator($storage);
            
            // First, initialize the PHP session
            session_start();
            // Determine if we are already logged in (user exists in the session variable)
            if(isset($_SESSION["userfrosting"]["user"]) && is_object($_SESSION["userfrosting"]["user"])) {       
                error_log("Current user id is " . $_SESSION["userfrosting"]["user"]->id);
                // Check, if the Rememberme cookie exists and is still valid.
                // If not, we log out the current session
                if(!empty($_COOKIE[$this->app->remember_me->getCookieName()]) && !$this->app->remember_me->cookieIsValid()) {
                    error_log("Logging out");
                    $controller = new AccountController($this->app);
                    $controller->logout(true);
                    exit;
                    //$this->app->redirect($this->app->site->uri['public'] . "/account/logout");
                }
                
                // User is still logged in - refresh the user.  If they don't exist any more, then an exception will be thrown.
                $_SESSION["userfrosting"]["user"] = $_SESSION["userfrosting"]["user"]->fresh();
                $this->app->user = $_SESSION["userfrosting"]["user"];
            // If not, try to login via RememberMe cookie
            } else {
                // If we can present the correct tokens from the cookie, log the user in
                // Get the user id
                $user_id = $this->app->remember_me->login();
                if($user_id) {
                    // Load the user
                    $_SESSION["userfrosting"]["user"] = \UserFrosting\UserLoader::fetch($user_id);
                    $this->app->user = $_SESSION["userfrosting"]["user"];
                    // There is a chance that an attacker has stolen the login token, so we store
                    // the fact that the user was logged in via RememberMe (instead of login form)
                    $_SESSION['remembered_by_cookie'] = true;
                } else {
                    // If $rememberMe returned false, check if the token was invalid
                    if($this->app->remember_me->loginTokenWasInvalid()) {
                        // TODO: redirect to a "cookie was stolen" page
                        $this->app->halt(403);
                        error_log("Cookie was stolen!");
                        //$content = tpl("cookie_was_stolen");
                    } else {
                        // $rememberMe returned false because of invalid/missing Rememberme cookie - create a dummy "guest" user
                        $this->app->user = new User([], $this->app->config('user_id_guest'));
                    }
                }
            }
        } catch (\PDOException $e) {
            // If the database doesn't exist yet, then use a guest user
            error_log("Error in " . $e->getFile() . " on line " . $e->getLine() . ": " . $e->getMessage());
            error_log($e->getTraceAsString());
            $this->app->user = new User([], $this->app->config('user_id_guest'));
            // In case the error is because someone is trying to reinstall with new db info while still logged in, log them out
            session_destroy();
            $controller = new BaseController($this->app);
            $controller->pageDatabaseError();
            exit;
        }        
        
        $this->app->setupMessageEnvironment();
        $this->app->setupTwig();
    }
}
