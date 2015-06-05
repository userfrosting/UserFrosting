<?php

    require_once("../resources/config-site.php"); 
    
    define("PATH_JS_ROOT", "../public/js/");
    define("PATH_CSS_ROOT", "../public/css/");
    define("FILE_SCHEMA_PAGE_DEFAULT", "../resources/schema/pages/pages.json");
    
    // Builds the minified CSS and JS files for the site.

    /*
     * In XAMPP and Mac OSX, by default Apache is run under the user 'daemon'.  To grant proper write permissions, run the following shell command:
     * `sudo chown -R daemon:daemon <write_path>`
     * This will grant ownership of the path to the web server.
     */
    echo "Running as user: ";
    echo `whoami`;
    echo "<br><br>";    
        
    // Get the manifest for the site
    $manifest_file = FILE_SCHEMA_PAGE_DEFAULT;
    
    echo "Loading manifest from $manifest_file...<br>";
    
    // Load the include manifest
    $manifest = json_decode(file_get_contents($manifest_file, FILE_USE_INCLUDE_PATH),true);
    if ($manifest === null){
        error_log(json_last_error());
        echo "Could not load manifest '$manifest_file'.  Please see the PHP error log.";
        exit();
    }
    
    echo "Manifest loaded.<br><br>";
    
    // For each manifest group, build the corresponding minified, concatenated JS and CSS files
    foreach ($manifest as $name => $manifest_group){
        echo "Building manifest '$name'...<br>";
        
        // Test permissions on JS and CSS directories:
        $output_js = $manifest_group['min']['js'][0];
        $js_minified_path = PATH_JS_ROOT . $output_js;
        $output_css = $manifest_group['min']['css'][0];
        $css_minified_path = PATH_CSS_ROOT . $output_css;
        if (write_minified_file($js_minified_path, []) === false)
            exit;
        if (write_minified_file($css_minified_path, [])  === false)
            exit;
        
        /***** JS *****/
        echo "--Creating bundled, minified JS...<br>";
        
        // Each file will be minified, and the result appended to the output array
        $output_arr = array();
        foreach($manifest_group['dev']['js'] as $js_file){
            echo "----Added file '$js_file'.<br>";
            $js_file_local = PATH_JS_ROOT . $js_file;
            exec("export DYLD_LIBRARY_PATH=''; java -jar yuicompressor-2.4.8.jar $js_file_local", $output_arr);
        }
        
        // Write minified JS to the output file
        echo "--Attempting to write to file '$output_js'...<br>";
        if (write_minified_file($js_minified_path, $output_arr) !== false)
            echo "SUCCESS!  Minified JS has been created in '$output_js'<br>";
        
        /***** CSS *****/
        echo "--Creating bundled, minified CSS...<br>";    

        // Each file will be minified, and the result appended to the output array
        $output_arr = array();
        foreach($manifest_group['dev']['css'] as $css_file){
            echo "----Added file '$css_file'.<br>";
            $css_file_local = PATH_CSS_ROOT . $css_file;
            exec("export DYLD_LIBRARY_PATH=''; java -jar yuicompressor-2.4.8.jar $css_file_local", $output_arr);
            
        }
        
        // Write minified CSS to the output file
        echo "--Attempting to write to file '$output_css'...<br>";
        if (write_minified_file($css_minified_path, $output_arr) !== false)
            echo "SUCCESS!  Minified CSS has been created in '$output_css'<br>";
    }
    
function write_minified_file($path, $content){
    $success = file_put_contents($path, implode("\n", $content));
    if ($success === false){
        $web_user = trim(`whoami`);
        echo "<br>";
        echo "<b>Failed writing '$path'.  Be sure that the directory exists, and that the user '$web_user' has permission to write to this directory.<br>";
        echo "To grant write access in OSX/Linux/etc, try running the chown command:<br>";
        echo "sudo chown -R $web_user:$web_user $path</b><br>";
        echo "<br>";
    }
    return $success;
}

?>
