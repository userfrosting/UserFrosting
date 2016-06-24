<?php

/**
 * JqueryValidationAdapter Class
 *
 * Loads validation rules from a schema and generates client-side rules compatible with the [jQuery Validation](http://http://jqueryvalidation.org) JS plugin.
 *
 * @package userfrosting/fortress
 * @author Alex Weissman
 * @link https://alexanderweissman.com
 * @license MIT
 */
namespace UserFrosting\Fortress\Adapter;

class JqueryValidationAdapter extends ClientSideValidationAdapter
{

    /**
     * Generate jQuery Validation compatible rules from the specified RequestSchema, as a JSON document.  
     * See [this](https://github.com/jzaefferer/jquery-validation/blob/master/demo/bootstrap/index.html#L168-L209) as an example of what this function will generate.
     * 
     * @param boolean $string_encode Specify whether to return a PHP array, or a JSON-encoded string.
     * @return string|array Returns either the array of rules, or a JSON-encoded representation of that array.
     */
    public function rules($format = "json", $string_encode = true)
    {
        $client_rules = [];
        $client_messages = [];
        $implicit_rules = [];
        foreach ($this->schema->getSchema() as $field_name => $field){
            $client_rules[$field_name] = [];
            if (isset($field['validators'])){
                $validators = $field['validators'];
                foreach ($validators as $validator_name => $validator){
                
                    // Skip messages that are for server-side use only
                    if (isset($validator['domain']) && $validator['domain'] == "server")
                        continue;
                    
                    $new_rules = $this->transformValidator($field_name, $validator_name, $validator);
                    $client_rules[$field_name] = array_merge($client_rules[$field_name], $new_rules);
                    // Message
                    if (isset($validator['message'])){
                        $validator = array_merge(["self" => $field_name], $validator);
                        if (!isset($client_messages[$field_name]))
                            $client_messages[$field_name] = [];
                        // Copy the translated message to every translated rule created by this validation rule
                        $message = $this->translator->translate($validator['message'], $validator);
                        foreach ($new_rules as $translated_rule_name => $rule){
                            $client_messages[$field_name][$translated_rule_name] = $message;
                        }
                    }  
                }
            }
        }
        $result = [
            "rules" => $client_rules,
            "messages" => $client_messages
        ];
        
        if ($string_encode)
            return json_encode($result, JSON_PRETTY_PRINT);
        else
            return $result;
    }
   
    /**
     * Transform a validator for a particular field into one or more jQueryValidation rules.
     *
     * @param string $field_name
     * @param string $validator_name
     * @param string[] $validator     
     */
    private function transformValidator($field_name, $validator_name, $validator)
    {   
        $transformedValidatorJson = [];        
        switch ($validator_name){
            // Required validator
            case "required":
                $transformedValidatorJson['required'] = true;
                break;
            case "email":
                $transformedValidatorJson['email'] = true;
                break;            
            case "telephone":
                $transformedValidatorJson['phoneUS'] = true;
                break;
            case "uri":
                $transformedValidatorJson['url'] = true;
                break;
            case "regex":
                $transformedValidatorJson['pattern'] = $validator['regex'];
                break;                   
            case "length":
                if (isset($validator['min']) && isset($validator['max']))
                    $transformedValidatorJson['rangelength'] = [
                        $validator['min'],
                        $validator['max']
                    ];            
                else if (isset($validator['min']))
                    $transformedValidatorJson['minlength'] = $validator['min'];
                else if (isset($validator['max']))
                    $transformedValidatorJson['maxlength'] = $validator['max'];
                break;
            case "integer":
                $transformedValidatorJson['digits'] = true;
                break;
            case "numeric":
                $transformedValidatorJson['number'] = true;
                break;
            case "range":
                if (isset($validator['min']) && isset($validator['max']))
                    $transformedValidatorJson['range'] = [
                        $validator['min'],
                        $validator['max']
                    ];
                else if (isset($validator['min']))
                    $transformedValidatorJson['min'] = $validator['min'];
                else if (isset($validator['max']))
                    $transformedValidatorJson['max'] = $validator['max'];
                break;
            case "member_of":
                if (isset($validator['values']))
                    $transformedValidatorJson['memberOf'] = $validator['values'];
                break;
            case "not_member_of":
                if (isset($validator['values']))
                    $transformedValidatorJson['notMemberOf'] = $validator['values'];
                break;
            case "matches":           
                if (isset($validator['field']))
                $transformedValidatorJson['matchFormField'] = $validator['field'];
                break;
            case "not_matches":
                if (isset($validator['field']))
                $transformedValidatorJson['notMatchFormField'] = $validator['field'];
                break;
            case "no_leading_whitespace":
                $transformedValidatorJson['noLeadingWhitespace'] = true;
                break;
            case "no_trailing_whitespace":
                $transformedValidatorJson['noTrailingWhitespace'] = true;
                break;            
            default:
                break;
        }
        return $transformedValidatorJson;  
    }
}
