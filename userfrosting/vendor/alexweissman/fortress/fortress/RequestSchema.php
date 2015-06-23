<?php

namespace Fortress;

/**
 * RequestSchema Class
 *
 * Represents a schema for an HTTP request, compliant with the WDVSS standard (https://github.com/alexweissman/wdvss)
 *
 * @package Fortress
 * @author Alex Weissman
 * @link http://alexanderweissman.com
 */
class RequestSchema {

    /**
     * @var array The schema, as a dictionary of field names -> field properties
     */
    protected $_schema = [];  

    /**
     * Loads the request schema from a file.
     *
     * @param string $file The full path to the file containing the [WDVSS schema](https://github.com/alexweissman/wdvss).
     * @throws Exception The file does not exist or is not a valid JSON schema.
     */    
    public function __construct($file){
        $this->_schema = json_decode(file_get_contents($file),true);
        if ($this->_schema === null) {
            throw new Exception("Either the schema '$file' could not be found, or it does not contain a valid JSON document: " . json_last_error());
        }
    }
    
    /**
     * Get the schema, as an associative array.
     *
     * @return array The schema data.
     */
    public function getSchema(){
        return $this->_schema;
    }
}
