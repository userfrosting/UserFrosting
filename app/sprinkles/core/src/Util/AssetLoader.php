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

    protected $basePath;

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
    
    public function getContent()
    {
        return file_get_contents($this->fullPath);
    }    
    
    public function getLength()
    {
        return filesize($this->fullPath);
    }
    
    public function getType()
    {
        $type = MimeType::detectByFilename($this->fullPath);
    }
}
