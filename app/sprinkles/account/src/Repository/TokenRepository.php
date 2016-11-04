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
use UserFrosting\Sprinkle\Core\Util\ClassMapper;

abstract class TokenRepository
{

    /**
     * @var ClassMapper
     */
    protected $classMapper;

    /**
     * @var string
     */    
    protected $algorithm;
    
    /**
     * @var string
     */
    protected $modelIdentifier;
    
    public function __construct(ClassMapper $classMapper, $algorithm = 'sha512')
    {
        $this->classMapper = $classMapper;
        $this->algorithm = $algorithm;
    }

    public function create(User $user, $timeout)
    {
        // Compute expiration time
        $expiresAt = Carbon::now()->addSeconds($timeout);

        $model = $this->classMapper->createInstance($this->modelIdentifier);

        // Generate a random token
        $model->setToken($this->generateRandomToken());

        // Hash the password reset token for the stored version
        $hash = hash($this->algorithm, $model->getToken());

        $model->fill([
            'hash'       => $hash,
            'completed'  => false,
            'expires_at' => $expiresAt
        ]);

        $model->user_id = $user->id;

        $model->save();

        return $model;
    }

    public function exists(User $user, $token = null)
    {
        $model = $this->classMapper
            ->staticMethod($this->modelIdentifier, 'where', 'user_id', $user->id)
            ->where('completed', false)
            ->where('expires_at', '>', Carbon::now());

        if ($token) {
            // get token hash
            $hash = hash($this->algorithm, $token);
            $model->where('hash', $hash);
        }

        return $model->first() ?: false;
    }

    public function cancel($token)
    {
        // Hash the password reset token for the stored version
        $hash = hash($this->algorithm, $token);

        // Find an incomplete reset request for the specified hash
        $model = $this->classMapper
            ->staticMethod($this->modelIdentifier, 'where', 'hash', $hash)
            ->where('completed', false)
            ->first();

        if ($model === null) {
            return false;
        }

        $model->delete();

        return $model;
    }

    public function complete($token, $userParams = [])
    {
        // Hash the token for the stored version
        $hash = hash($this->algorithm, $token);

        // Find an unexpired, incomplete token for the specified hash
        $model = $this->classMapper
            ->staticMethod($this->modelIdentifier, 'where', 'hash', $hash)
            ->where('completed', false)
            ->where('expires_at', '>', Carbon::now())
            ->first();

        if ($model === null) {
            return false;
        }

        // Fetch user for this token
        $user = $this->classMapper->staticMethod('user', 'where', 'id', $model->user_id)->first();

        if ($user === null) {
            return false;
        }

        $this->updateUser($user, $userParams);

        $model->fill([
            'completed'    => true,
            'completed_at' => Carbon::now()
        ]);

        $model->save();

        return $model;
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
            ->staticMethod($this->modelIdentifier, 'where', 'hash', hash($this->algorithm, $gen))
            ->first());
        return $gen;
    }
    
    abstract protected function updateUser($user, $args);
}
