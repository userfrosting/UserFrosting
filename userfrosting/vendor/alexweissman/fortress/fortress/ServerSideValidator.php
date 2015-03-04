<?php

namespace Fortress;

interface ServerSideValidatorInterface {
    public function setSchema($schema);
    public function validate($data, $schemaRequired);
    public function data();
    public function errors();    
}

/* Loads validation rules from a schema and validates a target array of data.
*/
class ServerSideValidator extends \Valitron\Validator implements ServerSideValidatorInterface {

    protected $_schema;         // A valid RequestSchema object
    protected $_locale = "";
    
    public function __construct($schema, $locale = "en_US") {        
        // Set schema
        $this->setSchema($schema);
        $this->_locale = $locale;  
        // TODO: use locale to determine Valitron language
        
        // Construct the parent with an empty data array.
        parent::__construct([]);
    }
    
    /* Set the schema for this validator, as a valid RequestSchema object. */
    public function setSchema($schema){
        $this->_schema = $schema;
    }
    
    /* Validate the specified data against the schema rules. */
    public function validate($data, $schemaRequired = true){
        $this->_fields = $data;         // Setting the parent class Validator's field data.
        $this->generateSchemaRules();   // Build Validator rules from the schema.
        return parent::validate();      // Validate!
    }
    
    private function ruleWithMessage($rule, $message_set) {
        // Weird way to adapt with Valitron's funky interface
        $params = array_merge([$rule], array_slice(func_get_args(), 2));
        call_user_func_array([$this,"rule"], $params);
        // Set message.  Use Valitron's default message if not specified in the schema.
        if (!$message_set){
            $message_set = "'" . $params[1] . "' " . vsprintf(static::$_ruleMessages[$rule], array_slice(func_get_args(), 3));
        }
        $this->message($message_set);
    }
    
    /* Generate and add rules from the schema */
    private function generateSchemaRules(){
        foreach ($this->_schema->getSchema() as $field_name => $field){
            $validators = $field['validators'];
            foreach ($validators as $validator_name => $validator){
                if (isset($validator['message'])){
                    $message_set = MessageTranslator::translate($validator['message'], $validator);
                }else
                    $message_set = null;
                // Required validator
                if ($validator_name == "required"){
                    $this->ruleWithMessage("required", $message_set, $field_name);
                }
                // String length validator
                if ($validator_name == "length"){
                    if (isset($validator['min']) && isset($validator['max'])) {
                        $this->ruleWithMessage("lengthBetween", $message_set, $field_name, $validator['min'], $validator['max']);
                    } else {          
                        if (isset($validator['min'])) {
                            $this->ruleWithMessage("lengthMin", $message_set, $field_name, $validator['min']);
                        }
                        if (isset($validator['max'])) {
                            $this->ruleWithMessage("lengthMax", $message_set, $field_name, $validator['max']);
                        }
                    }
                }
                // Numeric range validator
                if ($validator_name == "range"){
                    if (isset($validator['min'])){
                        $this->ruleWithMessage("min", $message_set, $field_name, $validator['min']);
                    }               
                    if (isset($validator['max'])){
                        $this->ruleWithMessage("max", $message_set, $field_name, $validator['max']);
                    }
                }
                // Integer validator
                if ($validator_name == "integer"){
                    $this->ruleWithMessage("integer", $message_set, $field_name);
                }                  
                // Array validator
                if ($validator_name == "array"){
                    // For now, just check that it is an array.  Really we need a new validation rule here.
                    $this->ruleWithMessage("array", $message_set, $field_name);
                }
                // Email validator
                if ($validator_name == "email"){
                    $this->ruleWithMessage("email", $message_set, $field_name);
                }            
                // Match another field
                if ($validator_name == "matches"){
                    $this->ruleWithMessage("equals", $message_set, $field_name, $validator['field']);
                }
            }
        }
    }    
}

?>