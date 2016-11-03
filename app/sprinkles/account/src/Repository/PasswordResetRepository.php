<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2013-2016 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */
namespace UserFrosting\Sprinkle\Account\Repository;

use Carbon\Carbon;
use Illuminate\Database\Capsule\Manager as Capsule;
use Interop\Container\ContainerInterface;
use UserFrosting\Sprinkle\Account\Model\User;
use UserFrosting\Sprinkle\Account\Util\Password;
use UserFrosting\Sprinkle\Core\Util\ClassMapper;

class PasswordResetRepository
{

    /**
     * @var ClassMapper
     */
    protected $classMapper;

    /**
     * @var Config
     */    
    protected $config;

    protected $modelIdentifier = 'password_reset';
    
    public function __construct(ClassMapper $classMapper, $config)
    {
        $this->classMapper = $classMapper;
        $this->config = $config;
    }

    public function create(User $user, $type = 'create')
    {
        // Compute expiration time
        if ($type == 'create') {
            $expiresAt = Carbon::now()->addSeconds($this->config['password_reset.timeouts.create']);
        } else if ($type = 'reset') {
            $expiresAt = Carbon::now()->addSeconds($this->config['password_reset.timeouts.reset']);
        } else {
            throw new \InvalidArgumentException("Password reset request must be of type 'create' or 'reset'.");
        }

        $passwordReset = $this->classMapper->createInstance($this->modelIdentifier);

        // Generate a random token
        $passwordReset->setToken($this->generateRandomToken());

        // Hash the password reset token for the stored version
        $hash = hash($this->config['password_reset.algorithm'], $passwordReset->getToken());

        $passwordReset->fill([
            'hash'       => $hash,
            'completed'  => false,
            'expires_at' => $expiresAt
        ]);

        $passwordReset->user_id = $user->id;

        $passwordReset->save();

        return $passwordReset;
    }

    public function exists(User $user, $token = null)
    {
        $passwordReset = $this->classMapper
            ->staticMethod($this->modelIdentifier, 'where', 'user_id', $user->id)
            ->where('completed', false)
            ->where('expires_at', '>', Carbon::now());

        if ($token) {
            // get token hash
            $hash = hash($this->config['password_reset.algorithm'], $token);
            $passwordReset->where('hash', $hash);
        }

        return $passwordReset->first() ?: false;
    }

    public function cancel($token)
    {
        // Hash the password reset token for the stored version
        $hash = hash($this->config['password_reset.algorithm'], $token);

        // Find an incomplete reset request for the specified hash
        $passwordReset = $this->classMapper
            ->staticMethod($this->modelIdentifier, 'where', 'hash', $hash)
            ->where('completed', false)
            ->first();

        if ($passwordReset === null) {
            return false;
        }

        $passwordReset->delete();

        return $passwordReset;
    }

    public function complete($token, $password)
    {
        // Hash the password reset token for the stored version
        $hash = hash($this->config['password_reset.algorithm'], $token);

        // Find an unexpired, incomplete reset request for the specified hash
        $passwordReset = $this->classMapper
            ->staticMethod($this->modelIdentifier, 'where', 'hash', $hash)
            ->where('completed', false)
            ->where('expires_at', '>', Carbon::now())
            ->first();

        if ($passwordReset === null) {
            return false;
        }

        // Fetch user for this token
        $user = $this->classMapper->staticMethod('user', 'where', 'id', $passwordReset->user_id)->first();

        if ($user === null) {
            return false;
        }

        $user->password = Password::hash($password);
        // TODO: generate user activity? or do this in controller?
        $user->save();

        $passwordReset->fill([
            'completed'    => true,
            'completed_at' => Carbon::now()
        ]);

        $passwordReset->save();

        return $passwordReset;
    }

    public function removeExpired()
    {
        return $this->classMapper
            ->staticMethod($this->modelIdentifier, 'where', 'completed', false)
            ->where('expires_at', '<', Carbon::now())
            ->delete();
    }

    /**
     * Generate a new random token for this user.
     *
     * This generates a token to use for verifying a new account, resetting a lost password, etc.
     * @param string $gen specify an existing token that, if we happen to generate the same value, we should regenerate on.
     * @return string
     */
    protected function generateRandomToken($gen = null)
    {
        do {
            $gen = md5(uniqid(mt_rand(), false));
        } while($this->classMapper
            ->staticMethod($this->modelIdentifier, 'where', 'hash', hash($this->config['password_reset.algorithm'], $gen))
            ->first());
        return $gen;
    }
}
