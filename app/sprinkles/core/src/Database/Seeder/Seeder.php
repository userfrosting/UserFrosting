<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Database\Seeder;

use Interop\Container\ContainerInterface;
use Illuminate\Support\Str;
use UserFrosting\UniformResourceLocator\Resource as ResourceInstance;

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
     * @var string $scheme The resource locator scheme
     */
    protected $scheme = 'seeds://';

    /**
     * Class Constructor
     *
     * @param ContainerInterface $ci
     */
    public function __construct(ContainerInterface $ci)
    {
        $this->ci = $ci;
    }

    /**
     * Loop all the available sprinkles and return a list of their seeds
     *
     * @return array An array of all the seed classes found for every sprinkle
     */
    public function getSeeds()
    {
        $seeds = $this->ci->locator->listResources($this->scheme, false, false);
        
        return $this->loadSeeders($seeds);
    }

    /**
     * Get a single seed info
     *
     * @param  string     $name The seed name
     * @throws \Exception If seed not found
     * @return array      The details about a seed file [name, class, sprinkle]
     */
    public function getSeed($name)
    {
        // Get seed resource
        $seedResource = $this->ci->locator->getResource($this->scheme . $name . '.php');

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
     * @param  string        $name The seed name
     * @throws \Exception    If class doesn't exist or is not right interface
     * @return SeedInterface The seed class instance
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
     * @param SeedInterface $seed The seed to execute
     */
    public function executeSeed(SeedInterface $seed)
    {
        $seed->run();
    }

    /**
     * Execute a seed based on it's name
     *
     * @param string $seedName
     */
    public function execute($seedName)
    {
        $seed = $this->getSeedClass($seedName);
        $this->executeSeed($seed);
    }

    /**
     * Process seeder Resource into info
     *
     * @param  array $seedFiles List of seeds file
     * @return array
     */
    protected function loadSeeders(array $seedFiles)
    {
        $seeds = [];
        foreach ($seedFiles as $seedFile) {
            $seeds[] = $this->getSeedDetails($seedFile);
        }

        return $seeds;
    }

    /**
     * Return an array of seed details inclusing the classname and the sprinkle name
     *
     * @param  ResourceInstance $file The seed file
     * @return array            The details about a seed file [name, class, sprinkle]
     */
    protected function getSeedDetails(ResourceInstance $file)
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
            'name'     => $name,
            'class'    => "\\UserFrosting\\Sprinkle\\$sprinkleName\\Database\\Seeds\\$className",
            'sprinkle' => $sprinkleName
        ];
    }
}
