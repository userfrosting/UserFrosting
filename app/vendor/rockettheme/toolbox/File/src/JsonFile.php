<?php
namespace RocketTheme\Toolbox\File;

/**
 * Implements Json File reader.
 *
 * @package RocketTheme\Toolbox\File
 * @author RocketTheme
 * @license MIT
 */
class JsonFile extends File
{
    /**
     * @var string
     */
    protected $extension = '.json';

    /**
     * @var array|File[]
     */
    static protected $instances = array();

    /**
     * Check contents and make sure it is in correct format.
     *
     * @param array $var
     * @return array
     */
    protected function check($var)
    {
        return (array) $var;
    }

    /**
     * Encode contents into RAW string.
     *
     * @param string $var
     * @params bitmask $options
     * @return string
     */
    protected function encode($var, $options = 0)
    {
        return (string) json_encode($var, $options);
    }

    /**
     * Decode RAW string into contents.
     *
     * @param string $var
     * @param bool $assoc
     * @return array mixed
     */
    protected function decode($var, $assoc = false)
    {
        return (array) json_decode($var, $assoc);
    }
}
