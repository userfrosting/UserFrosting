<?php

namespace UserFrosting;

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
    
    public function setup(){       
        try {
            error_log("Setting up user session");
            $storage = new \Birke\Rememberme\Storage\PDO($this->app->remember_me_table);
            $storage->setConnection(\Illuminate\Database\Capsule\Manager::connection()->getPdo());
            $this->app->remember_me = new \Birke\Rememberme\Authenticator($storage);
            
            // Change cookie path
            $cookie = $this->app->remember_me->getCookie();
            $cookie->setPath("/");
            $this->app->remember_me->setCookie($cookie);             
               
            error_log("Current cookies: " . print_r($_COOKIE, true));
            
            // Determine if we are already logged in (user exists in the session variable)
            if(isset($_SESSION["userfrosting"]["user_id"]) && ($_SESSION["userfrosting"]["user_id"] != null)) {       
                
                // User is still logged in - refresh the user.  If they don't exist any more, then an exception will be thrown.
                $this->app->user = User::find($_SESSION["userfrosting"]["user_id"]);                
                
                error_log("Current user id is " . $this->app->user->id);
                
                // Check, if the Rememberme cookie exists and is still valid.
                // If not, we log out the current session
                if(!empty($_COOKIE[$this->app->remember_me->getCookieName()]) && !$this->app->remember_me->cookieIsValid()) {
                    error_log("Session expired. logging out...");
                   
                    $this->app->remember_me->clearCookie();
                    throw new AuthExpiredException();
                }
            // If not, try to login via RememberMe cookie
            } else {
                // If we can present the correct tokens from the cookie, log the user in
                // Get the user id
                $name = $this->app->remember_me->getCookieName();
                $user_id = $this->app->remember_me->login();               
                
                if($user_id) {
                    error_log("Logging in via remember me for $user_id");
                    // Load the user
                    $this->app->user = \UserFrosting\UserLoader::fetch($user_id);
                    // Update in session
                    $_SESSION["userfrosting"]["user_id"] = $user_id;
                    // There is a chance that an attacker has stolen the login token, so we store
                    // the fact that the user was logged in via RememberMe (instead of login form)
                    $_SESSION['remembered_by_cookie'] = true;
                } else {
                    error_log("Cookie not found in db");
                    // If $rememberMe returned false, check if the token was invalid
                    if($this->app->remember_me->loginTokenWasInvalid()) {
                        //error_log("Cookie was stolen!");
                        throw new AuthCompromisedException();
                    } else {
                        // $rememberMe returned false because of invalid/missing Rememberme cookie - create a dummy "guest" user
                        $this->app->user = new User([], $this->app->config('user_id_guest'));
                    }
                }
            }
            // Now we have an authenticated user, setup their environment
            $this->app->setupAuthenticatedEnvironment();
            error_log("Done seting up authenticated environment.");
        } catch (\PDOException $e){
            // If we can't connect to the DB, then we can't create an authenticated user.  That's ok if we're in installation mode.
            error_log("Unable to authenticate user, falling back to guest user.");
            error_log($e->getTraceAsString());
        }
    }
}
