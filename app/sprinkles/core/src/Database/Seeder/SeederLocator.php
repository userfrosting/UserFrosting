<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */
namespace UserFrosting\Sprinkle\Core\Database\Seeder;

use Illuminate\Support\Str;
use UserFrosting\UniformResourceLocator\Resource;
use UserFrosting\UniformResourceLocator\ResourceLocator;

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
     * @var ResourceLocator The Sprinkle manager service
     */
    protected $locator;

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
     *    Returm a list of all available seeds for a specific sprinkle
     *
     *    @param  string $sprinkleName The sprinkle name
     *    @return array The list of available seed classes
     */
    public function getSeedersForSprinkle($sprinkleName)
    {
        return $this->loadSeeders($this->locator->listResources("seeds://$sprinkleName/"));
    }

    /**
     *    Loop all the available sprinkles and return a list of their seeds
     *
     *    @return array An array of all the seed classes found for every sprinkle
     */
    public function getSeeders()
    {
        return $this->loadSeeders($this->locator->listResources('seeds://'));
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
        $seedResource = $this->locator->getResource("seeds://$name.php");

        // Make sure we found something
        if (!$seedResource) {
            throw new \Exception("Seed $name not found");
        }

        // Return the seed info
        return $this->getSeedDetails($seedResource);
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
