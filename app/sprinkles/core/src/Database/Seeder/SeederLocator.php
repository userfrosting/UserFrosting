<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */
namespace UserFrosting\Sprinkle\Core\Database\Seeder;

use Illuminate\Support\Collection;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use UserFrosting\System\Sprinkle\SprinkleManager;

/**
 * SeederLocator Class
 *
 * Finds all seeds class across sprinkles
 *
 * @author Louis Charette
 */
class SeederLocator
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
     *    Returm a list of all available seeds for a specific sprinkle
     *
     *    @param  string $sprinkleName The sprinkle name
     *    @return array The list of available seed classes
     */
    public function getSeedersForSprinkle($sprinkleName)
    {
        $seeds = new Collection;

        // Get the sprinkle seed path and get all files in that path recursively
        $path = $this->seederDirectoryPath($sprinkleName);

        // If directory diesn't exist, stop
        if (!$this->files->exists($path)) {
            return $seeds;
        }

        // Get files
        $files = $this->files->allFiles($path);

        // Transform the path into the mgiration full class name
        $seeds = collect($files)->transform(function ($file) use ($sprinkleName, $path) {
            return $this->getSeedDetails($file, $path, $sprinkleName);
        });

        // Return as array
        return $seeds;
    }

    /**
     *    Loop all the available sprinkles and return a list of their seeds
     *
     *    @return Collection A collection of all the seed classes found for every sprinkle
     */
    public function getSeeders()
    {
        $seeds = new Collection;
        foreach ($this->sprinkleManager->getSprinkleNames() as $sprinkle) {
            $sprinkleSeeds = $this->getSeedersForSprinkle($sprinkle);
            $seeds = $seeds->merge($sprinkleSeeds);
        }

        return $seeds;
    }

    /**
     * Returns the path of the seed directory for a sprinkle.
     *
     * @param string $sprinkleName
     * @return string The sprinkle seed dir path
     */
    protected function seederDirectoryPath($sprinkleName)
    {
        return \UserFrosting\SPRINKLES_DIR .
               \UserFrosting\DS .
               $sprinkleName .
               \UserFrosting\DS .
               \UserFrosting\SRC_DIR_NAME .
               "/Database/Seeds";
    }

    /**
     *    Get the full classname of a seed based on the absolute file path,
     *    the initial search path and the SprinkleName
     *
     *    @param  string $file The seed file absolute path
     *    @param  string $path The initial search path
     *    @param  string $sprinkleName The sprinkle name
     *    @return array The details about a seed file [name, class, sprinkle]
     */
    protected function getSeedDetails($file, $path, $sprinkleName)
    {
        // Format the sprinkle name for the namespace
        $sprinkleName = Str::studly($sprinkleName);

        // Extract the class name from the path and file
        $relativePath = str_replace($path, '', $file);
        $className = str_replace('.php', '', $relativePath);
        $className = str_replace('/', '', $className);

        // Build the class name and namespace
        return [
            'name' => $className,
            'class' => "\\UserFrosting\\Sprinkle\\".$sprinkleName."\\Database\\Seeds\\" . $className,
            'sprinkle' => $sprinkleName
        ];
    }
}
