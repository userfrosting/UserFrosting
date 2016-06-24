<?php

/**
 * RequestSchema Class
 *
 * Represents a schema for an HTTP request, compliant with the WDVSS standard (https://github.com/alexweissman/wdvss)
 *
 * @package userfrosting/fortress
 * @author Alex Weissman
 * @link https://alexanderweissman.com
 * @license MIT
 */
namespace UserFrosting\Fortress;

use UserFrosting\Support\Exception\FileNotFoundException;
use UserFrosting\Support\Exception\JsonException;

class RequestSchema {

    /**
     * @var array The schema, as a dictionary of field names -> field properties
     */
    protected $schema = [];  

    /**
     * Loads the request schema from a file.
     *
     * @param string $file The full path to the file containing the [WDVSS schema](https://github.com/alexweissman/wdvss).
     * @throws Exception The file does not exist or is not a valid JSON schema.
     */    
    public function __construct($file)
    {
        $this->loadSchema($file);
    }
    
    /**
     * Get the schema, as an associative array.
     *
     * @return array The schema data.
     */
    public function getSchema()
    {
        return $this->schema;
    }

    /**
     * Load a schema from a JSON file.
     *
     * @param string $file Path to the schema file.
     */
    public function loadSchema($file)
    {
        $doc = file_get_contents($file);
        if ($doc === false)
            throw new FileNotFoundException("The schema '$file' could not be found.");
        
        $schema = json_decode($doc, true);
        if ($schema === null) {
            throw new JsonException("The schema '$file' does not contain a valid JSON document.  JSON error: " . json_last_error());
        }
        
        $this->schema = $schema;
    }    
    
    /**
     * Set the default value for a specified field.  
     *
     * If the specified field does not exist in the schema, add it.  If a default already exists for this field, replace it with the value specified here.
     * @param string $field The name of the field (e.g., "user_name")
     * @param string $value The new default value for this field.
     * @return RequestSchema This schema object.
     */
    public function setDefault($field, $value)
    {
        if (!isset($this->schema[$field]))
            $this->schema[$field] = [];
        $this->schema[$field]['default'] = $value;
        
        return $this;
    }
        
    /**
     * Adds a new validator for a specified field.  
     *
     * If the specified field does not exist in the schema, add it.  If a validator with the specified name already exists for the field,
     * replace it with the parameters specified here.
     * @param string $field The name of the field for this validator (e.g., "user_name")
     * @param string $validator_name A validator rule, as specified in https://github.com/alexweissman/wdvss (e.g. "length")
     * @param array $parameters An array of parameters, hashed as parameter_name => parameter value (e.g. [ "min" => 50 ])
     * @return RequestSchema This schema object.
     */
    public function addValidator($field, $validator_name, $parameters = [])
    {
        if (!isset($this->schema[$field]))
            $this->schema[$field] = [];
        if (!isset($this->schema[$field]['validators']))
            $this->schema[$field]['validators'] = [];
        $this->schema[$field]['validators'][$validator_name] = $parameters;
        
        return $this;
    }

    /**
     * Set a sequence of transformations for a specified field.  
     *
     * If the specified field does not exist in the schema, add it. 
     * @param string $field The name of the field for this transformation (e.g., "user_name")
     * @param string[] $transformations An array of transformations, as specified in https://github.com/alexweissman/wdvss (e.g. "purge")
     * @return RequestSchema This schema object.
     */
    public function setTransformations($field, $transformations = [])
    {
        if (!isset($this->schema[$field]))
            $this->schema[$field] = [];
        $this->schema[$field]['transformations'] = $transformations;
        
        return $this;
    }    
}
