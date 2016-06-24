<?php

namespace RocketTheme\Toolbox\ResourceLocator;

/**
 * Defines ResourceLocatorInterface.
 *
 * @package RocketTheme\Toolbox\ResourceLocator
 * @author RocketTheme
 * @license MIT
 */
interface ResourceLocatorInterface
{
    /**
     * Alias for findResource()
     *
     * @param $uri
     * @return string|bool
     */
    public function __invoke($uri);

    /**
     * Returns true if uri is resolvable by using locator.
     *
     * @param  string $uri
     * @return bool
     */
    public function isStream($uri);

    /**
     * @param  string $uri
     * @param  bool   $absolute
     * @param  bool   $first
     * @return string|bool
     */
    public function findResource($uri, $absolute = true, $first = false);

    /**
     * @param  string $uri
     * @param  bool   $absolute
     * @param  bool   $all
     * @return array
     */
    public function findResources($uri, $absolute = true, $all = false);
}
