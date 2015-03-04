<?php

namespace UserFrosting;

/* A static class used to load an includes schema for a given page. */
    
class PageSchema {
    
    // Load the include schema object for the specified page
    public static function load($page_name, $schema_path = null){
        // Set default schema file, if not specified.
        if (!$schema_path)
            $schema_path = FILE_SCHEMA_PAGE_DEFAULT;
            
        // Load the include manifest
        $schema = json_decode(file_get_contents($schema_path, FILE_USE_INCLUDE_PATH),true);
        if ($schema === null)
            throw new \Exception("Could not load schema file '$schema_path'.");
    
        // Find the page in the JSON include manifest    
        foreach ($schema as $name => $manifest_group){
            if (in_array($page_name, $manifest_group['pages'])){
                return $manifest_group;
            }
        }
        
        // Load default manifest if specified page not found
        return $schema['default'];
    }
}

?>