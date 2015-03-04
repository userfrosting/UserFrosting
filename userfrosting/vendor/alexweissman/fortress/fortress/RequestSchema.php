<?php

namespace Fortress;

/* Represents a request schema, compliant with the JSON Schema standard (http://json-schema.org/latest/json-schema-validation.html) */

class RequestSchema {

    protected $_schema = [];    // The schema, as a dictionary of field names -> field properties

    public function __construct($file){
        $this->_schema = json_decode(file_get_contents($file),true);
        if ($this->_schema === null) {
            throw new \Exception("The schema '$file' could not be loaded.  Check that it exists and is a valid JSON file.");
        }
    }
    
    public function getSchema(){
        return $this->_schema;
    }
}

?>
