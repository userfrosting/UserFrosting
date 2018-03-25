<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */
namespace UserFrosting\Sprinkle\Core\Database\Migrator;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use UserFrosting\System\Sprinkle\SprinkleManager;
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
     * @var SprinkleManager The Sprinkle manager service
     */
    protected $sprinkleManager;

    /**
     * @var Filesystem The filesystem instance
     */
    protected $files;

    /**
     *    Class Constructor
     *
     *    @param  SprinkleManager $sprinkleManager The sprinkle manager services
     *    @param  Filesystem $files The filesystem instance
     */
    public function __construct(SprinkleManager $sprinkleManager, Filesystem $files)
    {
        $this->sprinkleManager = $sprinkleManager;
        $this->files = $files;
    }

    /**
     *    Returm a list of all available migration available for a specific sprinkle
     *
     *    @param  string $sprinkleName The sprinkle name
     *    @return array The list of available migration classes
     */
    public function getMigrationsForSprinkle($sprinkleName)
    {
        // Get the sprinkle migration path and get all files in that path recursively
        $path = $this->migrationDirectoryPath($sprinkleName);

        // If directory diesn't exist, stop
        if (!$this->files->exists($path)) {
            return [];
        }

        // Get files
        $files = $this->files->allFiles($path);

        // Transform the path into the mgiration full class name
        $migrations = collect($files)->transform(function ($file) use ($sprinkleName, $path) {
            return $this->getMigrationClassName($file, $path, $sprinkleName);
        });

        // Return as array
        return $migrations->all();
    }

    /**
     *    Loop all the available sprinkles and return a list of their migrations
     *
     *    @return array A list of all the migration files found for every sprinkle
     */
    public function getMigrations()
    {
        $migrationsFiles = [];
        foreach ($this->sprinkleManager->getSprinkleNames() as $sprinkle) {
            $migrationsFiles = array_merge($this->getMigrationsForSprinkle($sprinkle), $migrationsFiles);
        }

        return $migrationsFiles;
    }

    /**
     * Returns the path of the Migration directory.
     *
     * @param string $sprinkleName
     * @return string The sprinkle Migration dir path
     */
    protected function migrationDirectoryPath($sprinkleName)
    {
        return \UserFrosting\SPRINKLES_DIR .
               \UserFrosting\DS .
               $sprinkleName .
               \UserFrosting\DS .
               \UserFrosting\SRC_DIR_NAME .
               "/Database/Migrations";
    }

    /**
     *    Get the full classname of a migration based on the absolute file path,
     *    the initial search path and the SprinkleName
     *
     *    @param  string $file The migration file absolute path
     *    @param  string $path The initial search path
     *    @param  string $sprinkleName The sprinkle name
     *    @return string The migration class name
     */
    protected function getMigrationClassName($file, $path, $sprinkleName)
    {
        // Format the sprinkle name for the namespace
        $sprinkleName = Str::studly($sprinkleName);

        // Extract the class name from the path and file
        $relativePath = str_replace($path, '', $file);
        $className = str_replace('.php', '', $relativePath);
        $className = str_replace('/', '\\', $className);

        // Build the class name and namespace
        return "\\UserFrosting\\Sprinkle\\".$sprinkleName."\\Database\\Migrations" . $className;
    }
}
