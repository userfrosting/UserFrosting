<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */
namespace UserFrosting\Sprinkle\Core\Database\Seeder;

use Interop\Container\ContainerInterface;
use Illuminate\Support\Str;
use UserFrosting\UniformResourceLocator\Resource;

/**
 * Seeder Class
 *
 * Finds all seeds class across sprinkles
 *
 * @author Louis Charette
 */
class Seeder
{
    /**
     * @var ContainerInterface $ci
     */
    protected $ci;

    /**
     *    Class Constructor
     *
     *    @param ContainerInterface $ci
     */
    public function __construct(ContainerInterface $ci)
    {
        $this->ci = $ci;
    }

    /**
     *    Returm a list of all available seeds for a specific sprinkle
     *
     *    @param  string $sprinkleName The sprinkle name
     *    @return array The list of available seed classes
     */
    public function getSeedersForSprinkle($sprinkleName)
    {
        return $this->loadSeeders($this->ci->locator->listResources("seeds://$sprinkleName/"));
    }

    /**
     *    Loop all the available sprinkles and return a list of their seeds
     *
     *    @return array An array of all the seed classes found for every sprinkle
     */
    public function getSeeders()
    {
        return $this->loadSeeders($this->ci->locator->listResources('seeds://'));
    }

    /**
     * Get a single seed info
     *
     * @param  string $name The seed name
     * @return array The details about a seed file [name, class, sprinkle]
     * @throws \Exception If seed not found
     */
    public function getSeed($name)
    {
        // Get seed resource
        $seedResource = $this->ci->locator->getResource("seeds://$name.php");

        // Make sure we found something
        if (!$seedResource) {
            throw new \Exception("Seed $name not found");
        }

        // Return the seed info
        return $this->getSeedDetails($seedResource);
    }

    /**
     * Return the class instance of a seed
     *
     * @param  string $name The seed name
     * @return SeedInterface The seed class instance
     * @throws \Exception If class doesn't exist
     */
    public function getSeedClass($name)
    {
        // Try to get seed info
        $seed = $this->getSeed($name);

        // Make sure class exist
        $classPath = $seed['class'];
        if (!class_exists($classPath)) {
            throw new \Exception("Seed class `$classPath` not found. Make sure the class has the correct namespace.");
        }

        // Create a new class instance
        $seedClass = new $classPath($this->ci);

        // Class must be an instance of `SeederInterface`
        if (!$seedClass instanceof SeedInterface) {
            throw new \Exception('Seed class must be an instance of `SeederInterface`');
        }

        return $seedClass;
    }

    /**
     * Execute a seed class
     *
     * @param  SeedInterface $seed The seed to execute
     * @return void
     */
    public function executeSeed(SeedInterface $seed)
    {
        $seed->run();
    }

    /**
     * Process seeder Resource into info
     *
     * @param  array $seedFiles List of seeds file
     * @return array
     */
    protected function loadSeeders($seedFiles)
    {
        $seeds = [];
        foreach ($seedFiles as $seedFile) {
            $seeds[] = $this->getSeedDetails($seedFile);
        }
        return $seeds;
    }

    /**
     *    Get the full classname of a seed based on the absolute file path,
     *    the initial search path and the SprinkleName
     *
     *    @param  Resource $file The seed file absolute path
     *    @return array The details about a seed file [name, class, sprinkle]
     */
    protected function getSeedDetails(Resource $file)
    {
        // Format the sprinkle name for the namespace
        $sprinkleName = $file->getLocation()->getName();
        $sprinkleName = Str::studly($sprinkleName);

        // Getting base path for classname
        $basePath = str_replace($file->getBasename(), '', $file->getBasePath());
        $basePath = str_replace('/', '\\', $basePath);

        // Build the class name and namespace
        return [
            'name' => $file->getFilename(),
            'class' => "\\UserFrosting\\Sprinkle\\".$sprinkleName."\\Database\\Seeds\\" . $basePath . $file->getFilename(),
            'sprinkle' => $sprinkleName
        ];
    }
}
