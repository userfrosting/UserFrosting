<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Tests;

use UserFrosting\Sprinkle\Core\Session\DatabaseSessionHandler;

/**
 * Trait used to run test against the `test_integration` db connection
 *
 * @author Louis Charette
 */
trait withDatabaseSessionHandler
{
    /**
     * Reset CI with database session handler
     */
    public function useDatabaseSessionHandler()
    {
        // Skip test if using in-memory database.
        // However we tell UF to use database session handler and in-memroy
        // database, the session will always be created before the db can be
        // migrate, causing "table not found" errors
        if ($this->usingInMemoryDatabase()) {
            $this->markTestSkipped("Can't run this test on memory database");
        }

        // Force test to use database session handler
        putenv('TEST_SESSION_HANDLER=database');

        // Unset the env when test is done to avoid conflict
        $this->beforeApplicationDestroyedCallbacks[] = function()
        {
            putenv('TEST_SESSION_HANDLER');
        };

        // Refresh app to use new setup
        $this->ci->session->destroy();
        $this->refreshApplication();
        $this->setupTestDatabase(); //<-- N.B.: This is executed after the session is created on the default db...
        $this->refreshDatabase();

        // Make sure it worked
        if (!($this->ci->session->getHandler() instanceof DatabaseSessionHandler)) {
            $this->markTestSkipped('Session handler not an instance of DatabaseSessionHandler');
        }
    }
}
