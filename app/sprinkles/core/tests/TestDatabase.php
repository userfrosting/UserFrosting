<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
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
        $this->ci->db->getDatabaseManager()->setDefaultConnection('test_integration');
    }
}
