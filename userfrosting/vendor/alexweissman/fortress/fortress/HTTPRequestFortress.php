<?php

namespace Fortress;

/*
 * A server-side filter for HTTP requests.  Sanitizes and validates GET and POST data, generates error messages, adds error messages to an error stream, and redirects and/or returns a JSON error object.
 *
 */

class HTTPRequestFortress {

    public static $message_stream =  null;    // The message stream (will be stored in the $_SESSION variable)

    protected $_validator;                        // A valid ServerSideValidatorInterface object
    protected $_sanitizer;                        // A valid DataSanitizerInterface object

    protected $_request_method = "post";  // "get" or "post"
    protected $_followup_uri = null;      // A URI to redirect to when the request is finished being processed, either on error or success
    protected $_ajax = false;             // true if this is an AJAX request, false otherwise
    
    protected $_data = [];                // Gets set to the POST or GET request data
    protected $_schema;                   // A valid RequestSchema object
    
    public function __construct($request_method = "post", $schema = null, $followup_uri = null, $locale = "en_US") {
        // Set the schema
        $this->setSchema($schema);
        
        // Set the followup URI.
        $this->setFollowupURI($followup_uri);
        
        // Set the request method
        $request_method = strtolower($request_method);
        if (in_array($request_method, ["post", "get"]))
            $this->_request_method = $request_method;
        else
            throw new \Exception("$request_method must be 'get' or 'post'.");
    
        // Set up the message stream
        if (!isset(self::$message_stream))
            throw new \Exception("Please set a message stream using setMessageStream!");
    
        // Check that the submitted request method matches the parameter request method.
        if ($_SERVER['REQUEST_METHOD'] != strtoupper($this->_request_method)) {
            $this->addMessage("danger", "Invalid request method: request method must be '{$this->_request_method}'.");
            $this->raiseFatalError();
        }
        
        // Set data array
        if ($request_method == "post")
            $this->_data = $_POST;
        else
            $this->_data = $_GET;
            
        // Determine whether this request is an ajax request, based on the `ajaxMode` parameter
        if (isset($this->_data['ajaxMode']) and $this->_data['ajaxMode'] == "true" ){
            $this->_ajax = true;
        } else {
            $this->_ajax = false;
        }
    
        // Construct default sanitizer and validators
        $this->_sanitizer = new DataSanitizer($schema);
        $this->_validator = new ServerSideValidator($schema, $locale);
       
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
    
    /* For non-ajax requests, automatically redirect to this URI after completion */
    public function setFollowupURI($uri){
        return $this->_followup_uri = $uri;
    }
    

    // Get the AJAX request mode.
    public function getAjaxMode(){
        return $this->_ajax;
    }    

    /* Get the data for this request, in its current state. */
    public function data(){
        return $this->_data;
    }
    
    /* Sanitize all fields and optionally add any error messages to the global message stream. */
    public function sanitize($reportErrors = true){
        $this->_data = $this->_sanitizer->sanitize($this->_data);
        // TODO: Implement sanitizer errors
    }
    
    /* Validate all fields and optionally add any error messages to the global message stream. */
    public function validate($reportErrors = true, $haltOnErrors = true){
        $this->_validator->validate($this->_data); 
        if ($reportErrors) {
            if (count($this->_validator->errors()) > 0) {	
                foreach ($this->_validator->errors() as $idx => $field){
                    foreach($field as $eidx => $error) {
                        self::$message_stream->addMessage("danger", $error);
                    }
                }
            }
        }
        if (count($this->_validator->errors()) > 0) {
            if ($haltOnErrors)
                $this->raiseFatalError();
            else
                return false;
        }
        return true;
    }    

    // Raise a fatal error, performing appropriate action and halting the script
    public function raiseFatalError() {
        if ($this->_ajax) {
            echo json_encode(["errors" => 1, "successes" => 0]);
        } else {      
            if ($this->_followup_uri != null) {
                header('Location: ' . $this->_followup_uri);
            }
        }
        exit();  
    }

    // Raise a success, rperforming appropriate action and halting the script 
    public function raiseSuccess(){
        if ($this->_ajax) {
          echo json_encode(["errors" => 0, "successes" => 1]);
        } else {
            if ($this->_followup_uri != null) {
                header('Location: ' . $this->_followup_uri);
            }
        }
        exit();    
    }
    
    /* Set up a persistent message stream by specifying a key in the Fortress "namespace" of $_SESSION. */
    public static function setMessageStream($stream_name){
        // Create message stream if it doesn't already exist
        if (!isset($_SESSION['Fortress']))
            $_SESSION['Fortress'] = [];
            
        if (!isset($_SESSION['Fortress'][$stream_name]))
            $_SESSION['Fortress'][$stream_name] = new MessageStream($stream_name);
        
        self::$message_stream = $_SESSION['Fortress'][$stream_name];
        
    }
}
    
?>