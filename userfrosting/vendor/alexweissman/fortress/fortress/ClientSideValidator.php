<?php

namespace Fortress;

/**
 * ClientSideValidator Class
 *
 * Loads validation rules from a schema and generates client-side rules compatible with the [FormValidation](http://formvalidation.io) JS plugin.
 *
 * @package Fortress
 * @author Alex Weissman
 * @link http://alexanderweissman.com
 */
class ClientSideValidator {

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
     * @param RequestSchema $schema A RequestSchema object, containing the validation rules.
     * @param MessageTranslator $translator A MessageTranslator to be used to translate message ids found in the schema.
     */  
    public function __construct($schema, $translator) {        
        // Set schema
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
     * Generate FormValidation compatible rules from the specified RequestSchema, as a JSON document.  
     * See [this](http://formvalidation.io/getting-started/#calling-plugin) as an example of what this function will generate.
     * 
     * @param boolean $encode Specify whether to return a PHP array, or a JSON-encoded string.
     * @return string|array Returns either the array of rules, or a JSON-encoded representation of that array.
     */
    public function formValidationRulesJson($encode = true){
        $client_rules = [];
        $implicit_rules = [];
        foreach ($this->_schema->getSchema() as $field_name => $field){
            $client_rules[$field_name] = [];
            $client_rules[$field_name]['validators'] = [];
            if (isset($field['validators'])){
                $validators = $field['validators'];
                foreach ($validators as $validator_name => $validator){
                    $client_rules[$field_name]['validators'] = array_merge($client_rules[$field_name]['validators'], $this->transformValidator($validator_name, $validator));
                }
            }
        }
        if ($encode)
            return json_encode($client_rules, JSON_PRETTY_PRINT);
        else
            return $client_rules;
    }
   
    /**
     * Generate FormValidation compatible rules from the specified RequestSchema, as HTML5 `data-*` attributes.  
     * See [Setting validator options via HTML attributes](http://formvalidation.io/examples/attribute/) as an example of what this function will generate.
     * 
     * @return array Returns an array of rules, mapping field names -> string of data-* attributes, separated by spaces.
     * Example: `data-fv-notempty data-fv-notempty-message="The gender is required"`.
     */   
    public function formValidationRulesHtml5(){
        $client_rules = array();
        $implicit_rules = array();
        foreach ($this->_schema->getSchema() as $field_name => $field){
            $field_rules = "";
            $validators = $field['validators'];
            foreach ($validators as $validator_name => $validator){
                // Required validator
                if ($validator_name == "required"){
                    $prefix = "data-fv-notempty";
                    $field_rules .= $this->html5Attributes($validator, $prefix);
                }
                // String length validator
                if ($validator_name == "length"){
                    $prefix = "data-fv-stringlength";
                    $field_rules .= $this->html5Attributes($validator, $prefix);
                    if (isset($validator['min']))
                        $field_rules .= "$prefix-min={$validator['min']} ";
                    if (isset($validator['max']))
                        $field_rules .= "$prefix-max={$validator['max']} ";
                }
                // Numeric range validator
                if ($validator_name == "range"){
                    if (isset($validator['min']) && isset($validator['max'])){
                        $prefix = "data-fv-between";
                        $field_rules .= $this->html5Attributes($validator, $prefix);
                        $field_rules .= "$prefix-min={$validator['min']} ";
                        $field_rules .= "$prefix-max={$validator['max']} ";      
                    } else {
                        if (isset($validator['min'])){
                            $prefix = "data-fv-greaterthan";
                            $field_rules .= $this->html5Attributes($validator, $prefix);
                            $field_rules .= "$prefix-value={$validator['min']} ";
                        }
                  
                        if (isset($validator['max'])){
                           $prefix = "data-fv-lessthan";
                            $field_rules .= $this->html5Attributes($validator, $prefix);
                            $field_rules .= "$prefix-value={$validator['max']} ";
                        }
                    }
                }
                // Integer validator
                if ($validator_name == "integer"){
                    $prefix = "data-fv-integer";
                    $field_rules .= $this->html5Attributes($validator, $prefix);   
                }                  
                // Array validator
                if ($validator_name == "array"){
                    $prefix = "data-fv-choice";
                    $field_rules .= $this->html5Attributes($validator, $prefix);
                    if (isset($validator['min']))
                        $field_rules .= "$prefix-min={$validator['min']} ";
                    if (isset($validator['max']))
                        $field_rules .= "$prefix-max={$validator['max']} ";                    
                }
                // Email validator
                if ($validator_name == "email"){
                    $prefix = "data-fv-emailaddress";
                    $field_rules .= $this->html5Attributes($validator, $prefix); 
                }            
                // Match another field
                if ($validator_name == "matches"){
                    $prefix = "data-fv-identical";
                    if (isset($validator['field'])){
                        $field_rules .= "$prefix-field={$validator['field']} ";
                    } else {
                        return null;    // TODO: throw exception
                    }
                    
                    $field_rules = $this->html5Attributes($validator, $prefix);
                    // Generates validator for matched field
                    $implicit_rules[$validator['field']] = $field_rules;
                    $implicit_rules[$validator['field']] .= "$prefix-field=$field_name ";
                }
            }

            $client_rules[$field_name] = $field_rules;
        }
        
        // Merge in any implicit rules       
        foreach ($implicit_rules as $field_name => $field){
            $client_rules[$field_name] .= $field;
        }
        
        return $client_rules;    
    }
    
    private function transformValidator($validator_name, $validator){
        $params = [];
        // Message
        if (isset($validator['message'])){
            if (isset($validator['message'])){
                $params["message"] = $this->_translator->translate($validator['message'], $validator);
            }
        }        
        $transformedValidatorJson = [];        
        switch ($validator_name){
            // Required validator
            case "required":
                $transformedValidatorJson['notEmpty'] = $params;
                break;
            case "length":
                if (isset($validator['min'])) $params['min'] = $validator['min'];
                if (isset($validator['max'])) $params['max'] = $validator['max'];
                $transformedValidatorJson['stringLength'] = $params;
                break;
            case "integer":
                $transformedValidatorJson['integer'] = $params;
                break;
            case "numeric":
                $transformedValidatorJson['numeric'] = $params;
                break;
            case "range":
                if (isset($validator['min'])) $params['min'] = $validator['min'];
                if (isset($validator['max'])) $params['max'] = $validator['max'];
                if (isset($validator['min']) && isset($validator['max']))
                    $transformedValidatorJson['between'] = $params;
                else if (isset($validator['min']))
                    $transformedValidatorJson['greaterThan'] = $params;
                else if (isset($validator['max']))
                    $transformedValidatorJson['lessThan'] = $params;
                break;
            case "array":
                if (isset($validator['min'])) $params['min'] = $validator['min'];
                if (isset($validator['max'])) $params['max'] = $validator['max'];                
                $transformedValidatorJson['choice'] = $params;
                break;
            case "email":
                $transformedValidatorJson['emailAddress'] = $params;
                break;
            case "matches":
                if (isset($validator['field'])) $params['field'] = $validator['field'];   
                $transformedValidatorJson['identical'] = $params;
                break;
            case "not_matches":
                if (isset($validator['field'])) $params['field'] = $validator['field'];   
                $transformedValidatorJson['different'] = $params;
                break;
            case "member_of":
                if (isset($validator['values'])) $params['regexp'] = "^" . implode("|", $validator['values']) . "$";
                $transformedValidatorJson['regexp'] = $params;
                break;
            case "not_member_of":
                if (isset($validator['values'])) $params['regexp'] = "^(?!" . implode("|", $validator['values']) . "$).*$";
                $transformedValidatorJson['regexp'] = $params;
                break;
            default:
                break;
        }
        return $transformedValidatorJson;
        
    }    
    
    public function html5Attributes($validator, $prefix){
        $attr = "$prefix=true ";
        if (isset($validator['message'])){
            $msg = "";
            if (isset($validator['message'])){
                $msg = $validator['message'];
            } else {
                return $attr;
            }
            $attr .= "$prefix-message=\"$msg\" ";    
        }
        return $attr;
    }
}





