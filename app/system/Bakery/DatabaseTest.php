<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */
namespace UserFrosting\System\Bakery;

use Illuminate\Database\Capsule\Manager as Capsule;

/**
 * Database Test Trait. Include method to test the db connexion
 *
 * @author Alex Weissman (https://alexanderweissman.com)
 */
trait DatabaseTest
{

    /**
     * Function to test the db connexion.
     *
     * @access protected
     * @return bool True if success
     */
    protected function testDB()
    {
        // Boot db
        $this->ci->db;

        // Get config
        $config = $this->ci->config;

        // Check params are valids
        $dbParams = $config['db.default'];
        if (!$dbParams) {
            throw new \Exception("'default' database connection not found.  Please double-check your configuration.");
        }

        // Test database connection directly using PDO
        try {
            Capsule::connection()->getPdo();
        } catch (\PDOException $e) {
            $message  = "Could not connect to the database '{$dbParams['username']}@{$dbParams['host']}/{$dbParams['database']}':".PHP_EOL;
            $message .= "Exception: " . $e->getMessage() . PHP_EOL.PHP_EOL;
            $message .= "Please check your database configuration and/or google the exception shown above and run command again.";
            throw new \Exception($message);
        }

        return true;
    }
}