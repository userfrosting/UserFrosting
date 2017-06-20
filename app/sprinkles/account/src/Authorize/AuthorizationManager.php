<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */
namespace UserFrosting\Sprinkle\Account\Authorize;

use Interop\Container\ContainerInterface;
use UserFrosting\Sprinkle\Account\Database\Models\User;

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
    public function __construct(ContainerInterface $ci, array $callbacks = [])
    {
        $this->ci = $ci;
        $this->callbacks = $callbacks;
    }

    /**
     * Register an authorization callback, which can then be used in permission conditions.
     *
     * To add additional callbacks, simply extend the `authorizer` service in your Sprinkle's service provider.
     * @param string $name
     * @param callable $callback
     */
    public function addCallback($name, $callback)
    {
        $this->callbacks[$name] = $callback;
        return $this;
    }

    /**
     * Get all authorization callbacks.
     *
     * @return callable[]
     */
    public function getCallbacks()
    {
        return $this->callbacks;
    }

    /**
     * Checks whether or not a user has access on a particular permission slug.
     *
     * Determine if this user has access to the given $slug under the given $params.
     *
     * @param UserFrosting\Sprinkle\Account\Database\Models\User $user
     * @param string $slug The permission slug to check for access.
     * @param array $params[optional] An array of field names => values, specifying any additional data to provide the authorization module
     * when determining whether or not this user has access.
     * @return boolean True if the user has access, false otherwise.
     */
    public function checkAccess(User $user, $slug, array $params = [])
    {
        $debug = $this->ci->config['debug.auth'];

        if ($debug) {
            $trace = array_slice(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3), 1);
            $this->ci->authLogger->debug("Authorization check requested at: ", $trace);
            $this->ci->authLogger->debug("Checking authorization for user {$user->id} ('{$user->user_name}') on permission '$slug'...");
        }

        if ($this->ci->authenticator->guest()) {
            if ($debug) {
                $this->ci->authLogger->debug("User is not logged in.  Access denied.");
            }
            return false;
        }

        // The master (root) account has access to everything.
        // Need to use loose comparison for now, because some DBs return `id` as a string.

        if ($user->id == $this->ci->config['reserved_user_ids.master']) {
            if ($debug) {
                $this->ci->authLogger->debug("User is the master (root) user.  Access granted.");
            }
            return true;
        }

        // Find all permissions that apply to this user (via roles), and check if any evaluate to true.
        $permissions = $user->getCachedPermissions();

        if (empty($permissions) || !isset($permissions[$slug])) {
            if ($debug) {
                $this->ci->authLogger->debug("No matching permissions found.  Access denied.");
            }
            return false;
        }

        $permissions = $permissions[$slug];

        if ($debug) {
            $this->ci->authLogger->debug("Found matching permissions: \n" . print_r($this->getPermissionsArrayDebugInfo($permissions), true));
        }

        $nodeVisitor = new ParserNodeFunctionEvaluator($this->callbacks, $this->ci->authLogger, $debug);
        $ace = new AccessConditionExpression($nodeVisitor, $user, $this->ci->authLogger, $debug);

        foreach ($permissions as $permission) {
            $pass = $ace->evaluateCondition($permission->conditions, $params);
            if ($pass) {
                if ($debug) {
                    $this->ci->authLogger->debug("User passed conditions '{$permission->conditions}' .  Access granted.");
                }
                return true;
            }
        }

        if ($debug) {
            $this->ci->authLogger->debug("User failed to pass any of the matched permissions.  Access denied.");
        }

        return false;
    }

    /**
     * Remove extraneous information from the permission to reduce verbosity.
     *
     * @param  array
     * @return array
     */
    protected function getPermissionsArrayDebugInfo($permissions)
    {
        $permissionsInfo = [];
        foreach ($permissions as $permission) {
            $permissionData = array_only($permission->toArray(), ['id', 'slug', 'name', 'conditions', 'description']);
            $permissionData['roles_via'] = $permission->roles_via->pluck('id')->all();
            $permissionsInfo[] = $permissionData;
        }

        return $permissionsInfo;
    }
}
