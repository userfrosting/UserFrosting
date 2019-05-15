<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Account\Authenticate;

use Illuminate\Cache\Repository as Cache;
use UserFrosting\Support\Repository\Repository as Config;

/**
 * Handles enhanced password security methods for integration with Have I Been Pwned.
 *
 * @see https://haveibeenpwned.com/API/v2
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
    public function __construct(Cache $cache, Config $config)
    {
        $this->cache = $cache;
        $this->config = $config;
    }

    /**
     * Check a password SHA1 hash against an array of compromised password hashes.
     *
     * @param  string $hash  The hash of the potential password to be used.
     * @param  array  $array Array of password hashes in the format c2d18a7d49b0d4260769eb03d027066d29a:181 - or <hash>:<number of breaches.
     * @return string A numeric string representing the number of times a password has been compromised.
     */
    private function checkHash($hash, $array)
    {
        foreach ($array as $index => $pwHash) {
            $breachedItemParts = explode(':', $pwHash);

            $breachedItemHash = $breachedItemParts[0];
            $numberOfBreaches = $breachedItemParts[1];

            // compare the hash suffix from Have I Been Pwned with password hash suffix.
            if ($breachedItemHash == substr($hash, 5)) {
                // if a match is found just return the response.
                return $breaches = trim($numberOfBreaches);
            } else {
                $breaches = '0';
            }
        }

        return $breaches;
    }

    /**
     * Generates a list of compromised passwords stored in cache or by querying Have I Been Pwned API.
     *
     * First check the cache to see if the hash prefix is stored.
     * If not found in cache, query Have I Been Pwned API and store response in cache.
     * @param  string $password
     * @return string A numeric string representing the number of times a password has been compromised.
     */
    public function checkPassword($password)
    {
        // Get the SHA1 hash of our password.
        // The first 5 characters (hash prefix) are sent to Have I Been Pwned.
        $passwordHash = strtoupper(sha1($password));
        $hashPrefix = substr($passwordHash, 0, 5);

        $cacheMinutes = $this->config['site.password_security.enforce_no_compromised.cache'];

        // We can use the comparison list directly from cache if it is found. Otherwise, we query Have I Been Pwned API and then save response to cache.
        $hashArray = $this->cache->remember($hashPrefix, $cacheMinutes, function () use ($hashPrefix) {
            return $this->getHashArrayFromAPI($hashPrefix);
        });

        return $this->checkHash($passwordHash, $hashArray);
    }

    /**
     * Queries Have I been Pwned API to generate list of hashed compromised password suffixes.
     *
     * @param  string $hashPrefix The prefix (first 5 characters) of hashed password.
     * @return array  Array of password hashes in the format c2d18a7d49b0d4260769eb03d027066d29a:181 - or <hash>:<number of breaches.
     */
    private function getHashArrayFromAPI($hashPrefix)
    {
        $ch = curl_init();
        $optionsArray = [
        CURLOPT_URL            => 'https://api.pwnedpasswords.com/range/' . $hashPrefix,
        CURLOPT_RETURNTRANSFER => true
    ];
        curl_setopt_array($ch, $optionsArray);

        // execute request and get response.
        $query = curl_exec($ch);
        $hashArray = preg_split("/[\n,]+/", $query);

        return $hashArray;
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
     * Checks the maximum number of times that is acceptable for a password to have appeared in breaches.
     *
     * @return string Numeric string with -1 meaning disabled.
     */
    public function breachThreshold()
    {
        return $this->config['site.password_security.enforce_no_compromised.breaches'];
    }
}
