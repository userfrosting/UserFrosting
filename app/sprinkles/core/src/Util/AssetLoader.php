<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @package   userfrosting/assets
 * @link      https://github.com/userfrosting/assets
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */
namespace UserFrosting\Sprinkle\Core\Util;

use UserFrosting\Assets\Util\MimeType;
use UserFrosting\Support\Util\Util;

/**
 * Asset loader class.
 *
 * Loads an asset from the filesystem.
 * @author Alex Weissman (https://alexanderweissman.com)
 * @deprecated
 */
class AssetLoader
{
    /**
     * @var string The base filesystem path in which to look for the asset file.  Can be an absolute path or stream.
     */
    protected $basePath;

    /**
     * @var string The fully constructed path to the file.
     */
    protected $fullPath;

    /**
     * @var string A regex pattern to represent the allowed subset of paths under basePath which are accessible.
     */
    protected $pattern;

    /**
     * Create a new AssetLoader object.
     *
     * @param string $basePath
     * @param string $pattern
     */
    public function __construct($basePath, $pattern)
    {
        $this->basePath = rtrim($basePath, "/\\") . '/';

        $this->pattern = $pattern;

        $this->fullPath = '';
    }

    /**
     * Compute the full filesystem path for the specified relative path (usually extracted from a URL).
     *
     * Also checks to make sure that the file actually exists.
     * @param string $relativePath
     * @return bool True if the file exists, false otherwise
     */
    public function loadAsset($relativePath)
    {
        // 1. Remove any query string
        $relativePath = preg_replace('/\?.*/', '', $relativePath);

        // 2. Normalize path, to prevent directory traversal
        $relativePath = Util::normalizePath($relativePath);

        // 3. Check that the beginning of the path matches the allowed paths pattern
        if (!preg_match($this->pattern, $relativePath)) {
            return false;
        }

        // 4. Build full path to file
        $this->fullPath = $this->basePath . $relativePath;

        // Return false if file does not exist
        if (!file_exists($this->fullPath)) {
            return false;
        }

        return true;
    }

    /**
     * Get the raw contents of the currently targeted file.
     *
     * @return string
     */
    public function getContent()
    {
        return file_get_contents($this->fullPath);
    }

    /**
     * Get the length in bytes of the currently targeted file.
     *
     * @return int
     */
    public function getLength()
    {
        return filesize($this->fullPath);
    }

    /**
     * Get the best-guess MIME type of the currently targeted file, based on the file extension.
     *
     * @return string
     */
    public function getType()
    {
        return MimeType::detectByFilename($this->fullPath);
    }
}