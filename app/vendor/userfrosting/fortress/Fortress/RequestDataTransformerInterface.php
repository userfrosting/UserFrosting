<?php

/**
 * RequestDataTransformer Interface
 *
 * Perform a series of transformations on a set of data fields, as specified by a RequestSchema.
 *
 * @package userfrosting/fortress
 * @author Alex Weissman
 * @link https://github.com/userfrosting/fortress
 * @license MIT
 */
namespace UserFrosting\Fortress;

interface RequestDataTransformerInterface
{   
    /**
     * Set the schema for this transformer, as a valid RequestSchema object.
     *
     * @param RequestSchema $schema A RequestSchema object, containing the transformation rules.
     */    
    public function setSchema($schema);
    
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
    public function transform($data, $on_unexpected_var);
    
    /**
     * Transform a raw field value.
     *
     * @param string $name The name of the field to transform, as specified in the schema.
     * @param string $value The value to be transformed.
     * @return string The transformed value.
     */
    public function transformField($name, $value);    
}
