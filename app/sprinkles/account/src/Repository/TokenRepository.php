<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Account\Repository;

use Carbon\Carbon;
use UserFrosting\Sprinkle\Account\Database\Models\Interfaces\UserInterface;
use UserFrosting\Sprinkle\Core\Database\Models\Model;
use UserFrosting\Sprinkle\Core\Util\ClassMapper;

/**
 * An abstract class for interacting with a repository of time-sensitive user tokens.
 *
 * User tokens are used, for example, to perform password resets and new account email verifications.
 * @author Alex Weissman (https://alexanderweissman.com)
 * @see https://learn.userfrosting.com/users/user-accounts
 */
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

    /**
     * Create a new TokenRepository object.
     *
     * @param ClassMapper $classMapper Maps generic class identifiers to specific class names.
     * @param string      $algorithm   The hashing algorithm to use when storing generated tokens.
     */
    public function __construct(ClassMapper $classMapper, $algorithm = 'sha512')
    {
        $this->classMapper = $classMapper;
        $this->algorithm = $algorithm;
    }

    /**
     * Cancels a specified token by removing it from the database.
     *
     * @param  int         $token The token to remove.
     * @return Model|false
     */
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

    /**
     * Completes a token-based process, invoking updateUser() in the child object to do the actual action.
     *
     * @param  int         $token      The token to complete.
     * @param  mixed[]     $userParams An optional list of parameters to pass to updateUser().
     * @return Model|false
     */
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
        $user = $this->classMapper->staticMethod('user', 'find', $model->user_id);

        if (is_null($user)) {
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

    /**
     * Create a new token for a specified user.
     *
     * @param  UserInterface $user    The user object to associate with this token.
     * @param  int           $timeout The time, in seconds, after which this token should expire.
     * @return Model         The model (PasswordReset, Verification, etc) object that stores the token.
     */
    public function create(UserInterface $user, $timeout)
    {
        // Remove any previous tokens for this user
        $this->removeExisting($user);

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

    /**
     * Determine if a specified user has an incomplete and unexpired token.
     *
     * @param  UserInterface $user  The user object to look up.
     * @param  int           $token Optionally, try to match a specific token.
     * @return Model|false
     */
    public function exists(UserInterface $user, $token = null)
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

    /**
     * Delete all existing tokens from the database for a particular user.
     *
     * @param  UserInterface $user
     * @return int
     */
    protected function removeExisting(UserInterface $user)
    {
        return $this->classMapper
            ->staticMethod($this->modelIdentifier, 'where', 'user_id', $user->id)
            ->delete();
    }

    /**
     * Remove all expired tokens from the database.
     *
     * @return bool|null
     */
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
     * @param  string $gen specify an existing token that, if we happen to generate the same value, we should regenerate on.
     * @return string
     */
    protected function generateRandomToken($gen = null)
    {
        do {
            $gen = md5(uniqid(mt_rand(), false));
        } while ($this->classMapper
            ->staticMethod($this->modelIdentifier, 'where', 'hash', hash($this->algorithm, $gen))
            ->first());

        return $gen;
    }

    /**
     * Modify the user during the token completion process.
     *
     * This method is called during complete(), and is a way for concrete implementations to modify the user.
     * @param  UserInterface $user the user object to modify.
     * @param  mixed[]       $args
     * @return mixed[]       $args the list of parameters that were supplied to the call to `complete()`
     */
    abstract protected function updateUser(UserInterface $user, $args);
}
