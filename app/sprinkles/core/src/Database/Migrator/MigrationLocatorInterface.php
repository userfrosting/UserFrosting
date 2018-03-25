<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */
namespace UserFrosting\Sprinkle\Core\Database\Migrator;

/**
 * MigrationLocatorInterface
 *
 * All MigrationLocator handlers must implement this interface.
 *
 * @author Louis Charette
 */
interface MigrationLocatorInterface
{
    /**
     *    Returm a list of all available migration available for a specific sprinkle
     *
     *    @param  string $sprinkleName The sprinkle name
     *    @return array The list of available migration classes
     */
    public function getMigrationsForSprinkle($sprinkleName);

    /**
     *    Loop all the available sprinkles and return a list of their migrations
     *
     *    @return array A list of all the migration files found for every sprinkle
     */
    public function getMigrations();
}
