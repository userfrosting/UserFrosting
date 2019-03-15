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
 * MigrationRollbackDependencyAnalyser Class
 *
 * Helper class used to analyse migrations rollback dependencies and return the
 * list of migrations classes that prevent the specified migrations to be rolledback
 *
 * @author Louis Charette
 */
class MigrationRollbackDependencyAnalyser extends MigrationDependencyAnalyser
{
    /**
     * Constructor
     *
     * @param array $installed The installed migrations
     * @param array $rollback  The migrations to rollback
     */
    public function __construct(array $installed = [], array $rollback = [])
    {
        $this->pending = collect($installed);
        $this->installed = collect($rollback);
    }

    /**
     * Received each installed migrations and determine if it depends on the
     * migrations we want to delete (rollback). It can if no other installed
     * migrations depends on it. In this context, fulfillable/unfulfillable
     * represent the same thing as "up" dependencies. fulfillable can be
     * rolledback, unfulfillable cannot.
     *
     * @param  string $migrationName The migration classname
     * @return bool   True/False if the migration is fulfillable
     */
    protected function validateClassDependencies($migrationName)
    {
        // If it's already marked as fulfillable, it's fulfillable
        // Return true directly (it's already marked)
        if ($this->fulfillable->contains($migrationName)) {
            return true;
        }

        // If it's already marked as unfulfillable, it's unfulfillable
        // Return false directly (it's already marked)
        if ($this->unfulfillable->contains($migrationName)) {
            return false;
        }

        // If it's in the list of migration to rollback (installed), it's ok to delete this one
        if ($this->installed->contains($migrationName)) {
            return $this->markAsFulfillable($migrationName);
        }

        // Get migration dependencies
        $dependencies = $this->getMigrationDependencies($migrationName);

        // If this migration has a dependencies for one of the migration to
        // rollback (installed), we can't perform the rollback
        if ($missing = array_intersect($this->installed->toArray(), $dependencies)) {
            return $this->markAsUnfulfillable($migrationName, $missing);
        }

        // If no dependencies returned false, it's fulfillable
        return $this->markAsFulfillable($migrationName);
    }
}
