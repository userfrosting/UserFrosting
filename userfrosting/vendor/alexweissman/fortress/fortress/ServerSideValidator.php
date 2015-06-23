<?php

namespace Fortress;

interface ServerSideValidatorInterface {
    public function setSchema($schema);
    public function validate($data);
    public function data();
    public function errors();    
}

/**
 * ServerSideValidator Class
 *
 * Loads validation rules from a schema and validates a target array of data.
 *
 * @package Fortress
 * @author Alex Weissman
 * @link http://alexanderweissman.com
 */
class ServerSideValidator extends \Valitron\Validator implements ServerSideValidatorInterface {

    /**
     * @var RequestSchema
     */  
    protected $_schema;

    /**
     * @var MessageTranslatorInterface
     */     
    protected $_translator;
    
    /** Create a new server-side validator.
     *
     * @param RequestSchema $schema A RequestSchema object, containing the validation rules.
     * @param MessageTranslator $translator A MessageTranslator to be used to translate message ids found in the schema.
     */    
    public function __construct($schema, $translator) {        
        // Set schema
        $this->setSchema($schema);
        
        // Set translator
        $this->_translator = $translator;
        // TODO: use locale of translator to determine Valitron language?
        
        // Construct the parent with an empty data array.
        parent::__construct([]);
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
     * Validate the specified data against the schema rules.
     *
     * @param array $data An array of data, mapping field names to field values.
     * @return boolean True if the data was successfully validated, false otherwise.
     */
    public function validate($data = []){
        $this->_fields = $data;         // Setting the parent class Validator's field data.
        $this->generateSchemaRules();   // Build Validator rules from the schema.
        return parent::validate();      // Validate!
    }
    
    /**
     * Add a rule to the validator, along with a specified error message if that rule is failed by the data.
     */
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
    
    /**
     *
     * Generate and add rules from the schema
     */
    private function generateSchemaRules(){
        foreach ($this->_schema->getSchema() as $field_name => $field){
            if (!isset($field['validators']))
                continue;
            $validators = $field['validators'];
            foreach ($validators as $validator_name => $validator){
                if (isset($validator['message'])){
                    $params = array_merge(["self" => $field_name], $validator);
                    $message_set = $this->_translator->translate($validator['message'], $params);
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
                // Integer validator
                if ($validator_name == "integer"){
                    $this->ruleWithMessage("integer", $message_set, $field_name);
                }                  
                // Numeric validator
                if ($validator_name == "numeric"){
                    $this->ruleWithMessage("numeric", $message_set, $field_name);
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
                // Negation of match another field
                if ($validator_name == "not_matches"){
                    $this->ruleWithMessage("different", $message_set, $field_name, $validator['field']);
                }
                // Check membership in array
                if ($validator_name == "member_of"){
                    $this->ruleWithMessage("in", $message_set, $field_name, $validator['values'], true);    // Strict comparison
                }
                // Negation of membership
                if ($validator_name == "not_member_of"){
                    $this->ruleWithMessage("notIn", $message_set, $field_name, $validator['values'], true);  // Strict comparison
                }
            }
        }
    }    
}

?>