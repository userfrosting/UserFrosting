<?php
namespace RocketTheme\Toolbox\ArrayTraits;

/**
 * Implements nested ArrayAccess interface with dot notation.
 *
 * @package RocketTheme\Toolbox\ArrayTraits
 * @author RocketTheme
 * @license MIT
 *
 * @property array $items
 */
trait NestedArrayAccess
{
    protected $nestedSeparator = '.';

    /**
     * Get value by using dot notation for nested arrays/objects.
     *
     * @example $value = $this->get('this.is.my.nested.variable');
     *
     * @param string  $name       Dot separated path to the requested value.
     * @param mixed   $default    Default value (or null).
     * @param string  $separator  Separator, defaults to '.'
     * @return mixed  Value.
     */
    public function get($name, $default = null, $separator = null)
    {
        $path = explode($separator ?: $this->nestedSeparator, $name);
        $current = $this->items;
        foreach ($path as $field) {
            if (is_object($current) && isset($current->{$field})) {
                $current = $current->{$field};
            } elseif (is_array($current) && isset($current[$field])) {
                $current = $current[$field];
            } else {
                return $default;
            }
        }

        return $current;
    }

    /**
     * Set value by using dot notation for nested arrays/objects.
     *
     * @example $data->set('this.is.my.nested.variable', $value);
     *
     * @param string  $name       Dot separated path to the requested value.
     * @param mixed   $value      New value.
     * @param string  $separator  Separator, defaults to '.'
     * @return $this
     */
    public function set($name, $value, $separator = null)
    {
        $path = explode($separator ?: $this->nestedSeparator, $name);
        $current = &$this->items;
        foreach ($path as $field) {
            if (is_object($current)) {
                // Handle objects.
                if (!isset($current->{$field})) {
                    $current->{$field} = array();
                }
                $current = &$current->{$field};
            } else {
                // Handle arrays and scalars.
                if (!is_array($current)) {
                    $current = array($field => array());
                } elseif (!isset($current[$field])) {
                    $current[$field] = array();
                }
                $current = &$current[$field];
            }
        }

        $current = $value;

        return $this;
    }

    /**
     * Unset value by using dot notation for nested arrays/objects.
     *
     * @example $data->undef('this.is.my.nested.variable');
     *
     * @param string  $name       Dot separated path to the requested value.
     * @param string  $separator  Separator, defaults to '.'
     * @return $this
     */
    public function undef($name, $separator = null)
    {
        if ($name === '') {
            $this->items = [];

            return $this;
        }

        $path = explode($separator ?: $this->nestedSeparator, $name);
        $var = array_pop($path);
        $current = &$this->items;

        foreach ($path as $field) {
            if (is_object($current)) {
                // Handle objects.
                if (!isset($current->{$field})) {
                    return $this;
                }
                $current = &$current->{$field};
            } else {
                // Handle arrays and scalars.
                if (!is_array($current) || !isset($current[$field])) {
                    return $this;
                }
                $current = &$current[$field];
            }
        }

        unset($current[$var]);

        return $this;
    }

    /**
     * Set default value by using dot notation for nested arrays/objects.
     *
     * @example $data->def('this.is.my.nested.variable', 'default');
     *
     * @param string  $name       Dot separated path to the requested value.
     * @param mixed   $default    Default value (or null).
     * @param string  $separator  Separator, defaults to '.'
     * @return $this
     */
    public function def($name, $default = null, $separator = null)
    {
        $this->set($name, $this->get($name, $default, $separator), $separator);

        return $this;
    }

    /**
     * Whether or not an offset exists.
     *
     * @param mixed $offset  An offset to check for.
     * @return bool          Returns TRUE on success or FALSE on failure.
     */
    public function offsetExists($offset)
    {
        return $this->get($offset) !== null;
    }

    /**
     * Returns the value at specified offset.
     *
     * @param mixed $offset  The offset to retrieve.
     * @return mixed         Can return all value types.
     */
    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    /**
     * Assigns a value to the specified offset.
     *
     * @param mixed $offset  The offset to assign the value to.
     * @param mixed $value   The value to set.
     */
    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->items[] = $value;
        } else {
            $this->set($offset, $value);
        }
    }

    /**
     * Unsets variable at specified offset.
     *
     * @param $offset
     */
    public function offsetUnset($offset)
    {
        if (is_null($offset)) {
            $this->items[] = [];
        } else {
            $this->undef($offset);
        }
    }
}
