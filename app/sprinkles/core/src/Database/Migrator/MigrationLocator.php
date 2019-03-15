<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Database\Migrator;

use Illuminate\Support\Str;
use UserFrosting\UniformResourceLocator\Resource as ResourceInstance;
use UserFrosting\UniformResourceLocator\ResourceLocator;

/**
 * MigrationLocator Class
 *
 * Finds all migrations class in a given sprinkle
 *
 * @author Louis Charette
 */
class MigrationLocator implements MigrationLocatorInterface
{
    /**
     * @var ResourceLocator The locator service
     */
    protected $locator;

    /**
     * @var string The resource locator migration scheme
     */
    protected $scheme = 'migrations://';

    /**
     * Class Constructor
     *
     * @param ResourceLocator $locator The locator services
     */
    public function __construct(ResourceLocator $locator)
    {
        $this->locator = $locator;
    }

    /**
     * Loop all the available sprinkles and return a list of their migrations
     *
     * @return array A list of all the migration files found for every sprinkle
     */
    public function getMigrations()
    {
        $migrationFiles = $this->locator->listResources($this->scheme, false, false);

        $migrations = [];
        foreach ($migrationFiles as $migrationFile) {
            $migrations[] = $this->getMigrationDetails($migrationFile);
        }

        return $migrations;
    }

    /**
     * Return an array of migration details inclusing the classname and the sprinkle name
     *
     * @param  ResourceInstance $file The migration file
     * @return string           The migration full class path
     */
    protected function getMigrationDetails(ResourceInstance $file)
    {
        // Format the sprinkle name for the namespace
        $sprinkleName = $file->getLocation()->getName();
        $sprinkleName = Str::studly($sprinkleName);

        // Getting base path, name and classname
        $basePath = str_replace($file->getBasename(), '', $file->getBasePath());
        $name = $basePath . $file->getFilename();
        $className = str_replace('/', '\\', $basePath) . $file->getFilename();

        // Build the class name and namespace
        return "\\UserFrosting\\Sprinkle\\$sprinkleName\\Database\\Migrations\\$className";
    }
}
