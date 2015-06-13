<?php

namespace UserFrosting;

/* A class representing the css and js to load for a given page. */
/* For minification, the `build` tool (TODO) will need to be re-run any time a CSS/JS include is added or removed, including those from plugin installation */
    
class PageSchema {

    protected $_css_uri;    // the root URI of the css directory
    protected $_css_path;   // the root filesystem path of the css directory
    protected $_js_uri;     // the root URI of the js directory
    protected $_js_path;    // the root filesystem path of the js directory

    protected $_css_includes = [];           // An multidimensional array of css includes, keyed by page group name, each which contains a list of CSS files
    protected $_js_includes_top = [];        // An multidimensional array of js includes, keyed by page group name, each which contains a list of JS files
    protected $_js_includes_bottom = [];     // An multidimensional array of js includes, keyed by page group name, each which contains a list of JS files
    
    public function __construct($css_uri, $css_path, $js_uri, $js_path){
        $this->_css_uri =  $css_uri;
        $this->_css_path = $css_path;
        $this->_js_uri =  $js_uri;
        $this->_js_path = $js_path;        
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
        // Check if the specified group actually exists, otherwise use the common minified file.
        if (isset($this->_css_includes[$group_name]))
            $minfile = "min/$group_name.min.css";
        else
            $minfile = "min/common.min.css";
        return $this->mergeIncludes($this->_css_uri, $this->_css_includes, $group_name, $minfile , $minify);
    }
    
   // Get the header JS includes for a specified page group
    public function getJSTopIncludes($group_name = "common", $minify = false) {
        // Check if the specified group actually exists, otherwise use nothing.
        if (isset($this->_js_includes_top[$group_name]))
            $minfile = "min/$group_name-top.min.js";
        else {
            $minfile = "";
            $minify = false;
        }
        return $this->mergeIncludes($this->_js_uri, $this->_js_includes_top, $group_name, $minfile, $minify);
    }    

    // Get the footer JS includes for a specified page group
    public function getJSBottomIncludes($group_name = "common", $minify = false) {
        // Check if the specified group actually exists, otherwise use the common minified file.
        if (isset($this->_js_includes_bottom[$group_name]))
            $minfile = "min/$group_name-bottom.min.js";
        else
            $minfile = "min/common-bottom.min.js";
        return $this->mergeIncludes($this->_js_uri, $this->_js_includes_bottom, $group_name, $minfile, $minify);
    }
    
    private function mergeIncludes($root, $raw, $group_name, $minfile, $minify = false, $include_externals = true){
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
            if ($include_externals) {
                foreach ($includes as $path){
                    if (strpos($path, 'http') === 0)
                        $includes_parsed[] = $path;
                }
            }
        } else {        
            $includes_parsed = [];
            foreach ($includes as $path){
                if (strpos($path, 'http') === 0) {
                    if ($include_externals)
                        $includes_parsed[] = $path;
                } else
                    $includes_parsed[] = $root . $path;
            }
        }
        return $includes_parsed;
    }
    
    
    // Builds the minified CSS and JS files for the site.  This function uses the Yahoo User Interface (YUI) interface (http://yui.github.io/yuicompressor/) to compress and minify the files as specified in this PageSchema object.
    public function build($debug = false){
        // Determine path to YUI jar file
        $yui = __DIR__ . "/yuicompressor-2.4.8.jar";      
        
        /*
         * In XAMPP and Mac OSX, by default Apache is run under the user 'daemon'.  To grant proper write permissions, run the following shell command:
         * `sudo chown -R daemon:daemon <write_path>`
         * This will grant ownership of the path to the web server.
         */
        $web_user = trim(`whoami`);
         
        if ($debug) {
            error_log("Running as user: $web_user");
            error_log("YUI compressor is in $yui");
        }
        
        // For each manifest group, build the corresponding minified, bundled (concatenated) JS and CSS files
        
        // Build CSS
        foreach ($this->_css_includes as $group_name => $group){
            if ($debug) {
                error_log("Building CSS for page group '$group_name'...");
            }
            $this->buildGroup($yui, $this->_css_path, $this->_css_includes, $group_name, "$group_name.min.css", $debug);
        }
        
        // Build head JS
        foreach ($this->_js_includes_top as $group_name => $group){
            if ($debug) {
                error_log("Building top JS for page group '$group_name'...");
            }
            $this->buildGroup($yui, $this->_js_path, $this->_js_includes_top, $group_name, "$group_name-top.min.js", $debug);
        }

        // Build footer JS
        foreach ($this->_js_includes_bottom as $group_name => $group){
            if ($debug) {
                error_log("Building bottom JS for page group '$group_name'...");
            }
            $this->buildGroup($yui, $this->_js_path, $this->_js_includes_bottom, $group_name, "$group_name-bottom.min.js", $debug);
        }
    }
    
    private function buildGroup($yui, $path_root, $includes, $group_name, $minfile, $debug = false){
        $paths = $this->mergeIncludes($path_root . "/", $includes, $group_name, "", false, false);
        
        // Test permissions on writing to min file:
        $output_dir = $path_root . "/min/";
        $output_file = $output_dir . $minfile;
        $this->write_minified_file($output_dir, $output_file, []);

        // Each file will be minified, and the result appended to the output array
        $group_arr = [];
        foreach($paths as $path){
            if ($debug) {
                error_log("----Minifying file '$path'");
            }
            
            // This will let us run the compressor and detect any errors
            $descriptorspec = array(
                0 => array("pipe", "r"),  // stdin
                1 => array("pipe", "w"),  // stdout
                2 => array("pipe", "w"),  // stderr
            );
            
            $process = proc_open("export DYLD_LIBRARY_PATH=''; java -jar $yui $path", $descriptorspec, $pipes);
            
            $stdout = stream_get_contents($pipes[1]);
            fclose($pipes[1]);
            
            $stderr = stream_get_contents($pipes[2]);
            fclose($pipes[2]);
            
            // Throw exception if there was an error
            if ($stderr){
                throw new \Exception("Unable to minify '$path'.  YUI error message:\n" . $stderr);
            }
            
            // If successful, ppend this minified content to the group file
            $group_arr[] = $stdout;
        }
        
        // Write minified CSS to the output file
        if ($debug) {
            error_log("--Attempting to write to file '$output_file'...");
        }
        return $this->write_minified_file($output_dir, $output_file, $group_arr);  
    }
        
    private function write_minified_file($output_dir, $output_file, $content){
        try {
            file_put_contents($output_file, implode("\n", $content));
            return true;
        } catch (\Exception $e){
            $web_user = trim(`whoami`);
            throw new \Exception("Access denied to write to '$output_file'.  Be sure that the directory exists, and that the user '$web_user' has permission to write to this directory.  To grant write access in OSX/Linux/etc, try running the chown command: 'sudo chown -R $web_user:$web_user $output_dir'.");
        }
    }    
    
}
