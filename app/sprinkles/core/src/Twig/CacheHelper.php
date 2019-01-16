<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Twig;

use Interop\Container\ContainerInterface;
use Illuminate\Filesystem\Filesystem;

/**
 * Provides helper function to delete the Twig cache directory
 *
 * @author Alex Weissman (https://alexanderweissman.com)
 */
class CacheHelper
{
    /**
     * @var ContainerInterface The global container object, which holds all your services.
     */
    protected $ci;

    /**
     * Constructor.
     *
     * @param ContainerInterface $ci The global container object, which holds all your services.
     */
    public function __construct(ContainerInterface $ci)
    {
        $this->ci = $ci;
    }

    /**
     * Function that delete the Twig cache directory content
     *
     * @return bool true/false if operation is successfull
     */
    public function clearCache()
    {
        // Get location
        $path = $this->ci->locator->findResource('cache://twig', true, true);

        // Get Filesystem instance
        $fs = new Filesystem();

        // Make sure directory exist and delete it
        if ($fs->exists($path)) {
            return $fs->deleteDirectory($path, true);
        }

        // It's still considered a success if directory doesn't exist yet
        return true;
    }
}
