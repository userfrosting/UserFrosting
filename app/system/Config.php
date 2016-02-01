<?php

    namespace UserFrosting;
    
    class Config {
    
        protected $mode;
        protected $site_name;
        protected $values;
    
        public function __construct($mode = null, $site_name = null){
            $this->site_name = $site_name;
        
            // Look for the mode in the environment variable, if not otherwise set
            if (!$mode){
                if (isset($_ENV['UF_MODE']))
                    $mode = $_ENV['UF_MODE'];
                else
                    $mode = "development";
            }
        
            $this->mode = $mode;
        
            $config_dir = APP_DIR . '/' . CONFIG_DIR_NAME;
        
            // Get the system defaults
            $this->values = require_once $config_dir . '/default.php';
            
            // If we're in multisite mode, merge in any site specific default values
            if ($site_name){
                $config = require_once SITES_DIR . "/$site_name/" . CONFIG_DIR_NAME . '/default.php';
                $this->values = array_replace_recursive($this->values, $config);
            }
            
            // Merge in any system mode-specific values
            if (file_exists($config_dir . "/$mode.php")){
                $config = require_once $config_dir . '/' . $mode . '.php';
                $this->values = array_replace_recursive($this->values, $config);
            }
            
            // Finally, merge in any site-specific, mode-specific values
            if ($site_name){
                $site_dir = SITES_DIR . "/$site_name/" . CONFIG_DIR_NAME;
                if (file_exists($site_dir . "/$mode.php")) {
                    $config = require_once $site_dir . "/$mode.php";
                    $this->values = array_replace_recursive($this->values, $config);
                }
            }
            
            // Compute derived URIs
            $uri_public_root = $this->values['uri']['scheme'] . '://' . $this->values['uri']['host'];
            
            // Append port, if specified
            if (isset($this->values['uri']['port']) && ($this->values['uri']['port'] != ''))
                $uri_public_root .= ':' . $this->values['uri']['port'];
                
            // Append subpath, if specified
            if (isset($this->values['uri']['public_relative']) && ($this->values['uri']['public_relative'] != ''))
                $uri_public_root .= $this->values['uri']['public_relative'];
            
            // For derived values, the config file should override anything we compute here
            $this->values = array_replace_recursive([
                'uri' => [
                    'public'   =>  $uri_public_root,
                    'js'       =>  $uri_public_root . $this->values['uri']['js_relative'],
                    'css'      =>  $uri_public_root . $this->values['uri']['css_relative'],
                    'image'    =>  $uri_public_root . $this->values['uri']['image_relative']
                ]
            ], $this->values);
 
            // Compute site root (filesystem path to public web directory for this site)
            $site_root = $this->values['path']['document_root'] . $this->values['path']['public_relative'];
            
            $this->values = array_replace_recursive([
                'path' => [
                    'public'   => $site_root,
                    'js'       => $site_root . $this->values['path']['js_relative'],
                    'css'      => $site_root . $this->values['path']['css_relative']
                ]
            ], $this->values);
            
        }
        
        public function __isset($name) {
            if (in_array($name, [
                'mode',
                'site_name'
            ]))
                return true;
            return isset($this->values[$name]);
        }
        
        public function __get($name) {
            if ($name == 'mode')
                return $this->mode;
            elseif ($name == 'site_name')
                return $this->site_name;
            else
                return $this->values[$name];
        }
        
        public function get(){
            return $this->values;
        }
    }
    