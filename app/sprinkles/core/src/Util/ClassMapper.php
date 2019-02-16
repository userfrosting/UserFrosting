<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Util;

/**
 * UserFrosting class mapper.
 *
 * This creates an abstraction layer for overrideable classes.
 * For example, if we want to replace usages of the User class with MyUser, this abstraction layer handles that.
 *
 * @author Alex Weissman (https://alexanderweissman.com)
 * @author Roger Ardibee
 */
class ClassMapper
{
    /**
     * @var array Mapping of generic class identifiers to specific class names.
     */
    protected $classMappings = [];

    /**
     * Creates an instance for a requested class identifier.
     *
     * @param string $identifier The identifier for the class, e.g. 'user'
     * @param mixed  ...$arg     Whatever needs to be passed to the constructor.
     */
    public function createInstance($identifier)
    {
        $className = $this->getClassMapping($identifier);

        $params = array_slice(func_get_args(), 1);

        return new $className(...$params);
    }

    /**
     * Gets the fully qualified class name for a specified class identifier.
     *
     * @param  string $identifier
     * @return string
     */
    public function getClassMapping($identifier)
    {
        if (isset($this->classMappings[$identifier])) {
            return $this->classMappings[$identifier];
        } else {
            throw new \OutOfBoundsException("There is no class mapped to the identifier '$identifier'.");
        }
    }

    /**
     * Assigns a fully qualified class name to a specified class identifier.
     *
     * @param  string      $identifier
     * @param  string      $className
     * @return ClassMapper
     */
    public function setClassMapping($identifier, $className)
    {
        // Check that class exists
        if (!class_exists($className)) {
            throw new BadClassNameException("Unable to find the class '$className'.");
        }

        $this->classMappings[$identifier] = $className;

        return $this;
    }

    /**
     * Call a static method for a specified class.
     *
     * @param string $identifier The identifier for the class, e.g. 'user'
     * @param string $methodName The method to be invoked.
     * @param mixed  ...$arg     Whatever needs to be passed to the method.
     */
    public function staticMethod($identifier, $methodName)
    {
        $className = $this->getClassMapping($identifier);

        $params = array_slice(func_get_args(), 2);

        return $className::$methodName(...$params);
    }
}
