<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Tests;

/**
 * Trait used to run test against the `test_integration` db connection
 *
 * @author Louis Charette
 */
trait TestDatabase
{
    /**
     *    Define the test_integration database connection the default one
     */
    public function setupTestDatabase()
    {
        $connection = $this->ci->config['testing.dbConnection'];
        $this->ci->db->getDatabaseManager()->setDefaultConnection($connection);
    }
}
