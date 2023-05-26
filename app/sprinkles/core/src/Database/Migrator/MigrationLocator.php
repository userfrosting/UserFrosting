<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Database\Migrator;

use Illuminate\Support\Str;
use Psr\Container\ContainerInterface;
use UserFrosting\System\Sprinkle\SprinkleManager;
use UserFrosting\UniformResourceLocator\Resource as ResourceInstance;
use UserFrosting\UniformResourceLocator\ResourceLocator;

/**
 * MigrationLocator Class.
 *
 * Finds all migrations class in a given sprinkle
 *
 * @author Louis Charette
 */
class MigrationLocator implements MigrationLocatorInterface
{
    /**
     * @var ContainerInterface
     */
    protected $ci;

    /**
     * @var string The resource locator migration scheme
     */
    protected $scheme = 'migrations://';

    /**
     * Class Constructor.
     *
     * @param ContainerInterface $ci
     */
    public function __construct(ContainerInterface $ci)
    {
        $this->ci = $ci;
    }

    /**
     * Loop all the available sprinkles and return a list of their migrations.
     *
     * @return array A list of all the migration files found for every sprinkle
     */
    public function getMigrations()
    {
        /** @var \UserFrosting\UniformResourceLocator\ResourceLocator */
        $locator = $this->ci->locator;

        $migrationFiles = $locator->listResources($this->scheme, false, false);

        $migrations = [];
        foreach ($migrationFiles as $migrationFile) {
            // Note that PSR4 insists that all php files must end in PHP, so ignore all
            // files that don't end in PHP.
            if ($migrationFile->getExtension() == 'php') {
                $migrations[] = $this->getMigrationDetails($migrationFile);
            }
        }

        return $migrations;
    }

    /**
     * Return an array of migration details including the class name and the sprinkle name.
     *
     * @param ResourceInstance $file The migration file
     *
     * @return string The migration full class path
     */
    protected function getMigrationDetails(ResourceInstance $file)
    {
        /** @var \UserFrosting\System\Sprinkle\SprinkleManager */
        $sprinkleManager = $this->ci->sprinkleManager;

        // Format the sprinkle name for the namespace
        $sprinkleName = $file->getLocation()->getName();
        $sprinkleNS = $sprinkleManager->getSprinkleClassNamespace($sprinkleName);

        // Getting base path, name and class name
        $basePath = str_replace($file->getBasename(), '', $file->getBasePath());
        $className = str_replace('/', '\\', $basePath) . $file->getFilename();

        // Build the class name and namespace
        return "\\$sprinkleNS\\Database\\Migrations\\$className";
    }
}
