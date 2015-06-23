<?php

namespace Fortress;

/**
 * HTTPRequestFortress Class
 *
 * A server-side filter for HTTP requests, based on the [WDVSS standard](https://github.com/alexweissman/wdvss).
 * Sanitizes and validates GET and POST data, sets default values, generates error messages, adds error messages to an error stream, and redirects and/or returns a JSON error object.
 * 
 * @package Fortress
 * @author Alex Weissman
 * @link http://alexanderweissman.com
 * @todo Remove sanitization features.  Sanitization should be done upon retrieval, not storage!  We can still do whitelisting and default values, though.
 * @todo Allow validation rules to be set as server-side only, client-side only, or both. (Default is both).
 */
class HTTPRequestFortress {

    /**
     * @var MessageStream
     */
    protected $_message_stream =  null;
    
    /**
     * @var ServerSideValidatorInterface
     */    
    protected $_validator; 

    /**
     * @var DataSanitizerInterface
     */
    protected $_sanitizer; 
    
    /**
     * @var RequestSchema
     */    
    protected $_schema;
    
    /**
     * @var array Gets set to the POST or GET request data
     */    
    protected $_data = [];
    
    /**
     * Create a new HTTPRequestFortress.
     *
     * @param MessageStream $message_stream The MessageStream to add validation and sanitization error messages to.
     * @param RequestSchema $schema A RequestSchema object, containing the validation and sanitization rules.
     * @param array $data The array of raw request data.
     */  
    public function __construct($message_stream, $schema = null, $data = []) {
        // Set the schema
        $this->setSchema($schema);
    
        // Set the message stream
        $this->setMessageStream($message_stream);
        
        // Set data
        $this->_data = $data;
    
        // Construct default sanitizer and validators
        $this->_sanitizer = new DataSanitizer($schema);
        $this->_validator = new ServerSideValidator($schema, $this->_message_stream->translator());
       
    }
    
    /**
     * Remove the specified fields from the request data.
     *
     * @param array $fields An array of field names to remove from the data array.
     */
    public function removeFields($fields){
        foreach ($fields as $idx => $field){
            unset($this->_data[$field]);
        }
    }

    /**
     * Set the message stream, as a valid MessageStream object.
     *
     * @param MessageStream $stream The MessageStream to add validation and sanitization error messages to.
     */ 
    public function setMessageStream($stream){
        return $this->_message_stream = $stream;
    }
    
    /**
     * Set the WDVSS schema, as a valid RequestSchema object.
     *
     * @param RequestSchema $schema A RequestSchema object, containing the validation and sanitization rules.
     */    
    public function setSchema($schema){
        return $this->_schema = $schema;
    }
    
    /**
     * Set the validator, as a valid ServerSideValidatorInterface instance.
     *
     * @param ServerSideValidatorInterface $validator The validator to use when validating the request data.
     */ 
    public function setValidator($validator) {
        if ($validator instanceof ServerSideValidatorInterface)
            $this->_validator = $validator;
        else
            throw new \Exception("$validator must be a valid instance of ServerSideValidatorInterface.");
    }    
        
    /**
     * Sanitize all fields and optionally add any error messages to the global message stream.
     *
     * @param boolean $report_errors[optional] Add validation error messages to the stream if true (not yet supported).
     * @param string $on_unexpected_var[optional] Determines what to do when a field is encountered that is not in the schema.  Set to one of:
     * "allow": Treat the field as any other, applying the "purge" sanitization filter.
     * "error": Raise an exception.
     * "skip" (default): Quietly ignore the field.  It will not be part of the sanitized data array.
     * @return array The array of sanitized data, mapping field names => values.     
     */
    public function sanitize($report_errors = true, $on_unexpected_var = "skip"){
        return $this->_data = $this->_sanitizer->sanitize($this->_data, $on_unexpected_var);
        // TODO: Implement sanitizer errors
    }
    
    /**
     * Validate all fields and optionally add any error messages to the global message stream.
     *
     * @param boolean $report_errors[optional] Add validation error messages to the stream if true.
     * @return boolean True if validation succeeded, false if any of the rules failed.
     */
    public function validate($report_errors = true){
        $this->_validator->validate($this->_data); 
        if ($report_errors) {
            if (count($this->_validator->errors()) > 0) {	
                foreach ($this->_validator->errors() as $idx => $field){
                    foreach($field as $eidx => $error) {
                        $this->_message_stream->addMessage("danger", $error);
                    }
                }
            }
        }
        if (count($this->_validator->errors()) > 0) {
            return false;
        }
        return true;
    }

    /**
     * Get the data for this request, in its current state.
     *
     * @return array The data, after any santization, validation, or removeField actions have been carried out.
     */
    public function data(){
        return $this->_data;
    }
}
