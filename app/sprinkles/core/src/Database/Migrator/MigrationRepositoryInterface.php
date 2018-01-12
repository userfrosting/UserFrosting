<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
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
     * Get the ran migrations.
     *
     * @return array An array of migration class names
     */
    public function getRan();

    /**
     * Get list of migrations.
     *
     * @param  int  $steps
     * @return array
     */
    public function getMigrations($steps);

    /**
     * Get the last migration batch.
     *
     * @return array
     */
    public function getLast();

    /**
     * Log that a migration was run.
     *
     * @param  string  $file
     * @param  int     $batch
     * @return void
     */
    public function log($file, $batch);

    /**
     * Remove a migration from the log.
     *
     * @param  string  $migration
     * @return void
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
     *
     * @return void
     */
    public function createRepository();

    /**
     *    Delete the migration repository data store
     *
     *    @return void
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
