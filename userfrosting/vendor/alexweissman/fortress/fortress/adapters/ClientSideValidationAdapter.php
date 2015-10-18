<?php

namespace Fortress;

/**
 * ClientSideValidationAdapter Class
 *
 * Loads validation rules from a schema and generates client-side rules compatible with a particular client-side (usually Javascript) plugin.
 *
 * @package Fortress
 * @author Alex Weissman
 * @link http://alexanderweissman.com
 */
abstract class ClientSideValidationAdapter {

    /**
     * @var RequestSchema
     */
    protected $_schema;

    /**
     * @var MessageTranslator
     */    
    protected $_translator; 

    /**
     * Create a new client-side validator.
     *
     * @param MessageTranslator $translator A MessageTranslator to be used to translate message ids found in the schema.     
     * @param RequestSchema $schema optional A RequestSchema object, containing the validation rules.
     */  
    public function __construct($translator, $schema = null) {        
        // Set schema
        if ($schema)
            $this->setSchema($schema);
        
        // Set translator
        $this->_translator = $translator;
    }
    
    /**
     * Set the schema for this validator, as a valid RequestSchema object.
     *
     * @param RequestSchema $schema A RequestSchema object, containing the validation rules.
     */
    public function setSchema($schema){
        $this->_schema = $schema;
    }

    /**
     * Generate and return the validation rules for this specific validation adapter.
     *
     * This method returns a collection of rules, in the format required by the specified plugin.
     * @param string $format The format in which to return the rules.  For example, "json" or "html5".
     * @param bool $string_encode In the case of JSON rules, specify whether or not to encode the result as a serialized JSON string.
     * @return mixed The validation rule collection.
     */    
    abstract public function rules($format = "json", $string_encode = true);
    
}
