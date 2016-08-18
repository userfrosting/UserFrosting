<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2013-2016 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */
namespace UserFrosting\Sprinkle\Account\Authorize;

use Interop\Container\ContainerInterface;

/**
 * AuthorizationManager class.
 *
 * Manages a collection of access condition callbacks, and uses them to perform access control checks on user objects.
 * @author Alex Weissman (https://alexanderweissman.com)
 */
class AuthorizationManager
{
    /**
     * @var ContainerInterface The global container object, which holds all your services.
     */
    protected $ci;
    
    /**
     * @var array[callable] An array of callbacks that accept some parameters and evaluate to true or false.
     */
    protected $callbacks = [];
        
    /**
     * Create a new AuthorizationManager object.
     *
     * @param ContainerInterface $ci The global container object, which holds all your services.
     */
    public function __construct(ContainerInterface $ci, $callbacks = [])
    {
        $this->ci = $ci;
        $this->callbacks = $callbacks;
    }
    
    public function addCallback($name, $callback)
    {
        $this->callbacks[$name] = $callback;
    }
    
    /**
     * Checks whether or not a user has access on a particular permission slug.
     *
     * Determine if this user has access to the given $hook under the given $params.
     * @param string $hook The authorization hook to check for access.
     * @param array $params[optional] An array of field names => values, specifying any additional data to provide the authorization module
     * when determining whether or not this user has access.
     * @return boolean True if the user has access, false otherwise.
     */ 
    public function checkAccess($user, $slug, $params = [])
    {
        if ($user->isGuest()) {   // TODO: do we sometimes want to allow access to protected resources for guests?  Should we model a "guest" group?
            return false;
        }
    
        // The master (root) account has access to everything.
        // Need to use loose comparison for now, because some DBs return `id` as a string.
        
        if ($user->id == $this->ci->config['reserved_user_ids.master']) {  
            return true;
        }
        
        $pass = false;
        
        // Find all permissions that apply to this user (via roles), and check if any evaluate to true.
        if (!$pass) {
            $nodeVisitor = new ParserNodeFunctionEvaluator($this->callbacks, $this->ci->config['debug.auth']);
            $ace = new AccessConditionExpression($nodeVisitor, $user, $this->ci->config['debug.auth']);
            
            $permissions = $user->permissions($slug)->get();
            
            if (!empty($permissions)) {
                foreach ($permissions as $permission) {
                    $pass = $ace->evaluateCondition($permission->conditions, $params);
                    if ($pass) {
                        break;
                    }
                }
            }
        }
        
        return $pass;
    }
}
