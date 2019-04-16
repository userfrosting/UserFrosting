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
 * Trait enabling wrapping of each test case in a database transaction
 * Based on Laravel `RefreshDatabase` Traits
 *
 * @author Louis Charette
 */
trait RefreshDatabase
{
    /**
     * @var bool Indicates if the test database has been migrated.
     */
    public static $migrated = false;

    /**
     * Define hooks to migrate the database before and after each test.
     */
    public function refreshDatabase()
    {
        $this->usingInMemoryDatabase()
                        ? $this->refreshInMemoryDatabase()
                        : $this->refreshTestDatabase();
    }

    /**
     * Determine if an in-memory database is being used.
     *
     * @return bool
     */
    public function usingInMemoryDatabase()
    {
        $connection = $this->ci->db->getConnection();

        return $connection->getDatabaseName() == ':memory:';
    }

    /**
     * Refresh the in-memory database.
     */
    protected function refreshInMemoryDatabase()
    {
        $this->ci->migrator->run();
    }

    /**
     * Refresh a conventional test database.
     */
    protected function refreshTestDatabase()
    {
        if (!self::$migrated) {

            // Refresh the Database. Rollback all migrations and start over
            $this->ci->migrator->reset();
            $this->ci->migrator->run();

            self::$migrated = true;
        }

        $this->beginDatabaseTransaction();
    }

    /**
     * Handle database transactions on the specified connections.
     */
    protected function beginDatabaseTransaction()
    {
        $database = $this->ci->db;

        foreach ($this->connectionsToTransact() as $name) {
            $database->connection($name)->beginTransaction();
        }

        $this->beforeApplicationDestroyed(function () use ($database) {
            foreach ($this->connectionsToTransact() as $name) {
                $database->connection($name)->rollBack();
            }
        });
    }

    /**
     * The database connections that should have transactions.
     *
     * @return array
     */
    protected function connectionsToTransact()
    {
        return property_exists($this, 'connectionsToTransact')
                            ? $this->connectionsToTransact : [null];
    }
}
