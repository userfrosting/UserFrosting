<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Account\Authenticate;

use UserFrosting\Sprinkle\Core\Facades\Cache;

/**
 * Handles enhanced password security methods for integration with Have I Been pwnedpasswords
 *
 * @see https://haveibeenpwned.com/API/v2
 * @author Amos Folz
 */
class PasswordSecurity
{
    /**
     * Check a password SHA1 hash against an array of compromised password hashes.
     *
     * @param  string $hash  The hash of the potential password to be used.
     * @param  array  $array Array of password hashes in the format c2d18a7d49b0d4260769eb03d027066d29a:181 - or <hash>:<number of breaches.
     * @return string $result The number of breaches password has been exposed in.
     */
    private static function checkHash($hash, $array)
    {
        foreach ($array as $index => $pwHash) {
            $breachedItemParts = explode(':', $pwHash);

            $breachedItemHash = $breachedItemParts[0];
            $numberOfBreaches = $breachedItemParts[1];

            // compare the hash suffix from Have I Been Pwned with password hash suffix.
            if ($breachedItemHash == substr($hash, 5)) {
                // if a match is found just return the response.
                return $result = trim($numberOfBreaches);
            } else {
                $result = '0';
            }
        }

        return $result;
    }

    /**
     * Generates a list of compromised passwords stored in cache or by querying Have I Been Pwned API.
     *
     * First check the cache to see if the hash prefix is stored.
     * If not found in cache, query Have I Been Pwned API and store response in cache.
     * @see https://haveibeenpwned.com/API/v2
     * @param  string $password
     * @return array  $result An array containing the password checked and the number of breaches.
     */
    public static function checkPassword($password)
    {

        // Setup the variable that will be returned.
        $result = ['password' => $password];

        // Get the SHA1 hash of our password.
        // The first 5 characters (hash prefix) are sent to Have I Been Pwned.
        $passwordHash = strtoupper(sha1($password));
        $hashPrefix = substr($passwordHash, 0, 5);

        // We can return our comparison list directly from cache if it exists. Otherwise, we query Have I Been Pwned API.
        if (Cache::has($hashPrefix)) {
            $hashArray = Cache::get($hashPrefix);

            $result['breaches'] = PasswordSecurity::checkHash($passwordHash, $hashArray);

            return $result;
        } else {

        // Query Have I Been Pwned API and save response to cache.
            $ch = curl_init();
            $optionsArray = [
          CURLOPT_URL            => 'https://api.pwnedpasswords.com/range/' . $hashPrefix,
          CURLOPT_RETURNTRANSFER => true
      ];

            curl_setopt_array($ch, $optionsArray);

            // execute request, get and cache response.
            $query = curl_exec($ch);
            $hashArray = preg_split("/[\n,]+/", $query);
            Cache::add($hashPrefix, $hashArray, 10);

            $result['breaches'] = PasswordSecurity::checkHash($passwordHash, $hashArray);

            return $result;
        }
    }
}
