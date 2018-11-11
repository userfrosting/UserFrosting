<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
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
     * Function used to execute the seed
     */
    abstract public function run();
}
