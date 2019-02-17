<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Database\Seeder;

use Interop\Container\ContainerInterface;

/**
 * Seeder Class
 * Base class for seeds
 *
 * @author Louis Charette
 */
abstract class BaseSeed implements SeedInterface
{
    /**
     * @var ContainerInterface $ci
     */
    protected $ci;

    /**
     * Constructor
     *
     * @param ContainerInterface $ci
     */
    public function __construct(ContainerInterface $ci)
    {
        $this->ci = $ci;
    }

    /**
     * Validate if a specific set of migrations have been ran
     *
     * @param  string|array $migrations List of migraiton or specific migration required
     * @throws \Exception   If dependent migration is not available
     * @return bool         True on success
     */
    protected function validateMigrationDependencies($migrations)
    {
        if (!is_array($migrations)) {
            $migrations = [$migrations];
        }

        /** @var \UserFrosting\Sprinkle\Core\Database\Migrator\Migrator; */
        $migrator = $this->ci->migrator;

        // Get ran migrations list
        $ranMigrations = $migrator->getRepository()->getMigrationsList();

        // Make sure required migrations are in the ran list. Throw exception if it isn't.
        foreach ($migrations as $migration) {
            if (!in_array($migration, $ranMigrations)) {
                throw new \Exception("Migration `$migration` doesn't appear to have been run!");
            }
        }

        return true;
    }

    /**
     * Function used to execute the seed
     */
    abstract public function run();
}
