<?php

namespace Fortress;

interface DataSanitizerInterface {
    public function setSchema($schema);
    public function sanitize($data, $on_unexpected_var);
}

/**
 * DataSanitizer Class
 *
 * Perform sanitization and transformation on a set of data fields, as specified by a RequestSchema.
 *
 * @package Fortress
 * @author Alex Weissman
 * @link http://alexanderweissman.com
 */
 class DataSanitizer implements DataSanitizerInterface {
    
    /**
     * @var RequestSchema
     */        
    protected $_schema;
    
    /**
     * @var HTMLPurifier
     */       
    protected $_purifier;
    
    /**
     * Create a new data sanitizer.
     *
     * @param RequestSchema $schema A RequestSchema object, containing the validation rules.
     */      
    public function __construct($schema){
        // Create purifier
        $this->_purifier = new \HTMLPurifier();
         
        // Set schema
        $this->setSchema($schema);
    }
    
    /**
     * Set the schema for this sanitizer, as a valid RequestSchema object.
     *
     * @param RequestSchema $schema A RequestSchema object, containing the validation and sanitization rules.
     */
    public function setSchema($schema){
        $this->_schema = $schema;
    }
    
    /**
     * Sanitize each field in the specified data array, applying transformations in the following order:
     * 1. Escape/purge/purify HTML entities
     * 2. Set any default values for unspecified fields.
     * 3. Perform any other specified transformations.
     *
     * @param array $data The array of data to be sanitized.
     * @param string $on_unexpected_var[optional] Determines what to do when a field is encountered that is not in the schema.  Set to one of:
     * "allow": Treat the field as any other, applying the "purge" sanitization filter.
     * "error": Raise an exception.
     * "skip" (default): Quietly ignore the field.  It will not be part of the sanitized data array.
     * @return array The array of sanitized data, mapping field names => values.
     */
    public function sanitize($data, $on_unexpected_var = "skip") {
        $schemaFields = $this->_schema->getSchema();
        
        // 1. Perform sanitization on each value in the $data array.  This is important for preventing XSS attacks.
        // If there is a sanitization rule specified in the schema, use that.  Otherwise, apply the FILTER_SANITIZE_SPECIAL_CHARS filter by default.
        $sanitizedData = [];
        foreach ($data as $name => $value){        
            // Default sanitization behavior
            if (!isset($schemaFields[$name])) {
                switch ($on_unexpected_var) {
                    case "allow" : $sanitizedData[$name] = $this->purgeHtmlCharacters($value); break;
                    case "error" : throw new \Exception("The field '$name' is not a valid input field."); break;
                    case "skip" : default: continue;
                }
            } else {
                $sanitizedData[$name] = $this->sanitizeField($name, $value);
            }
        }
        
        // 2. Get default values for any fields missing from $data.  Especially useful for checkboxes, etc which are not submitted when they are unchecked
        foreach ($this->_schema->getSchema() as $field_name => $field){
            if (!isset($sanitizedData[$field_name])){
                if (isset($field['default']))
                    $sanitizedData[$field_name] = $field['default'];
            }               
        }
        
        return $sanitizedData;
    }
    
    /**
     * Sanitize a raw field value.
     *
     * @param string $name The name of the field to sanitize, as specified in the schema.
     * @param string $value The value to be sanitized.
     * @return string The sanitized value.
     */
    public function sanitizeField($name, $value){
        $schemaFields = $this->_schema->getSchema();

        $fieldParameters = $schemaFields[$name];
        
        $sanitizedValue = $value;
        // Field exists in schema, so validate accordingly
        if (!isset($fieldParameters['sanitizers']) || empty($fieldParameters['sanitizers'])) {
            return $this->purgeHtmlCharacters($sanitizedValue);
        } else {
            $processed = false;
            foreach ($fieldParameters['sanitizers'] as $sanitizer => $attributes){
                switch (strtolower($sanitizer)){
                    case "purify": $sanitizedValue = $this->_purifier->purify($sanitizedValue); $processed = true; break;
                    case "escape": $sanitizedValue = $this->escapeHtmlCharacters($sanitizedValue); $processed = true; break;
                    case "purge" : $sanitizedValue = $this->purgeHtmlCharacters($sanitizedValue); $processed = true; break;
                    case "raw" : $processed = true; break;
                    default: break;
                }
            }
            // If no sanitizers have been applied, then apply purge by default
            if (!$processed)
                $sanitizedValue = $this->purgeHtmlCharacters($sanitizedValue);
            return $sanitizedValue;
        }
    }

    /** Autodetect if a field is an array or scalar, and filter appropriately. */
    private function escapeHtmlCharacters($value){
            if (is_array($value))
            return filter_var_array($value, FILTER_SANITIZE_SPECIAL_CHARS);
        else
            return filter_var($value, FILTER_SANITIZE_SPECIAL_CHARS);
    }
    
    /** Autodetect if a field is an array or scalar, and filter appropriately. */
    private function purgeHtmlCharacters($value){
            if (is_array($value))
            return filter_var_array($value, FILTER_SANITIZE_STRING);
        else
            return filter_var($value, FILTER_SANITIZE_STRING);
    }
}

?>
