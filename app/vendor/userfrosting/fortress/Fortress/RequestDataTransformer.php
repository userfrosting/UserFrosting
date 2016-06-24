<?php

/**
 * RequestDataTransformer Class
 *
 * Perform a series of transformations on a set of data fields, as specified by a RequestSchema.
 *
 * @package userfrosting/fortress
 * @author Alex Weissman
 * @link https://alexanderweissman.com
 * @license MIT
 */
namespace UserFrosting\Fortress;

use UserFrosting\Support\Exception\BadRequestException;

class RequestDataTransformer implements RequestDataTransformerInterface
{
    
    /**
     * @var RequestSchema
     */        
    protected $schema;
    
    /**
     * @var HTMLPurifier
     */       
    protected $purifier;
    
    /**
     * Create a new data transformer.
     *
     * @param RequestSchema $schema A RequestSchema object, containing the transformation rules.
     */      
    public function __construct($schema)
    {
        // Create purifier
        $config = \HTMLPurifier_Config::createDefault();
        $config->set('Cache.DefinitionImpl', null); // turn off cache
        $this->purifier = new \HTMLPurifier($config);
        
        // Set schema
        $this->setSchema($schema);
    }
    
    /**
     * Set the schema for this transformer, as a valid RequestSchema object.
     *
     * @param RequestSchema $schema A RequestSchema object, containing the transformation rules.
     */
    public function setSchema($schema)
    {
        $this->schema = $schema;
    }
    
    /**
     * Process each field in the specified data array, applying transformations in the specified order.
     *
     * Example transformations: escape/purge/purify HTML entities
     * Also, set any default values for unspecified fields.
     *
     * @param array $data The array of data to be transformed.
     * @param string $on_unexpected_var[optional] Determines what to do when a field is encountered that is not in the schema.  Set to one of:
     * "allow": Treat the field as any other, allowing the value through.
     * "error": Raise an exception.
     * "skip" (default): Quietly ignore the field.  It will not be part of the transformed data array.
     * @return array The array of transformed data, mapping field names => values.
     */
    public function transform($data, $on_unexpected_var = "skip")
    {
        $schemaFields = $this->schema->getSchema();
        
        // 1. Perform sequence of transformations on each field.
        $transformedData = [];
        foreach ($data as $name => $value){        
            // Handle values not listed in the schema
            if (!isset($schemaFields[$name])) {
                switch ($on_unexpected_var) {
                    case "allow" : $transformedData[$name] = $value; break;
                    case "error" :
                        $e = new BadRequestException("The field '$name' is not a valid input field.");
                        throw $e;
                        break;
                    case "skip" : default: continue;
                }
            } else {
                $transformedData[$name] = $this->transformField($name, $value);
            }
        }
        
        // 2. Get default values for any fields missing from $data.  Especially useful for checkboxes, etc which are not submitted when they are unchecked
        foreach ($this->schema->getSchema() as $field_name => $field){
            if (!isset($transformedData[$field_name])){
                if (isset($field['default']))
                    $transformedData[$field_name] = $field['default'];
            }               
        }
        
        return $transformedData;
    }
    
    /**
     * Transform a raw field value.
     *
     * @param string $name The name of the field to transform, as specified in the schema.
     * @param string $value The value to be transformed.
     * @return string The transformed value.
     */
    public function transformField($name, $value)
    {
        $schemaFields = $this->schema->getSchema();
        
        $fieldParameters = $schemaFields[$name];
        
        if (!isset($fieldParameters['transformations']) || empty($fieldParameters['transformations'])) {
            return $value;
        } else {
            // Field exists in schema, so apply sequence of transformations
            $transformedValue = $value;
            
            foreach ($fieldParameters['transformations'] as $transformation){
                switch (strtolower($transformation)){
                    case "purify": $transformedValue = $this->purifier->purify($transformedValue); break;
                    case "escape": $transformedValue = $this->escapeHtmlCharacters($transformedValue); break;
                    case "purge" : $transformedValue = $this->purgeHtmlCharacters($transformedValue); break;
                    case "trim"  : $transformedValue = $this->trim($transformedValue); break;
                    default: break;
                }
            }
            
            return $transformedValue;
        }
    }

    /**
     * Autodetect if a field is an array or scalar, and filter appropriately.
     *
     * @param mixed $value
     * @return mixed
     */
    private function escapeHtmlCharacters($value)
    {
        if (is_array($value))
            return filter_var_array($value, FILTER_SANITIZE_SPECIAL_CHARS);
        else
            return filter_var($value, FILTER_SANITIZE_SPECIAL_CHARS);
    }
    
    /**
     * Autodetect if a field is an array or scalar, and filter appropriately.
     *
     * @param mixed $value
     * @return mixed
     */
    private function purgeHtmlCharacters($value)
    {
        if (is_array($value))
            return filter_var_array($value, FILTER_SANITIZE_STRING);
        else
            return filter_var($value, FILTER_SANITIZE_STRING);
    }
    
    /**
     * Autodetect if a field is an array or scalar, and filter appropriately.
     *
     * @param mixed $value
     * @return mixed
     */
    private function trim($value)
    {
        if (is_array($value))
            return array_map('trim', $value);
        else
            return trim($value);
    }    
}
