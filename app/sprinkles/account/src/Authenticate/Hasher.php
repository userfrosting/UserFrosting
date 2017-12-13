<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */
namespace UserFrosting\Sprinkle\Account\Authenticate;

/**
 * Password hashing and validation class
 *
 * @author Alex Weissman (https://alexanderweissman.com)
 */
class Hasher
{
    /**
     * Default crypt cost factor.
     *
     * @var int
     */
    protected $defaultRounds = 10;

    /**
     * Returns the hashing type for a specified password hash.
     *
     * Automatically detects the hash type: "sha1" (for UserCake legacy accounts), "legacy" (for 0.1.x accounts), and "modern" (used for new accounts).
     * @param string $password the hashed password.
     * @return string "sha1"|"legacy"|"modern".
     */
    public function getHashType($password)
    {
        // If the password in the db is 65 characters long, we have an sha1-hashed password.
        if (strlen($password) == 65) {
            return 'sha1';
        } elseif (strlen($password) == 82) {
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
    public function hash($password, array $options = [])
    {
        $hash = password_hash($password, PASSWORD_BCRYPT, [
            'cost' => $this->cost($options),
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
     * @param array  $options
     * @return boolean True if the password matches, false otherwise.
     */
    public function verify($password, $hash, array $options = [])
    {
        $hashType = $this->getHashType($hash);

        if ($hashType == 'sha1') {
            // Legacy UserCake passwords
            $salt = substr($hash, 0, 25);		// Extract the salt from the hash
            $inputHash = $salt . sha1($salt . $password);

            return (hash_equals($inputHash, $hash) === true);

        } elseif ($hashType == 'legacy') {
            // Homegrown implementation (assuming that current install has been using a cost parameter of 12)
            // Used for manual implementation of bcrypt.
            // Note that this legacy hashing put the salt at the _end_ for some reason.
            $salt = substr($hash, 60);
            $inputHash = crypt($password, '$2y$12$' . $salt);
            $correctHash = substr($hash, 0, 60);

            return (hash_equals($inputHash, $correctHash) === true);
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
    protected function cost(array $options = [])
    {
        return isset($options['rounds']) ? $options['rounds'] : $this->defaultRounds;
    }
}
