<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */
namespace UserFrosting\Sprinkle\Core\Util;

use Illuminate\Support\Str;
use UserFrosting\System\Sprinkle\SprinkleManager;

/**
 * ClassFinder Class
 * Finds a class accross sprinkles
 *
 * @author Louis Charette
 */
class ClassFinder
{
    /**
     * @var SprinkleManager The Sprinkle manager service
     */
    protected $sprinkleManager;

    /**
     *    Constructor
     *
     *    @param SprinkleManager $sprinkleManager
     */
    public function __construct(SprinkleManager $sprinkleManager)
    {
        $this->sprinkleManager = $sprinkleManager;
    }

    /**
     *    Finds a class across sprinkles. Will return the first instance found
     *    while respecting the sprinkle load order. Search is done after the `src/`
     *    dir. So to find `UserFrosting\Sprinkle\Core\Database\Models\Users`,
     *    search for `\Database\Models\Users`
     *
     *    @param  string $className The class name to find, including path inside `src/`
     *    @return string The fully qualified classname
     *    @throws \Exception If class not found
     */
    public function getClass($className)
    {
        $sprinkles = $this->getSprinkles();
        foreach ($sprinkles as $sprinkle) {

            // Format the sprinkle name for the namespace
            $sprinkle = Str::studly($sprinkle);

            // Build the class name and namespace
            $class = "\\UserFrosting\\Sprinkle\\$sprinkle\\$className";

            // Check if class exist.
            if (class_exists($class)) {
                return $class;
            }
        }

        //No class if found. Throw Exception
        $sprinklesString = implode(", ", $sprinkles);
        throw new \Exception("Class $className not found in sprinkles [$sprinklesString]");
    }

    /**
     *    Return an array of sprinkles respecting the load order
     *
     *    @return array
     */
    protected function getSprinkles()
    {
        $sprinkles = $this->sprinkleManager->getSprinkleNames();
        return array_reverse($sprinkles);
    }
}
