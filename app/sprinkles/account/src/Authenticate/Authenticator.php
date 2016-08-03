<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2013-2016 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */ 
namespace UserFrosting\Sprinkle\Account\Authenticate;

use Illuminate\Database\Capsule\Manager as Capsule;
use Interop\Container\ContainerInterface;
use UserFrosting\Sprinkle\Account\Util\Password;

/**
 * Handles authentication tasks.
 *
 * @author Alex Weissman (https://alexanderweissman.com)
 * @see http://www.userfrosting.com/components/#authentication
 */
class Authenticator
{
    /**
     * @var ContainerInterface The global container object, which holds all your services.
     */
    protected $ci;
    
    /**
     * Create a new Authenticator object.
     *
     * @param ContainerInterface $ci The global container object, which holds all your services.
     */
    public function __construct(ContainerInterface $ci)
    {
        $this->ci = $ci;
    }
    
    /**
     * Attempts to authenticate a user based on a supplied identity and password.
     *
     * If successful, the user's id is stored in session.
     */
    public function attempt($identityColumn, $identityValue, $password, $rememberMe = false)
    {
        // Try to load the user, using the specified conditions
        $user = User::where($identityColumn, $identityValue)->first();
        
        if (!$user) {
            throw new InvalidCredentialsException();
        }
        
        // Check that the user has a password set (so, rule out newly created accounts without a password)
        if (!$user->password) {
            throw new InvalidCredentialsException();
        }

        // Check that the user's account is enabled
        if ($user->flag_enabled == 0) {
            throw new AccountDisabledException();
        }

        // Check that the user's account is activated
        if ($user->flag_verified == 0) {
            throw new AccountNotVerifiedException();
        }

        // Here is my password.  May I please assume the identify of this user now?
        if (Password::verify($user->password, $password)) {
            $this->login($user, $rememberMe);
            return $user;
        } else {
            // We know the password is at fault here (as opposed to the identity), but lets not give away the combination in case of someone bruteforcing
            throw new InvalidCredentialsException();
        }
    }
    
    /**
     * Process an account login request.
     *
     * This method logs in the specified user, allowing the client to assume the user's identity for the duration of the session.
     * @param User $user The user to log in.
     * @param bool $remember Set to true to make this a "persistent session", i.e. one that will re-login even after the session expires.
     */
    public function login($user, $rememberMe = false)
    {
        // Set user login events
        $user->login();
        session_regenerate_id();
        // If the user wants to be remembered, create Rememberme cookie
        // Change cookie path
        $cookie = $this->remember_me->getCookie();
        $cookie->setPath("/");
        $this->remember_me->setCookie($cookie);
        if($remember) {
            //error_log("Creating user cookie for " . $user->id);
            $this->remember_me->createCookie($user->id);
        } else {
            $this->remember_me->clearCookie();
        }            
        // Assume identity
        $_SESSION["userfrosting"]["user_id"] = $user->id;
        
        // Set user in application
        $this->user = $user;       
        
        // Setup logged in user environment
        $this->setupAuthenticatedEnvironment();  
    }    
    
        /**
         * "Remember me" service.
         *
         * Allows UF to recreate a user's session from a "remember me" cookie.
         * @throws PDOException

        $container['rememberMe'] = function ($c) {
            $config = $c->get('config');
            $session = $c->get('session');        
            // Force database connection to boot up
            $c->get('db');            
            
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
         */        
        
    /**
     * Try to get the currently authenticated user from the session.
     */
    public function getSessionUser()
    {
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
        } catch (\PDOException $e){
            // If we can't connect to the DB, then we can't create an authenticated user.
            // That's ok if we're in installation mode. We'll use the guest user instead.
        }
    }
    
    /**
     * Processes an account logout request.
     *
     * Logs the currently authenticated user out, destroying the PHP session and optionally removing persistent sessions
     * @param bool $complete If set to true, will also clear out any persistent sessions.
     */      
    public function logout($complete = false)
    {
        if ($complete){
            $storage = new \Birke\Rememberme\Storage\PDO($this->remember_me_table);
            $storage->setConnection(Capsule::connection()->getPdo());
            $storage->cleanAllTriplets($this->user->id);
        }        
        // Change cookie path
        $cookie = $this->remember_me->getCookie();
        $cookie->setPath("/");
        $this->remember_me->setCookie($cookie);
        
        if ($this->remember_me->clearCookie())
            error_log("Cleared cookie");
            
        session_regenerate_id(true);
        session_destroy();   
    }
}
