<?php
namespace RocketTheme\Toolbox\Blueprints;

/**
 * BlueprintSchema is used to define a data structure.
 *
 * @package RocketTheme\Toolbox\Blueprints
 * @author RocketTheme
 * @license MIT
 */
class BlueprintSchema
{
    /**
     * @var array
     */
    protected $items = [];

    /**
     * @var array
     */
    protected $rules = [];

    /**
     * @var array
     */
    protected $nested = [];

    /**
     * @var array
     */
    protected $dynamic = [];

    /**
     * @var array
     */
    protected $filter = ['validation' => true];

    /**
     * @var array
     */
    protected $ignoreFormKeys = ['fields' => 1];

    /**
     * @var array
     */
    protected $types = [];

    /**
     * Constructor.
     *
     * @param array $serialized  Serialized content if available.
     */
    public function __construct($serialized = null)
    {
        if (is_array($serialized) && !empty($serialized)) {
            $this->items = (array) $serialized['items'];
            $this->rules = (array) $serialized['rules'];
            $this->nested = (array) $serialized['nested'];
            $this->dynamic = (array) $serialized['dynamic'];
            $this->filter = (array) $serialized['filter'];
        }
    }

    /**
     * @param array $types
     * @return $this
     */
    public function setTypes(array $types)
    {
        $this->types = $types;

        return $this;
    }

    /**
     * Restore Blueprints object.
     *
     * @param array $serialized
     * @return static
     */
    public static function restore(array $serialized)
    {
        return new static($serialized);
    }

    /**
     * Initialize blueprints with its dynamic fields.
     *
     * @return $this
     */
    public function init()
    {
        foreach ($this->dynamic as $key => $data) {
            $field = &$this->items[$key];
            foreach ($data as $property => $call) {
                $action = 'dynamic' . ucfirst($call['action']);

                if (method_exists($this, $action)) {
                    $this->{$action}($field, $property, $call);
                }
            }
        }

        return $this;
    }

    /**
     * Set filter for inherited properties.
     *
     * @param array $filter     List of field names to be inherited.
     */
    public function setFilter(array $filter)
    {
        $this->filter = array_flip($filter);
    }

    /**
     * Get value by using dot notation for nested arrays/objects.
     *
     * @example $value = $data->get('this.is.my.nested.variable');
     *
     * @param string  $name       Dot separated path to the requested value.
     * @param mixed   $default    Default value (or null).
     * @param string  $separator  Separator, defaults to '.'
     *
     * @return mixed  Value.
     */
    public function get($name, $default = null, $separator = '.')
    {
        $name = $separator != '.' ? strtr($name, $separator, '.') : $name;

        return isset($this->items[$name]) ? $this->items[$name] : $default;
    }

    /**
     * Set value by using dot notation for nested arrays/objects.
     *
     * @example $value = $data->set('this.is.my.nested.variable', $newField);
     *
     * @param string  $name       Dot separated path to the requested value.
     * @param mixed   $value      New value.
     * @param string  $separator  Separator, defaults to '.'
     */
    public function set($name, $value, $separator = '.')
    {
        $name = $separator != '.' ? strtr($name, $separator, '.') : $name;

        $this->items[$name] = $value;
        $this->addProperty($name);
    }

    /**
     * Define value by using dot notation for nested arrays/objects.
     *
     * @example $value = $data->set('this.is.my.nested.variable', true);
     *
     * @param string  $name       Dot separated path to the requested value.
     * @param mixed   $value      New value.
     * @param string  $separator  Separator, defaults to '.'
     */
    public function def($name, $value, $separator = '.')
    {
        $this->set($name, $this->get($name, $value, $separator), $separator);
    }

    /**
     * @return array
     * @deprecated
     */
    public function toArray()
    {
        return $this->getState();
    }

    /**
     * Convert object into an array.
     *
     * @return array
     */
    public function getState()
    {
        return [
            'items' => $this->items,
            'rules' => $this->rules,
            'nested' => $this->nested,
            'dynamic' => $this->dynamic,
            'filter' => $this->filter
        ];
    }

    /**
     * Get nested structure containing default values defined in the blueprints.
     *
     * Fields without default value are ignored in the list.
     *
     * @return array
     */
    public function getDefaults()
    {
        return $this->buildDefaults($this->nested);
    }

    /**
     * Embed an array to the blueprint.
     *
     * @param $name
     * @param array $value
     * @param string $separator
     * @param bool $merge   Merge fields instead replacing them.
     * @return $this
     */
    public function embed($name, array $value, $separator = '.', $merge = false)
    {
        if (isset($value['rules'])) {
            $this->rules = array_merge($this->rules, $value['rules']);
        }
        if (!isset($value['form']['fields']) || !is_array($value['form']['fields'])) {
            $value['form']['fields'] = [];
        }
        $name = $separator != '.' ? strtr($name, $separator, '.') : $name;

        $form = array_diff_key($value['form'], ['fields' => 1]);
        $items = isset($this->items[$name]) ? $this->items[$name] : ['type' => '_root', 'form_field' => false];

        $this->items[$name] = [
                'form' => $form
            ] + $items;

        $this->addProperty($name);

        $prefix = $name ? $name . '.' : '';
        $params = array_intersect_key($form, $this->filter);
        $location = [$name];
        $this->parseFormFields($value['form']['fields'], $params, $prefix, '', $merge, $location);

        return $this;
    }

    /**
     * Merge two arrays by using blueprints.
     *
     * @param  array $data1
     * @param  array $data2
     * @param  string $name         Optional
     * @param  string $separator    Optional
     * @return array
     */
    public function mergeData(array $data1, array $data2, $name = null, $separator = '.')
    {
        $nested = $this->getProperty($name, $separator);

        return $this->mergeArrays($data1, $data2, $nested);
    }


    /**
     * Get property from the definition.
     *
     * @param  string  $path  Comma separated path to the property.
     * @param  string  $separator
     * @return array
     * @internal
     */
    public function getProperty($path = null, $separator = '.')
    {
        if (!$path) {
            return $this->nested;
        }
        $parts = explode($separator, $path);
        $item = array_pop($parts);

        $nested = $this->nested;
        foreach ($parts as $part) {
            if (!isset($nested[$part])) {
                return [];
            }
            $nested = $nested[$part];
        }

        return isset($nested[$item]) && is_array($nested[$item]) ? $nested[$item] : [];
    }

    /**
     * Return data fields that do not exist in blueprints.
     *
     * @param  array  $data
     * @param  string $prefix
     * @return array
     */
    public function extra(array $data, $prefix = '')
    {
        $rules = $this->nested;

        // Drill down to prefix level
        if (!empty($prefix)) {
            $parts = explode('.', trim($prefix, '.'));
            foreach ($parts as $part) {
                $rules = isset($rules[$part]) ? $rules[$part] : [];
            }
        }

        return $this->extraArray($data, $rules, $prefix);
    }

    /**
     * @param array $nested
     * @return array
     */
    protected function buildDefaults(array $nested)
    {
        $defaults = [];

        foreach ($nested as $key => $value) {
            if ($key === '*') {
                // TODO: Add support for adding defaults to collections.
                continue;
            }
            if (is_array($value)) {
                // Recursively fetch the items.
                $list = $this->buildDefaults($value);

                // Only return defaults if there are any.
                if (!empty($list)) {
                    $defaults[$key] = $list;
                }
            } else {
                // We hit a field; get default from it if it exists.
                $item = $this->get($value);

                // Only return default value if it exists.
                if (isset($item['default'])) {
                    $defaults[$key] = $item['default'];
                }
            }
        }

        return $defaults;
    }

    /**
     * @param array $data1
     * @param array $data2
     * @param array $rules
     * @return array
     * @internal
     */
    protected function mergeArrays(array $data1, array $data2, array $rules)
    {
        foreach ($data2 as $key => $field) {
            $val = isset($rules[$key]) ? $rules[$key] : null;
            $rule = is_string($val) ? $this->items[$val] : null;

            if (!empty($rule['type']) && $rule['type'][0] === '_'
                || (array_key_exists($key, $data1) && is_array($data1[$key]) && is_array($field) && is_array($val) && !isset($val['*']))
            ) {
                // Array has been defined in blueprints and is not a collection of items.
                $data1[$key] = $this->mergeArrays($data1[$key], $field, $val);
            } else {
                // Otherwise just take value from the data2.
                $data1[$key] = $field;
            }
        }

        return $data1;
    }

    /**
     * Gets all field definitions from the blueprints.
     *
     * @param array  $fields    Fields to parse.
     * @param array  $params    Property parameters.
     * @param string $prefix    Property prefix.
     * @param string $parent    Parent property.
     * @param bool   $merge     Merge fields instead replacing them.
     * @param array $formPath
     */
    protected function parseFormFields(array $fields, array $params, $prefix = '', $parent = '', $merge = false, array $formPath = [])
    {
        // Go though all the fields in current level.
        foreach ($fields as $key => $field) {
            $key = $this->getFieldKey($key, $prefix, $parent);

            $newPath = array_merge($formPath, [$key]);

            $properties = array_diff_key($field, $this->ignoreFormKeys) + $params;
            $properties['name'] = $key;

            // Set default properties for the field type.
            $type = isset($properties['type']) ? $properties['type'] : '';
            if (isset($this->types[$type])) {
                $properties += $this->types[$type];
            }

            // Merge properties with existing ones.
            if ($merge && isset($this->items[$key])) {
                $properties += $this->items[$key];
            }

            $isInputField = !isset($properties['input@']) || $properties['input@'];

            if (!$isInputField) {
                // Remove property if it exists.
                if (isset($this->items[$key])) {
                    $this->removeProperty($key);
                }
            } elseif (!isset($this->items[$key])) {
                // Add missing property.
                $this->addProperty($key);
            }

            if (isset($field['fields'])) {
                // Recursively get all the nested fields.
                $isArray = !empty($properties['array']);
                $newParams = array_intersect_key($properties, $this->filter);
                $this->parseFormFields($field['fields'], $newParams, $prefix, $key . ($isArray ? '.*': ''), $merge, $newPath);
            } else {
                if (!isset($this->items[$key])) {
                    // Add parent rules.
                    $path = explode('.', $key);
                    array_pop($path);
                    $parent = '';
                    foreach ($path as $part) {
                        $parent .= ($parent ? '.' : '') . $part;
                        if (!isset($this->items[$parent])) {
                            $this->items[$parent] = ['type' => '_parent', 'name' => $parent, 'form_field' => false];
                        }
                    }
                }

                if ($isInputField) {
                    $this->parseProperties($key, $properties);
                }
            }

            if ($isInputField) {
                $this->items[$key] = $properties;
            }
        }
    }

    protected function getFieldKey($key, $prefix, $parent)
    {
        // Set name from the array key.
        if ($key && $key[0] == '.') {
            return ($parent ?: rtrim($prefix, '.')) . $key;
        }

        return $prefix . $key;
    }

    protected function parseProperties($key, array &$properties)
    {
        $key = ltrim($key, '.');

        if (!empty($properties['data'])) {
            $this->dynamic[$key] = $properties['data'];
        }

        foreach ($properties as $name => $value) {
            if (!empty($name) && ($name[0] === '@' || $name[strlen($name) - 1] === '@')) {
                $list = explode('-', trim($name, '@'), 2);
                $action = array_shift($list);
                $property = array_shift($list);

                $this->dynamic[$key][$property] = ['action' => $action, 'params' => $value];
            }
        }

        // Initialize predefined validation rule.
        if (isset($properties['validate']['rule'])) {
            $properties['validate'] += $this->getRule($properties['validate']['rule']);
        }
    }

    /**
     * Add property to the definition.
     *
     * @param  string  $path  Comma separated path to the property.
     * @internal
     */
    protected function addProperty($path)
    {
        $parts = explode('.', $path);
        $item = array_pop($parts);

        $nested = &$this->nested;
        foreach ($parts as $part) {
            if (!isset($nested[$part]) || !is_array($nested[$part])) {
                $nested[$part] = [];
            }
            $nested = &$nested[$part];
        }

        if (!isset($nested[$item])) {
            $nested[$item] = $path;
        }
    }

    /**
     * Remove property to the definition.
     *
     * @param  string  $path  Comma separated path to the property.
     * @internal
     */
    protected function removeProperty($path)
    {
        $parts = explode('.', $path);
        $item = array_pop($parts);

        $nested = &$this->nested;
        foreach ($parts as $part) {
            if (!isset($nested[$part]) || !is_array($nested[$part])) {
                return;
            }
            $nested = &$nested[$part];
        }

        if (isset($nested[$item])) {
            unset($nested[$item]);
        }
    }

    /**
     * @param $rule
     * @return array
     * @internal
     */
    protected function getRule($rule)
    {
        if (isset($this->rules[$rule]) && is_array($this->rules[$rule])) {
            return $this->rules[$rule];
        }
        return [];
    }

    /**
     * @param array $data
     * @param array $rules
     * @param string $prefix
     * @return array
     * @internal
     */
    protected function extraArray(array $data, array $rules, $prefix)
    {
        $array = [];

        foreach ($data as $key => $field) {
            $val = isset($rules[$key]) ? $rules[$key] : (isset($rules['*']) ? $rules['*'] : null);
            $rule = is_string($val) ? $this->items[$val] : null;

            if ($rule || isset($val['*'])) {
                // Item has been defined in blueprints.
            } elseif (is_array($field) && is_array($val)) {
                // Array has been defined in blueprints.
                $array += $this->ExtraArray($field, $val, $prefix . $key . '.');
            } else {
                // Undefined/extra item.
                $array[$prefix.$key] = $field;
            }
        }
        return $array;
    }

    /**
     * @param array $field
     * @param string $property
     * @param array $call
     */
    protected function dynamicData(array &$field, $property, array $call)
    {
        $params = $call['params'];

        if (is_array($params)) {
            $function = array_shift($params);
        } else {
            $function = $params;
            $params = [];
        }

        $list = preg_split('/::/', $function, 2);
        $f = array_pop($list);
        $o = array_pop($list);
        if (!$o) {
            if (function_exists($f)) {
                $data = call_user_func_array($f, $params);
            }
        } else {
            if (method_exists($o, $f)) {
                $data = call_user_func_array(array($o, $f), $params);
            }
        }

        // If function returns a value,
        if (isset($data)) {
            if (isset($field[$property]) && is_array($field[$property]) && is_array($data)) {
                // Combine field and @data-field together.
                $field[$property] += $data;
            } else {
                // Or create/replace field with @data-field.
                $field[$property] = $data;
            }
        }
    }
}
