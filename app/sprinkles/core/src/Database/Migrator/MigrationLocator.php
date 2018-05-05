<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */
namespace UserFrosting\Sprinkle\Core\Database\Migrator;

use Illuminate\Support\Str;
use UserFrosting\UniformResourceLocator\Resource;
use UserFrosting\UniformResourceLocator\ResourceLocator;
use UserFrosting\Sprinkle\Core\Database\Migrator\MigrationLocatorInterface;

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
     *    Class Constructor
     *
     *    @param  ResourceLocator $locator The locator services
     */
    public function __construct(ResourceLocator $locator)
    {
        $this->locator = $locator;
    }

    /**
     *    Return a list of all available migration available for a specific sprinkle
     *
     *    @param  string $sprinkleName The sprinkle name
     *    @return array The list of available migration classes
     */
    public function getMigrationsForSprinkle($sprinkleName)
    {
        // Get migration and drop anything not related to the sprinkle we want
        $migrations = $this->loadMigrations($this->locator->listResources($this->scheme));
        return collect($migrations)->where('sprinkle', $sprinkleName)->pluck('class')->all();
    }

    /**
     *    Loop all the available sprinkles and return a list of their migrations
     *
     *    @return array A list of all the migration files found for every sprinkle
     */
    public function getMigrations()
    {
        $migrations = $this->loadMigrations($this->locator->listResources($this->scheme));
        return collect($migrations)->pluck('class')->all();
    }

    /**
     * Process migration Resource into info
     *
     * @param  array $migrationFiles List of migrations file
     * @return array
     */
    protected function loadMigrations($migrationFiles)
    {
        $migrations = [];
        foreach ($migrationFiles as $migrationFile) {
            $migrations[] = $this->getMigrationDetails($migrationFile);
        }
        return $migrations;
    }

    /**
     *    Return an array of migration details inclusing the classname and the sprinkle name
     *
     *    @param  Resource $file The migration file
     *    @return array The details about a seed file [name, class, sprinkle]
     */
    protected function getMigrationDetails(Resource $file)
    {
        // Format the sprinkle name for the namespace
        $sprinkleName = $file->getLocation()->getName();
        $sprinkleName = Str::studly($sprinkleName);

        // Getting base path, name and classname
        $basePath = str_replace($file->getBasename(), '', $file->getBasePath());
        $name = $basePath . $file->getFilename();
        $className = str_replace('/', '\\', $basePath) . $file->getFilename();

        // Build the class name and namespace
        return [
            'name' => $name,
            'class' => "\\UserFrosting\\Sprinkle\\$sprinkleName\\Database\\Migrations\\$className",
            'sprinkle' => $sprinkleName
        ];
    }
}
