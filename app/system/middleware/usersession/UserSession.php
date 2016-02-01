<?php

namespace UserFrosting;

/**
 * User session middleware.
 *
 * Handles creation of the authenticated user session at the beginning of each request.
 * @package UserFrosting
 * @author Alex Weissman
 * @link http://www.userfrosting.com/
 */
class UserSession extends \Slim\Middleware {

    /**
     * Call middleware.
     *
     * @return void
     */
    public function call() {
        // TODO: Register error-handlers?   
        
        // Attach as hook.
        $this->app->hook('slim.before', array($this, 'setup'));

        // Call next middleware.
        $this->next->call();
    }
    
    /**
     * Sets up the session for the currently logged-in user, trying to re-establish a session for "remember-me" users who have been logged out,
     * or creates a guest user object if no one is logged in.
     */     
    public function setup(){       
        try {
            // Initialize RememberMe
            $storage = new \Birke\Rememberme\Storage\PDO($this->app->remember_me_table);
            $storage->setConnection(\Illuminate\Database\Capsule\Manager::connection()->getPdo());
            $this->app->remember_me = new \Birke\Rememberme\Authenticator($storage);
            // Set cookie name
            $this->app->remember_me->setCookieName($this->app->config('session')['name'] . "-rememberme");
            
            // Change cookie path
            $cookie = $this->app->remember_me->getCookie();
            $cookie->setPath("/");
            $this->app->remember_me->setCookie($cookie);             
            
            // Determine if we are already logged in (user exists in the session variable)
            if(isset($_SESSION["userfrosting"]["user_id"]) && ($_SESSION["userfrosting"]["user_id"] != null)) {       
                
                // Load the user.  If they don't exist any more, throw an exception.
                if (!($this->app->user = User::find($_SESSION["userfrosting"]["user_id"])))
                    throw new AccountInvalidException();
                
                //error_log("Current user id is " . $this->app->user->id);
                
                // Check, if the Rememberme cookie exists and is still valid.
                // If not, we log out the current session
                if(!empty($_COOKIE[$this->app->remember_me->getCookieName()]) && !$this->app->remember_me->cookieIsValid()) {
                    //error_log("Session expired. logging out...");
                    $this->app->remember_me->clearCookie();
                    throw new AuthExpiredException();
                }
            // If not, try to login via RememberMe cookie
            } else {
                // Get the user id. If we can present the correct tokens from the cookie, log the user in
                $user_id = $this->app->remember_me->login();               
                if($user_id) {
                    //error_log("Logging in via remember me for $user_id");
                    // Load the user
                    $this->app->user = User::find($user_id);
                    // Update in session
                    $_SESSION["userfrosting"]["user_id"] = $user_id;
                    // There is a chance that an attacker has stolen the login token, so we store
                    // the fact that the user was logged in via RememberMe (instead of login form)
                    $_SESSION['remembered_by_cookie'] = true;
                } else {
                    // If $remember_me->login() returned false, check if the token was invalid.  This means the cookie was stolen.
                    if($this->app->remember_me->loginTokenWasInvalid()) {
                        //error_log("Cookie was stolen!");
                        throw new AuthCompromisedException();
                    }
                }
            }
            // Now we have an authenticated user, setup their environment
            $this->app->setupAuthenticatedEnvironment();
        } catch (\PDOException $e){
            // If we can't connect to the DB, then we can't create an authenticated user.  That's ok if we're in installation mode.
            error_log("Unable to authenticate user because the database is not yet initialized, invalid, or inaccessible.  Falling back to guest user.");
            error_log($e->getTraceAsString());
        }
    }
}
