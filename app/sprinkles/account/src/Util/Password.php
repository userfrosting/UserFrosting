<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */
namespace UserFrosting\Sprinkle\Account\Util;

/**
 * Password utility class
 *
 * @author Alex Weissman (https://alexanderweissman.com)
 */
class Password
{
    /**
     * Default crypt cost factor.
     *
     * @var int
     */
    protected static $rounds = 12;

    /**
     * Returns the hashing type for a specified password hash.
     *
     * Automatically detects the hash type: "sha1" (for UserCake legacy accounts), "legacy" (for 0.1.x accounts), and "modern" (used for new accounts).
     * @param string $password the hashed password.
     * @param array  $options
     * @return string "sha1"|"legacy"|"modern".
     */
    public static function getHashType($password, array $options = [])
    {
        // If the password in the db is 65 characters long, we have an sha1-hashed password.
        if (strlen($password) == 65) {
            return 'sha1';
        } elseif (substr($password, 0, 7) == '$2y$' . static::cost($options) . '$') {
            return 'legacy';
        }

        return 'modern';
    }

    /**
     * Hashes a plaintext password using bcrypt.
     *
     * @param string $password the plaintext password.
     * @param array  $options
     * @return string the hashed password.
     * @throws HashFailedException
     */
    public static function hash($password, array $options = [])
    {
        $hash = password_hash($password, PASSWORD_BCRYPT, [
            'cost' => static::cost($options),
        ]);

        if (!$hash) {
            throw new HashFailedException();
        }

        return $hash;
    }

    /**
     * Verify a plaintext password against the user's hashed password.
     *
     * @param string $password The plaintext password to verify.
     * @param string $hash The hash to compare against.
     * @return boolean True if the password matches, false otherwise.
     */
    public static function verify($password, $hash, array $options = [])
    {
        $hashType = static::getHashType($hash, $options);

        if ($hashType == 'sha1') {
            // Legacy UserCake passwords
            $salt = substr($hash, 0, 25);		// Extract the salt from the hash
            $hashInput = $salt . sha1($salt . $password);
            if (hash_equals($hashInput, $hash) === true) {
                return true;
            }

            return false;

        } elseif ($hashType == 'legacy') {
            // Homegrown implementation (assuming that current install has been using a cost parameter of 12)
            // Used for manual implementation of bcrypt.
            $extract = substr($hash, 0, 60);
            $compare = crypt($password, '$2y$' . static::cost($options) . '$' . substr($hash, 60));

            if (hash_equals($extract, $compare) === true) {
                return true;
            }

            return false;
        }

        // Modern implementation
        return password_verify($password, $hash);
    }

    /**
     * Extract the cost value from the options array.
     *
     * @param  array  $options
     * @return int
     */
    protected static function cost(array $options = [])
    {
        return isset($options['rounds']) ? $options['rounds'] : static::$rounds;
    }
}
