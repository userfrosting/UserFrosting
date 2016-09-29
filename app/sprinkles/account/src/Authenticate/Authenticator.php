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
use UserFrosting\Sprinkle\Core\Util\ClassMapper;

/**
 * Handles authentication tasks.
 *
 * @author Alex Weissman (https://alexanderweissman.com)
 * @see http://www.userfrosting.com/components/#authentication
 */
class Authenticator
{
    /**
     * @var ClassMapper
     */
    protected $classMapper;

    /**
     * @var Session
     */    
    protected $session;
    
    /**
     * @var Config
     */    
    protected $config;
    
    /**
     * @var RememberMePDO
     */    
    protected $rememberMeStorage;
    
    /**
     * @var RememberMe
     */    
    protected $rememberMe;
    
    /**
     * Create a new Authenticator object.
     *
     * @param ClassMapper $classMapper Maps generic class identifiers to specific class names.
     * @param Session $session The session wrapper object that will store the user's id.
     * @param Config $config Config object that contains authentication settings.
     */
    public function __construct(ClassMapper $classMapper, Session $session, $config)
    {
        $this->classMapper = $classMapper;
        $this->session = $session;
        $this->config = $config;           
        
        // Initialize RememberMe storage
        $this->rememberMeStorage = new RememberMePDO($this->config['remember_me.table']);
        $this->rememberMeStorage->setConnection(Capsule::connection()->getPdo());
        
        // Set up RememberMe
        $this->rememberMe = new RememberMe($this->rememberMeStorage);
        // Set cookie name
        $cookieName = $this->config['session.name'] . '-' . $this->config['remember_me.cookie.name'];
        $this->rememberMe->setCookieName($cookieName);
        
        // Change cookie path
        $this->rememberMe->getCookie()->setPath($this->config['remember_me.session.path']);
        
        // Set expire time, if specified
        if ($this->config->has('remember_me.expire_time') && ($this->config->has('remember_me.expire_time') != null)) {
            $this->rememberMe->setExpireTime($this->config['remember_me.expire_time']);
        }
    }
    
    /**
     * Attempts to authenticate a user based on a supplied identity and password.
     *
     * If successful, the user's id is stored in session.
     */
    public function attempt($identityColumn, $identityValue, $password, $rememberMe = false)
    {
        // Try to load the user, using the specified conditions
        $user = $this->classMapper->staticMethod('user', 'where', $identityColumn, $identityValue)->first();
        
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
        if (Password::verify($password, $user->password)) {
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
     * @param bool $rememberMe Set to true to make this a "persistent session", i.e. one that will re-login even after the session expires.
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
        $key = $this->config['session.keys.current_user_id'];
        $this->session[$key] = $user->id;
        
        // Set auth mode
        $this->session[$this->config['session.keys.auth_mode']] = 'form';
        
        // Set user login events
        $user->onLogin();
    }       
        
    /**
     * Try to get the currently authenticated user from the session.
     */
    public function getSessionUser()
    {        
        // Determine if we are already logged in (user id exists in the session variable)
        $currentUserIdKey = $this->config['session.keys.current_user_id'];
        if($this->session->has($currentUserIdKey) && ($this->session[$currentUserIdKey] != null)) {       
            $currentUserId = $this->session[$currentUserIdKey];
            
            // Check, if the Rememberme cookie exists and is still valid.
            // If not, we log out the current session and throw an exception.
            if(!empty($_COOKIE[$this->rememberMe->getCookieName()]) && !$this->rememberMe->cookieIsValid()) {
                $this->logout();
                throw new AuthExpiredException();
            }
        // If not, try to login via RememberMe cookie
        } else {
            // Get the user id. If we can present the correct tokens from the cookie, remake the session and automatically log the user in
            $currentUserId = $this->rememberMe->login();
            
            if ($currentUserId) {
                // Update in session
                $this->session[$currentUserIdKey] = $currentUserId;
                // There is a chance that an attacker has stolen the login token, so we store
                // the fact that the user was logged in via RememberMe (instead of login form)
                $this->session[$this->config['session.keys.auth_mode']] = 'cookie';
            } else {
                // If $rememberMe->login() returned false, check if the token was invalid.  This means the cookie was stolen.
                if($this->rememberMe->loginTokenWasInvalid()) {
                    throw new AuthCompromisedException();
                }
            }
        }
        
        // If a user id was retrieved from the session or rememberMe storage, try to load the user object from the DB
        if ($currentUserId) {
            $currentUser = $this->classMapper->staticMethod('user', 'find', $currentUserId);
            
            // If the user doesn't exist any more, throw an exception.
            if (!$currentUser)
                throw new AccountInvalidException();
            
            // If the user has been disabled since their last request, throw an exception.
            if (!$currentUser->flag_enabled)
                throw new AccountDisabledException();
        } else {
            return;
        }
        
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
        $currentUserIdKey = $this->config['session.keys.current_user_id'];
        $currentUserId = $this->session[$currentUserIdKey];
        
        // This removes all of the user's persistent logins from the database
        if ($complete) {
            $this->storage->cleanAllTriplets($currentUserId);
        }
        
        // Clear the rememberMe cookie
        if ($this->rememberMe->clearCookie()) {
            //error_log("Cleared cookie");
        }
        
        // Completely destroy the session
        $this->session->destroy();
        
        // Reset the session service
        $this->session = null;
    }
}
