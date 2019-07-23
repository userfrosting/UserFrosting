<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Account\Authenticate;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\TransferException;
use GuzzleHttp\Psr7;
use Illuminate\Cache\Repository as Cache;
use Illuminate\Database\Capsule\Manager as Capsule;
use UserFrosting\Sprinkle\Account\Database\Models\Interfaces\UserInterface;
use UserFrosting\Sprinkle\Core\Util\ClassMapper;
use UserFrosting\Support\Repository\Repository as Config;
use UserFrosting\Sprinkle\Core\Facades\Debug;

/**
 * Handles advanced password security methods for integration with Have I Been Pwned.
 *
 * @see https://haveibeenpwned.com/API/v2
 *
 * @author Amos Folz
 */
class PasswordSecurity
{
    /**
     * @var Config
     */
    protected $config;

    /**
     * @var Cache
     */
    protected $cache;

    /**
     * Create a new PasswordSecurity object.
     *
     * @param Cache  $cache  Cache service instance
     * @param Config $config Config object that contains security settings.
     */
    public function __construct(Cache $cache, Config $config, ClassMapper $classMapper)
    {
        $this->cache = $cache;
        $this->config = $config;
        $this->classMapper = $classMapper;
    }

    /**
     * Checks the maximum number of times that is acceptable for a password to have appeared in breaches with -1 meaning disabled.
     *
     * @return int
     */
    public function breachThreshold()
    {
        return $this->config['site.password_security.enforce_no_compromised.breaches'];
    }

    /**
     * Generates a list of compromised passwords from cache or by querying Have I Been Pwned API.
     *
     * First check the cache to see if the hash prefix is stored.
     * If not found in cache, query Have I Been Pwned API and store response in cache.
     *
     * @param string $password
     *
     * @return int The number of times a password has been exposed in data breaches.
     */
    public function checkPassword(string $password)
    {
        // Get the SHA1 hash of our password.
        // The first 5 characters (hash prefix) are sent to Have I Been Pwned.
        $passwordHash = strtoupper(sha1($password));
        $hashPrefix = substr($passwordHash, 0, 5);

        $cacheMinutes = $this->config['site.password_security.enforce_no_compromised.cache'];

        // Check to see if it already exists in cache. If not, we query Have I Been Pwned API and then save response to cache.
        $hashArray = $this->cache->remember($hashPrefix, $cacheMinutes, function () use ($hashPrefix) {
            return $this->getHashArrayFromAPI($hashPrefix);
        });

        return $this->checkHash($passwordHash, $hashArray);
    }

    /**
     * Checks the `flag_password_reset_required` column for a user.
     *
     * @param UserInterface $currentUser
     *
     * @return bool True if a password reset is required, false otherwise.
     */
    public function checkPasswordResetRequired(UserInterface $currentUser)
    {
        if ($currentUser->flag_password_reset_required) {
            return true;
        }

        return false;
    }

    /**
     * Checks if compromised password reset feature is enabeld.
     *
     * @return bool True if the feature is enabled.
     */
    public function resetCompromisedEnabled()
    {
        return $this->config['site.login.enforce_reset_compromised'];
    }

    /**
     * Sets the `flag_password_reset_required` column for a user to true.
     *
     * @param UserInterface $currentUser
     */
    public function setPasswordResetRequired(UserInterface $currentUser)
    {

        /** @var \UserFrosting\Sprinkle\Core\Util\ClassMapper $classMapper */
        $classMapper = $this->classMapper;

        // All checks passed!  log events/activities, update user, and send email
        // Begin transaction - DB will be rolled back if an exception occurs
        Capsule::transaction(function () use ($classMapper, $currentUser) {

            // Load the user, by username
            $user = $classMapper->getClassMapping('user')::where('user_name', $currentUser['user_name'])->first();
            $user->flag_password_reset_required = true;
            $user->save();
        });
    }

    /**
     * Check a password SHA1 hash against an array of compromised password hashes.
     *
     * @param string $hash  The hash of the potential password to be used.
     * @param array  $array Array of password hashes in the format c2d18a7d49b0d4260769eb03d027066d29a:181 - or <hash>:<number of breaches.
     *
     * @return int The number of times a password has been exposed in data breaches.
     */
    private function checkHash(string $hash, array $array)
    {
        foreach ($array as $index => $pwHash) {
            $breachedItemParts = explode(':', $pwHash);

            $breachedItemHash = $breachedItemParts[0];
            $numberOfBreaches = $breachedItemParts[1];

            // compare the hash suffix from array of hash suffix with password hash suffix.
            if ($breachedItemHash == substr($hash, 5)) {
                return $breaches = (int) trim($numberOfBreaches);
            } else {
                $breaches = 0;
            }
        }

        return $breaches;
    }

    /**
     * Queries Have I been Pwned API to generate list of hashed compromised password suffixes.
     *
     * @param string $hashPrefix The prefix (first 5 characters) of hashed password.
     *
     * @return array Array of password hashes in the format c2d18a7d49b0d4260769eb03d027066d29a:181 - or <hash>:<number of breaches.
     */
    private function getHashArrayFromAPI(string $hashPrefix)
    {
        $client = new Client([
          'base_uri'    => 'https://api.pwnedpasswords.com/range/',
          'timeout'     => 2.0,
          'headers'     => [
            'User-Agent' => 'UserFrosting Application',
          ],

        ]);

        try {
            $response = $client->request('GET', $hashPrefix);
        } catch (TransferException $e) {
            if ($this->config['site.password.security.log_errors']) {
                Debug::debug(Psr7\str($e->getRequest()));
                if ($e->hasResponse()) {
                    Debug::debug(Psr7\str($e->getResponse()));
                }
            }

            return;
        }

        $body = $response->getBody()->getContents();
        $hashArray = preg_split("/[\n,]+/", $body);

        return $hashArray;
    }
}
