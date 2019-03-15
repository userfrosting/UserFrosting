<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Util;

use UserFrosting\Assets\AssetBundles\GulpBundleAssetsRawBundles;
use UserFrosting\Assets\Exception\InvalidBundlesFileException;

/**
 * RawAssetBundles Class
 *
 * Extends GulpBundleAssetsRawBundles with an extend method that merges the referenced asset bundles with special collision logic.
 *
 * @author Alex Weissman (https://alexanderweissman.com)
 */
class RawAssetBundles extends GulpBundleAssetsRawBundles
{
    /**
     * Extends the currently loaded bundles with another bundle schema.
     *
     * @param string $filePath
     *
     * @throws \UserFrosting\Support\Exception\FileNotFoundException if file cannot be found.
     * @throws \UserFrosting\Support\Exception\JsonException         if file cannot be parsed as JSON.
     * @throws InvalidBundlesFileException                           if unexpected value encountered.
     */
    public function extend($filePath)
    {
        if (!is_string($filePath)) {
            throw new \InvalidArgumentException('$filePath must of type string but was ' . gettype($filePath));
        }

        // Read file
        $schema = $this->readSchema($filePath, true);

        // Abort if no bundle is specified
        if ($schema['bundle'] === null) {
            return;
        }

        // Process bundles
        foreach ($schema['bundle'] as $bundleName => $_) {

            // Get collision setting.
            $collisionRule = $schema["bundle.$bundleName.options.sprinkle.onCollision"] ?: 'replace';

            // Handle CSS bundle
            $styles = $schema["bundle.$bundleName.styles"];
            if ($styles !== null) {
                // Attempt to add CSS bundle
                try {
                    $this->addWithCollisionRule($styles, $bundleName, $collisionRule, $this->cssBundles);
                } catch (\Exception $e) {
                    throw new InvalidBundlesFileException("Encountered issue processing styles property for '$bundleName' for file '$filePath'", 0, $e);
                }
            }

            // Handle JS bundle
            $scripts = $schema["bundle.$bundleName.scripts"];
            if ($scripts !== null) {
                // Attempt to add JS bundle
                try {
                    $this->addWithCollisionRule($scripts, $bundleName, $collisionRule, $this->jsBundles);
                } catch (\Exception $e) {
                    throw new InvalidBundlesFileException("Encountered issue processing scripts property for '$bundleName' for file '$filePath'", 0, $e);
                }
            }
        }
    }

    /**
     * Adds provided bundle to provided bundle store with collision rule respected.
     * @param  string|string[]       $bundle        Bundle to add.
     * @param  string                $name          Name of bundle provided.
     * @param  string                $collisionRule Rule to apply if collision is detected.
     * @param  string[string][]      $bundleStore   Place to add bundles (CSS or JS depending on provided store).
     * @throws \ErrorException       if collision rule is 'error' and bundle is already defined.
     * @throws \OutOfBoundsException if an invalid collision rule is provided.
     */
    protected function addWithCollisionRule(&$bundle, $bundleName, $collisionRule, &$bundleStore)
    {
        $standardisedBundle = $this->standardiseBundle($bundle);
        if (!array_key_exists($bundleName, $bundleStore)) {
            $bundleStore[$bundleName] = $standardisedBundle;
        } else {
            switch ($collisionRule) {
                case 'replace':
                    // Replaces the existing bundle.
                    $bundleStore[$bundleName] = $standardisedBundle;
                break;
                case 'merge':
                    // Merge with existing bundle.
                    foreach ($standardisedBundle as $assetPath) {
                        if (!in_array($assetPath, $bundleStore[$bundleName])) {
                            $bundleStore[$bundleName][] = $assetPath;
                        }
                    }
                break;
                case 'ignore':
                break;
                case 'error':
                    throw new \ErrorException("The bundle '$bundleName' is already defined.");
                break;
                default:
                    throw new \OutOfBoundsException("Invalid value '$collisionRule' provided for 'onCollision' key in bundle '$bundleName'.");
                break;
            }
        }
    }
}
