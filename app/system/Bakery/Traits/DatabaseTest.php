<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2013-2016 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */
namespace UserFrosting\System\Bakery\Traits;

use Illuminate\Database\Capsule\Manager as Capsule;

/**
 * Database Test Trait. Include method to test the db connexion
 *
 * @extends Bakery
 * @author Alex Weissman (https://alexanderweissman.com)
 */
trait DatabaseTest
{

    /**
     * Function to test the db connexion.
     *
     * @access protected
     * @param bool $verbose (default: false)
     * @return void
     */
    protected function testDB($verbose = false)
    {
        $message = "\n<info>Testing database connexion...</info>";
        if ($verbose) {
            $this->io->write($message);
        } else {
            $this->io->debug($message);
        }

        // Boot db
        $this->ci->db;

        // Get config
        $config = $this->ci->config;

        // Check params are valids
        $dbParams = $config['db.default'];

        if (!$dbParams) {
            $this->io->write("\n<error>'default' database connection not found.  Please double-check your configuration.</error>");
            exit(1);
        }

        // Test database connection directly using PDO
        try {
            Capsule::connection()->getPdo();
        } catch (\PDOException $e) {

            $message  = "Could not connect to the database '{$dbParams['username']}@{$dbParams['host']}/{$dbParams['database']}'.  Please check your database configuration and/or google the exception shown below:".PHP_EOL;
            $message .= "Exception: " . $e->getMessage() . PHP_EOL;
            $this->io->error("$message");
            exit(1);
        }

        $message = "Database connexion successful";
        if ($verbose) {
            $this->io->write($message);
        } else {
            $this->io->debug($message);
        }
    }
}