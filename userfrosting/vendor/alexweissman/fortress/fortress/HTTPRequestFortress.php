<?php

namespace Fortress;

/*
 * A server-side filter for HTTP requests.  Sanitizes and validates GET and POST data, generates error messages, adds error messages to an error stream, and redirects and/or returns a JSON error object.
 *
 */

class HTTPRequestFortress {

    protected $_message_stream =  null;    // A valid MessageStream object

    protected $_validator;                // A valid ServerSideValidatorInterface object
    protected $_sanitizer;                // A valid DataSanitizerInterface object
    
    protected $_data = [];                // Gets set to the POST or GET request data
    protected $_schema;                   // A valid RequestSchema object
    
    public function __construct($message_stream, $schema = null, $data = []) {
        // Set the schema
        $this->setSchema($schema);
    
        // Set the message stream
        $this->setMessageStream($message_stream);
        
        // Set data
        $this->_data = $data;
    
        // Construct default sanitizer and validators
        $this->_sanitizer = new DataSanitizer($schema);
        $this->_validator = new ServerSideValidator($schema);
       
    }
    
    /* Remove the specified fields from the request data. */
    public function removeFields($fields){
        foreach ($fields as $idx => $field){
            unset($this->_data[$field]);
        }
    }
    
    // Set the ServerSideValidatorInterface instance.
    public function setValidator($validator) {
        if ($validator instanceof ServerSideValidatorInterface)
            $this->_validator = $validator;
        else
            throw new \Exception("$validator must be a valid instance of ServerSideValidatorInterface.");
    }
    
    public function setSchema($schema){
        return $this->_schema = $schema;
    }
    
    public function setMessageStream($stream){
        return $this->_message_stream = $stream;
    }
    
    /* Get the data for this request, in its current state. */
    public function data(){
        return $this->_data;
    }
    
    /* Sanitize all fields and optionally add any error messages to the global message stream. */
    public function sanitize($reportErrors = true, $on_unexpected_var = "skip"){
        $this->_data = $this->_sanitizer->sanitize($this->_data, $on_unexpected_var);
        // TODO: Implement sanitizer errors
    }
    
    /* Validate all fields and optionally add any error messages to the global message stream. */
    public function validate($reportErrors = true){
        $this->_validator->validate($this->_data); 
        if ($reportErrors) {
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
}
    
?>