<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2013-2016 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */ 
namespace UserFrosting\Sprinkle\Account\Authenticate;

use Birke\Rememberme\Authenticator as RememberMe;
use Birke\Rememberme\Storage\PDO as RememberMePDO;
use Illuminate\Database\Capsule\Manager as Capsule;
use Interop\Container\ContainerInterface;
use UserFrosting\Session\Session;
use UserFrosting\Sprinkle\Account\Model\User;
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
    protected $session;
    
    protected $key;
    
    protected $config;
    
    protected $rememberMeStorage;
    
    protected $rememberMe;
    
    /**
     * Create a new Authenticator object.
     *
     */
    public function __construct(Session $session, $key, $config)
    {
        $this->session = $session;
        $this->key = $key;
        $this->config = $config;
            
        // Force database connection to boot up
        $c->get('db');            
        
        // Initialize RememberMe storage
        $this->rememberMeStorage = new RememberMePDO($this->config['remember_me_table']);
        $this->rememberMeStorage->setConnection(Capsule::connection()->getPdo());
        
        // Set up RememberMe
        $this->rememberMe = new RememberMe($this->rememberMeStorage);
        // Set cookie name
        $this->rememberMe->setCookieName($this->config['session.name'] . '-rememberme');
        
        // Change cookie path
        $this->rememberMe->getCookie()->setPath('/');
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
        $this->session->regenerateId(true);
        
        // If the user wants to be remembered, create Rememberme cookie
        if($rememberMe) {
            //error_log("Creating user cookie for " . $user->id);
            $this->rememberMe->createCookie($user->id);
        } else {
            $this->rememberMe->clearCookie();
        }            
        // Assume identity
        $this->session[$this->key] = $user->id;
        
        // Set user login events
        $user->onLogin();
    }       
        
    /**
     * Try to get the currently authenticated user from the session.
     */
    public function getSessionUser()
    {
        $currentUserId = null;
        
        // Determine if we are already logged in (user id exists in the session variable)
        if($this->session->has($this->key) && ($this->session[$this->key] != null)) {       
            $currentUserId = $this->session[$this->key];
            
            // Check, if the Rememberme cookie exists and is still valid.
            // If not, we log out the current session
            if(!empty($_COOKIE[$this->rememberMe->getCookieName()]) && !$this->rememberMe->cookieIsValid()) {
                $this->rememberMe->clearCookie();
                throw new AuthExpiredException();
            }
        // If not, try to login via RememberMe cookie
        } else {
            // Get the user id. If we can present the correct tokens from the cookie, automatically log the user in
            $currentUserId = $this->rememberMe->login();
            
            if($currentUserId) {
                // Update in session
                $this->session[$this->key] = $currentUserId;
                // There is a chance that an attacker has stolen the login token, so we store
                // the fact that the user was logged in via RememberMe (instead of login form)
                $this->session['remembered_by_cookie'] = true;
            } else {
                // If $rememberMe->login() returned false, check if the token was invalid.  This means the cookie was stolen.
                if($rememberMe->loginTokenWasInvalid()) {
                    throw new AuthCompromisedException();
                }
            }
        }
        
        $currentUser = User::find($currentUserId);
        
        // Load the user.  If they don't exist any more, throw an exception.
        if (!$currentUser)
            throw new AccountInvalidException();
            
        if (!$currentUser->flag_enabled)
            throw new AccountDisabledException();
            
        return $currentUser;
    }
    
    /**
     * Processes an account logout request.
     *
     * Logs the currently authenticated user out, destroying the PHP session and clearing the persistent session.
     * This can optionally remove persistent sessions across all browsers/devices, since there can be a "RememberMe" cookie
     * and corresponding database entries in multiple browsers/devices.  See http://jaspan.com/improved_persistent_login_cookie_best_practice.
     *
     * @param bool $complete If set to true, will ensure that the user is logged out from *all* browsers on all devices.
     */      
    public function logout($complete = false)
    {
        $currentUserId = $this->session[$this->key];
        
        // This removes all of the user's persistent logins from the database
        if ($complete) {
            $this->storage->cleanAllTriplets($currentUserId);
        }
        
        // Clear the rememberMe cookie
        if ($this->rememberMe->clearCookie()) {
            error_log("Cleared cookie");
        }
        
        // Completely destroy the session
        $this->session->destroy();
        
        // Reset the session service
        $this->session = null;
    }
}
