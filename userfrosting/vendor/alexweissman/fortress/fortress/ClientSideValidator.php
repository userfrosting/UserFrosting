<?php

namespace Fortress;

/* Loads a validation schema, and generates client-side rules compatible with the FormValidation JS plugin. */

class ClientSideValidator {

    protected $_schema = [];
    protected $_locale = "";

    // Load schema from a file
    public function __construct($file, $locale = "en_US") {
        $this->_schema = json_decode(file_get_contents($file),true);
        if ($this->_schema === null) {
            error_log(json_last_error());
            // Throw error
        }
        $this->_locale = $locale;
    }

    
    /* Generate FormValidation compatible rules from the schema */
    public function formValidationRulesJson($encode = true){
        $client_rules = [];
        $implicit_rules = [];
        foreach ($this->_schema as $field_name => $field){
            $client_rules[$field_name] = [];
            $client_rules[$field_name]['validators'] = [];
            $validators = $field['validators'];
            foreach ($validators as $validator_name => $validator){
                $client_rules[$field_name]['validators'] = array_merge($client_rules[$field_name]['validators'], $this->transformValidator($validator_name, $validator));
            }
        }
        if ($encode)
            return json_encode($client_rules, JSON_PRETTY_PRINT);
        else
            return $client_rules;
    }
    
    private function transformValidator($validator_name, $validator){
        $params = [];
        // Message
        if (isset($validator['message'])){
            if (isset($validator['message'])){
                $params["message"] = MessageTranslator::translate($validator['message'], $validator);
            }
        }        
        $transformedValidatorJson = [];        
        switch ($validator_name){
            // Required validator

            case "length":
                if (isset($validator['min'])) $params['min'] = $validator['min'];
                if (isset($validator['max'])) $params['max'] = $validator['max'];
                $transformedValidatorJson['stringLength'] = $params;
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
            case "integer":
                $transformedValidatorJson['integer'] = $params;
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
            case "required":
                $transformedValidatorJson['notEmpty'] = $params;
                break;
            default:
                break;
        }
        return $transformedValidatorJson;
        
    }
   
    public function clientRules(){
        $client_rules = array();
        $implicit_rules = array();
        foreach ($this->_schema as $field_name => $field){
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





