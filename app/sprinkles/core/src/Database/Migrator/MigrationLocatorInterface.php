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
 * MigrationLocatorInterface
 *
 * All MigrationLocator handlers must implement this interface.
 *
 * @author Louis Charette
 */
interface MigrationLocatorInterface
{
    /**
     * Loop all the available sprinkles and return a list of their migrations
     *
     * @return array A list of all the migration files found for every sprinkle
     */
    public function getMigrations();
}
