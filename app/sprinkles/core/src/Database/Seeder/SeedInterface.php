<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
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
