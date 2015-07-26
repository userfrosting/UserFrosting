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
        // Test database connection
        try {          
            error_log("Setting up user session");
            $storage = new \Birke\Rememberme\Storage\PDO($this->app->remember_me_table);
            $storage->setConnection(Database::connection());
            $this->app->remember_me = new \Birke\Rememberme\Authenticator($storage);
            
            // Determine if we are already logged in (user exists in the session variable)
            if(isset($_SESSION["userfrosting"]["user"]) && is_object($_SESSION["userfrosting"]["user"])) {       
                // User is still logged in - refresh the user.  If they don't exist any more, then an exception will be thrown.
                $_SESSION["userfrosting"]["user"] = $_SESSION["userfrosting"]["user"]->fresh();
                $this->app->user = $_SESSION["userfrosting"]["user"];                
                
                //error_log("Current user id is " . $_SESSION["userfrosting"]["user"]->id);
                // Check, if the Rememberme cookie exists and is still valid.
                // If not, we log out the current session
                if(!empty($_COOKIE[$this->app->remember_me->getCookieName()]) && !$this->app->remember_me->cookieIsValid()) {
                    //error_log("Session expired. logging out...");
                    // Change cookie path
                    $cookie = $this->_app->remember_me->getCookie();
                    $cookie->setPath("/");
                    $this->_app->remember_me->setCookie($cookie);                    
                    $this->app->remember_me->clearCookie();
                    throw new AuthExpiredException();
                }
            // If not, try to login via RememberMe cookie
            } else {
                // If we can present the correct tokens from the cookie, log the user in
                // Get the user id
                $name = $this->app->remember_me->getCookieName();
                error_log("Cookie is called $name");
                
                error_log("Trying to log in via cookie: " . print_r($_COOKIE, true));
                $user_id = $this->app->remember_me->login();
                
                // Change cookie path
                $cookie = $this->app->remember_me->getCookie();
                $cookie->setPath("/");
                $this->app->remember_me->setCookie($cookie);
                
                if($user_id) {
                    error_log("Logging in via remember me for $user_id");
                    // Load the user
                    $_SESSION["userfrosting"]["user"] = \UserFrosting\UserLoader::fetch($user_id);
                    $this->app->user = $_SESSION["userfrosting"]["user"];
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
        } catch (\PDOException $e) {
            throw new DatabaseInvalidException($e->getMessage(), $e->getCode(), $e);
        }        
    }
}
