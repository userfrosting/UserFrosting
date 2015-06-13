<?php

namespace UserFrosting;

/* A class representing the css and js to load for a given page. */
/* For minification, the `build` tool (TODO) will need to be re-run any time a CSS/JS include is added or removed, including those from plugin installation */
    
class PageSchema {

    protected $_css_root;
    protected $_js_root;

    protected $_css_includes;           // An multidimensional array of css includes, keyed by page group name, each which contains a list of CSS files
    protected $_js_includes_top;        // An multidimensional array of js includes, keyed by page group name, each which contains a list of JS files
    protected $_js_includes_bottom;     // An multidimensional array of js includes, keyed by page group name, each which contains a list of JS files
    
    public function __construct($css_root, $js_root){
        $this->_css_root = $css_root;
        $this->_js_root = $js_root;
    }
    
    // Register a CSS include for a specified page group
    public function registerCSS($group_name, $path){
        if (!isset($this->_css_includes[$group_name]))
            $this->_css_includes[$group_name] = [];
            
        if (!in_array($path, $this->_css_includes))
            $this->_css_includes[$group_name][] = $path;
    }

    // Register a JS include for a specified page group
    public function registerJS($group_name, $path, $position = "bottom"){
        if ($position == "bottom"){
            if (!isset($this->_js_includes_bottom[$group_name]))
                $this->_js_includes_bottom[$group_name] = [];
            
            if (!in_array($path, $this->_js_includes_bottom[$group_name]))
                $this->_js_includes_bottom[$group_name][] = $path;
        } else if ($position == "top"){
            if (!isset($this->_js_includes_top[$group_name]))
                $this->_js_includes_top[$group_name] = [];
            
            if (!in_array($path, $this->_js_includes_top[$group_name]))    
                $this->_js_includes_top[$group_name][] = $path;
        } else {
            throw new \Exception("'position' must be either 'top' or 'bottom'.");
        }
    }

    // Get the CSS includes for a specified page group
    public function getCSSIncludes($group_name = "common", $minify = false) {
        return $this->mergeIncludes($this->_css_root, $this->_css_includes, $group_name, "min/$group_name.min.css",$minify);
    
    }
    
   // Get the header JS includes for a specified page group
    public function getJSTopIncludes($group_name = "common", $minify = false) {
        return $this->mergeIncludes($this->_js_root, $this->_js_includes_top, $group_name, "min/$group_name-top.min.js", $minify);
    }    

    // Get the footer JS includes for a specified page group
    public function getJSBottomIncludes($group_name = "common", $minify = false) {
        return $this->mergeIncludes($this->_js_root, $this->_js_includes_bottom, $group_name, "min/$group_name-bottom.min.js", $minify);
    }
    
    private function mergeIncludes($root, $raw, $group_name, $minfile, $minify = false){
        // Combine the common and group-specific includes    
        if (isset($raw["common"]))
            $includes = $raw["common"];
        else
            $includes = [];
        if ($group_name != "common" && isset($raw[$group_name]))
            $includes = array_merge($includes, $raw[$group_name]);
 
        // For minified, replace with minified file but we still need to include any external includes
        if ($minify){
            $includes_parsed = [$root . $minfile];
            // Include external files
            foreach ($includes as $path){
                if (strpos($path, 'http') === 0)
                    $includes_parsed[] = $path;
            }
            
        } else {        
            $includes_parsed = [];
            foreach ($includes as $path){
                if (strpos($path, 'http') === 0)
                    $includes_parsed[] = $path;
                else
                    $includes_parsed[] = $root . $path;
            }
            
            return $includes_parsed;
        }
    }
}
