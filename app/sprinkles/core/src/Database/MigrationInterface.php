<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Database;

/**
 * Migration Interfaces class.
 *
 * @author Louis Charette
 */
interface MigrationInterface
{
    /**
     * Method to apply changes to the database
     */
    public function up();

    /**
     * Method to revert changes applied by the `up` method
     */
    public function down();
}
