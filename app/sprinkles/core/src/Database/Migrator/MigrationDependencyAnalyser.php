<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */
namespace UserFrosting\Sprinkle\Core\Database\Migrator;

use ReflectionClass;
use UserFrosting\Sprinkle\Core\Util\BadClassNameException;
use UserFrosting\Sprinkle\Core\Facades\Debug;

/**
 * MigrationDependencyAnalyser Class
 *
 * Helper class used to analyse migrations dependencies and return the
 * migrations classes in the correct order for migration to be run up without
 * dependency collisions.
 *
 * @author Louis Charette
 */
class MigrationDependencyAnalyser
{
    /**
     * @var \Illuminate\Support\Collection List of fulfillable migrations
     */
    protected $fulfillable;

    /**
     * @var \Illuminate\Support\Collection List of unfulfillable migration (Migration that needs to be run and their dependencies are NOT met)
     */
    protected $unfulfillable;

    /**
     * @var \Illuminate\Support\Collection List of installed migration
     */
    protected $installed;

    /**
     * @var \Illuminate\Support\Collection List of migration to install
     */
    protected $pending;

    /**
     * @var bool True/false if the analyse method has been called
     */
    protected $analysed = false;

    /**
     *    Constructor
     *
     *    @param array $pending The pending migrations
     *    @param array $installed The installed migrations
     */
    public function __construct(array $pending = [], array $installed = [])
    {
        $this->pending = collect($pending);
        $this->installed = collect($installed);
    }

    /**
     *    Analyse the dependencies
     *
     *    @return void
     */
    public function analyse()
    {
        // Reset fulfillable/unfulfillable lists
        $this->analysed = false;
        $this->fulfillable = collect([]);
        $this->unfulfillable = collect([]);

        // Loop pending and check for dependencies
        foreach ($this->pending as $migration) {
            $this->validateClassDependencies($migration);
        }

        $this->analysed = true;
    }

    /**
     *    Validate if a migration is fulfillable.
     *    N.B.: The key element here is the recursion while validating the
     *    dependencies. This is very important as the order the migrations needs
     *    to be run is defined by this recursion. By waiting for the dependency
     *    to be marked as fulfillable to mark the parent as fulfillable, the
     *    parent class will be automatocally placed after it's dependencies
     *    in the `fullfillable` property.
     *
     *    @param  string $migrationName The migration classname
     *    @return bool True/False if the migration is fulfillable
     */
    protected function validateClassDependencies($migrationName)
    {
        // Get migration dependencies
        $dependencies = $this->getMigrationDependencies($migrationName);

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

        // If it's already run, it's fulfillable
        // Mark it as such for next time it comes up in this point
        if ($this->installed->contains($migrationName)) {
            return $this->markAsFulfillable($migrationName);
        }

        // Loop dependencies. If one is not fulfillable, then this migration is not either
        foreach ($dependencies as $dependency) {

            // The dependency might already be installed. Check that first
            if ($this->installed->contains($dependency)) {
                continue;
            }

            // Check is the dependency is pending installation. If so, check for it's dependencies.
            // If the dependency is not fullfillable, then this one isn't either
            if (!$this->pending->contains($dependency) || !$this->validateClassDependencies($dependency)) {
                return $this->markAsUnfulfillable($migrationName);
            }
        }

        // If no dependencies returned false, it's fulfillable
        return $this->markAsFulfillable($migrationName);
    }

    /**
     *    Return the fulfillable list. Analyse the stack if not done already
     *
     *    @return array
     */
    public function getFulfillable()
    {
        if (!$this->analysed) {
            $this->analyse();
        }

        return $this->fulfillable->toArray();
    }

    /**
     *    Return the fulfillable list. Analyse the stack if not done already
     *
     *    @return array
     */
    public function getUnfulfillable()
    {
        if (!$this->analysed) {
            $this->analyse();
        }

        return $this->unfulfillable->toArray();
    }

    /**
     *    Mark a dependency as fulfillable. Removes it from the pending list and add it to the fulfillable list
     *
     *    @param  string $migration The migration classname
     *    @return bool True, it's fulfillable
     */
    protected function markAsFulfillable($migration)
    {
        $this->fulfillable->push($migration);
        return true;
    }

    /**
     *    Mark a dependency as unfulfillable. Removes it from the pending list and add it to the unfulfillable list
     *
     *    @param  string $migration The migration classname
     *    @return bool False, it's not fullfillable
     */
    protected function markAsUnfulfillable($migration)
    {
        $this->unfulfillable->push($migration);
        return false;
    }

    /**
     *    Returns the migration dependency list
     *    Also handles the old deprecated behaviour where dependencies where not in a static property
     *
     *    @param  string $migration The migration class
     *    @return array The dependency list
     */
    protected function getMigrationDependencies($migration) {

        // Make sure class exists
        if (!class_exists($migration)) {
            throw new BadClassNameException("Unable to find the migration class '$migration'." );
        }

        // If the `dependencies` property exist and is static, use this one.
        // Otherwise, get a class instance and the non static property
        $reflectionClass = new ReflectionClass($migration);
        if ($reflectionClass->hasProperty('dependencies') && $reflectionClass->getProperty('dependencies')->isStatic()) {
            return $migration::$dependencies;
        } else if (property_exists($migration, 'dependencies')) {
            Debug::debug("`$migration` uses a non static `dependencies` property. Please change the `dependencies` property to a static property.");
            $instance = new $migration();
            return $instance->dependencies;
        } else {
            return [];
        }
    }
}
