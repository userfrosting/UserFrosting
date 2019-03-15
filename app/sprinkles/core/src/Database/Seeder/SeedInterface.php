<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Database\Seeder;

/**
 * All seeds must implement this interface.
 *
 * @author Louis Charette
 */
interface SeedInterface
{
    /**
     * Run the seed
     */
    public function run();
}
