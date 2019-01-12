<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Database\Migrator;

/**
 * MigrationRepository Interface
 *
 * @author Louis Charette
 */
interface MigrationRepositoryInterface
{
    /**
     * Get the list of ran migrations
     *
     * @param  int    $steps Number of batch to return
     * @param  string $order asc|desc
     * @return array  An array of migration class names in the order they where ran
     */
    public function getMigrationsList($steps = -1, $order = 'asc');

    /**
     * Get list of migrations.
     *
     * @param  int    $steps Number of batch to return
     * @param  string $order asc|desc
     * @return array
     */
    public function getMigrations($steps = -1, $order = 'asc');

    /**
     * Get the last migration batch.
     *
     * @return array
     */
    public function getLast();

    /**
     * Log that a migration was run.
     *
     * @param string $file
     * @param int    $batch
     */
    public function log($file, $batch);

    /**
     * Remove a migration from the log.
     *
     * @param string $migration
     */
    public function delete($migration);

    /**
     * Get the next migration batch number.
     *
     * @return int
     */
    public function getNextBatchNumber();

    /**
     * Get the last migration batch number.
     *
     * @return int
     */
    public function getLastBatchNumber();

    /**
     * Create the migration repository data store.
     */
    public function createRepository();

    /**
     * Delete the migration repository data store
     */
    public function deleteRepository();

    /**
     * Determine if the migration repository exists.
     *
     * @return bool
     */
    public function repositoryExists();

    /**
     * Set the information source to gather data.
     *
     * @param string $name The source name
     */
    public function setSource($name);
}
