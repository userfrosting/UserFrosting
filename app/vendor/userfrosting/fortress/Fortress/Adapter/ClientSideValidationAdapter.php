<?php

/**
 * ClientSideValidationAdapter Class
 *
 * Loads validation rules from a schema and generates client-side rules compatible with a particular client-side (usually Javascript) plugin.
 *
 * @package userfrosting/fortress
 * @author Alex Weissman
 * @link https://alexanderweissman.com
 * @license MIT
 */
namespace UserFrosting\Fortress\Adapter;

abstract class ClientSideValidationAdapter
{

    /**
     * @var RequestSchema
     */
    protected $schema;

    /**
     * @var MessageTranslator
     */    
    protected $translator; 

    /**
     * Create a new client-side validator.
     *
     * @param RequestSchema $schema  A RequestSchema object, containing the validation rules.
     * @param MessageTranslator $translator A MessageTranslator to be used to translate message ids found in the schema.     
     */  
    public function __construct($schema, $translator)
    {        
        // Set schema
        $this->setSchema($schema);
        
        // Set translator
        $this->setTranslator($translator);
    }
    
    /**
     * Set the schema for this validator, as a valid RequestSchema object.
     *
     * @param RequestSchema $schema A RequestSchema object, containing the validation rules.
     */
    public function setSchema($schema)
    {
        $this->schema = $schema;
    }

    /**
     * Set the translator for this validator, as a valid MessageTranslator object.
     *
     * @param MessageTranslator $translator A MessageTranslator to be used to translate message ids found in the schema.
     */    
    public function setTranslator($translator)
    {
        $this->translator = $translator;
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
