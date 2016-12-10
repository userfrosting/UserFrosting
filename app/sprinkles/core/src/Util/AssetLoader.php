<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2013-2016 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */
namespace UserFrosting\Sprinkle\Core\Util;

/**
 * Asset loader class.
 *
 * Loads an asset from the filesystem.
 * @author Alex Weissman (https://alexanderweissman.com)
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
     * Create a new AssetLoader object.
     *
     * @param string $basePath
     */
    public function __construct($basePath = 'assets://')
    {
        $this->basePath = $basePath;

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
        // Remove any query string
        $relativePath = preg_replace('/\?.*/', '', $relativePath);

        // Find file
        $this->fullPath = $this->basePath . $relativePath;

        // Return 404 if file does not exist
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
