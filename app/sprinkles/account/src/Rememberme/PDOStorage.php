<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Account\Rememberme;

use Birke\Rememberme\Storage\StorageInterface;
use Illuminate\Database\Capsule\Manager as Capsule;
use UserFrosting\Sprinkle\Account\Database\Models\Persistence;

/**
 * Store login tokens in database with PDO class
 *
 * @author Louis Charette
 */
class PDOStorage implements StorageInterface
{
    /** @var Capsule $db */
    protected $db;

    /**
     * @param Capsule $db [description]
     */
    public function __construct(Capsule $db)
    {
        $this->db = $db;
    }

    /**
     * @param  mixed  $credential
     * @param  string $token
     * @param  string $persistentToken
     * @return int
     */
    public function findTriplet($credential, $token, $persistentToken)
    {
        $result = Persistence::notExpired()->where([
            'user_id'          => $credential,
            'persistent_token' => sha1($persistentToken)
        ])->first();

        if (!$result) {
            return self::TRIPLET_NOT_FOUND;
        } elseif ($result->token === sha1($token)) {
            return self::TRIPLET_FOUND;
        }

        return self::TRIPLET_INVALID;
    }

    /**
     * @param mixed  $credential
     * @param string $token
     * @param string $persistentToken
     * @param int    $expire
     */
    public function storeTriplet($credential, $token, $persistentToken, $expire = 0)
    {
        $persistence = new Persistence([
            'user_id'          => $credential,
            'token'            => sha1($token),
            'persistent_token' => sha1($persistentToken),
            'expires_at'       => date('Y-m-d H:i:s', $expire)
        ]);
        $persistence->save();
    }

    /**
     * @param mixed  $credential
     * @param string $persistentToken
     */
    public function cleanTriplet($credential, $persistentToken)
    {
        Persistence::where([
            'user_id'          => $credential,
            'persistent_token' => sha1($persistentToken)
        ])->delete();
    }

    /**
     * Replace current token after successful authentication
     *
     * @param mixed  $credential
     * @param string $token
     * @param string $persistentToken
     * @param int    $expire
     */
    public function replaceTriplet($credential, $token, $persistentToken, $expire = 0)
    {
        try {
            Capsule::transaction(function () use ($credential, $token, $persistentToken, $expire) {
                $this->cleanTriplet($credential, $persistentToken);
                $this->storeTriplet($credential, $token, $persistentToken, $expire);
            });
        } catch (\PDOException $e) {
            throw $e;
        }
    }

    /**
     * @param mixed $credential
     */
    public function cleanAllTriplets($credential)
    {
        Persistence::where('user_id', $credential)->delete();
    }

    /**
     * Remove all expired triplets of all users.
     *
     * @param int $expiryTime Timestamp, all tokens before this time will be deleted
     */
    public function cleanExpiredTokens($expiryTime)
    {
        Persistence::where('expires_at', '<', date('Y-m-d H:i:s', $expiryTime))->delete();
    }
}
