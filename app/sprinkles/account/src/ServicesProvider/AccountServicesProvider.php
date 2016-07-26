<?php

/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @author    Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */
namespace UserFrosting\Account\ServicesProvider;

use Illuminate\Database\Capsule\Manager as Capsule;
use UserFrosting\Account\Model\User;

/**
 * Registers services for the account sprinkle, such as currentUser, etc
 */
class AccountServicesProvider
{
    /**
     * Register UserFrosting's account services.
     *
     * @param Container $container A DI container implementing ArrayAccess and container-interop.
     */
    public function register($container)
    {
        /**
         * Loads the User object for the currently logged-in user.
         *
         * Tries to re-establish a session for "remember-me" users who have been logged out, or creates a guest user object if no one is logged in.
         * @todo Move some of this logic to the Authenticate class.
         */ 
        $container['currentUser'] = function ($c) {
            $config = $c->get('config');
            $session = $c->get('session');
            
            try {
                $rememberMe = $c->get('rememberMe');
                
                // Determine if we are already logged in (user exists in the session variable)
                if($session->has('user_id') && ($session['user_id'] != null)) {       
                    $currentUser = User::find($session['user_id']);
                    
                    // Load the user.  If they don't exist any more, throw an exception.
                    if (!$currentUser)
                        throw new AccountInvalidException();
                        
                    if (!$currentUser->flag_enabled)
                        throw new AccountDisabledException();
                    
                    // Check, if the Rememberme cookie exists and is still valid.
                    // If not, we log out the current session
                    if(!empty($_COOKIE[$rememberMe->getCookieName()]) && !$rememberMe->cookieIsValid()) {
                        $rememberMe->clearCookie();
                        throw new AuthExpiredException();
                    }
                // If not, try to login via RememberMe cookie
                } else {
                    // Get the user id. If we can present the correct tokens from the cookie, log the user in
                    $user_id = $rememberMe->login();
                    
                    if($user_id) {
                        // Load the user
                        return User::find($user_id);
                        // Update in session
                        $session['user_id'] = $user_id;
                        // There is a chance that an attacker has stolen the login token, so we store
                        // the fact that the user was logged in via RememberMe (instead of login form)
                        $session['remembered_by_cookie'] = true;
                    } else {
                        // If $rememberMe->login() returned false, check if the token was invalid.  This means the cookie was stolen.
                        if($rememberMe->loginTokenWasInvalid()) {
                            throw new AuthCompromisedException();
                        }
                    }
                }
                // Now we have an authenticated user, setup their environment
                
                // TODO: setup user locale in translator
                
                // TODO: Add user to Twig globals
                /*
                $twig->addGlobal("user", $this->user);
                */
                
                /*
                // TODO: Set user theme in Twig
                // Set path to user's theme, prioritizing over any other themes.
                $loader = $twig->getLoader();
                $loader->prependPath($this->config('themes.path') . "/" . $this->user->getTheme());
                */
            } catch (\PDOException $e){
                // If we can't connect to the DB, then we can't create an authenticated user.  That's ok if we're in installation mode.
                return null;
            }
        };
        
        $container['rememberMe'] = function ($c) {
            $config = $c->get('config');
            $session = $c->get('session');        
            
            // Initialize RememberMe
            $storage = new \Birke\Rememberme\Storage\PDO($config['remember_me_table']);
            $storage->setConnection(Capsule::connection()->getPdo());
            $rememberMe = new \Birke\Rememberme\Authenticator($storage);
            // Set cookie name
            $rememberMe->setCookieName($config['session.name'] . '-rememberme');
            
            // Change cookie path
            $cookie = $rememberMe->getCookie();
            $cookie->setPath("/");
            $rememberMe->setCookie($cookie);
            
            return $rememberMe;
        };
    }
}
