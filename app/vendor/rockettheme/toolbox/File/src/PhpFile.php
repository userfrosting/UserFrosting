<?php
namespace RocketTheme\Toolbox\File;

/**
 * Implements PHP File reader.
 *
 * @package RocketTheme\Toolbox\File
 * @author RocketTheme
 * @license MIT
 */
class PhpFile extends File
{
    /**
     * @var string
     */
    protected $extension = '.php';

    /**
     * @var array|File[]
     */
    static protected $instances = array();

    /**
     * Saves PHP file and invalidates opcache.
     *
     * @param  mixed  $data  Optional data to be saved, usually array.
     * @throws \RuntimeException
     */
    public function save($data = null)
    {
        parent::save($data);

        // Invalidate configuration file from the opcache.
        if (function_exists('opcache_invalidate')) {
            // PHP 5.5.5+
            @opcache_invalidate($this->filename, true);
        } elseif (function_exists('apc_invalidate')) {
            // APC
            @apc_invalidate($this->filename);
        }
    }

    /**
     * Check contents and make sure it is in correct format.
     *
     * @param array $var
     * @return array
     * @throws \RuntimeException
     */
    protected function check($var)
    {
        if (!(is_array($var) || is_object($var))) {
            throw new \RuntimeException('Provided data is not an array');
        }

        return $var;
    }

    /**
     * Encode configuration object into RAW string (PHP class).
     *
     * @param array $var
     * @return string
     * @throws \RuntimeException
     */
    protected function encode($var)
    {
        // Build the object variables string
        return "<?php\nreturn {$this->encodeArray((array) $var)};\n";
    }

    /**
     * Method to get an array as an exported string.
     *
     * @param array $a      The array to get as a string.
     * @param int   $level  Used internally to indent rows.
     *
     * @return array
     */
    protected function encodeArray(array $a, $level = 0)
    {
        $r = [];
        foreach ($a as $k => $v) {
            if (is_array($v) || is_object($v)) {
                $r[] = var_export($k, true) . " => " . $this->encodeArray((array) $v, $level + 1);
            } else {
                $r[] = var_export($k, true) . " => " . var_export($v, true);
            }
        }

        $space = str_repeat("    ", $level);
        return "[\n    {$space}" . implode(",\n    {$space}", $r) . "\n{$space}]";
    }

    /**
     * Decode PHP file into contents.
     *
     * @param string $var
     * @return array
     */
    protected function decode($var)
    {
        $var = (array) include $this->filename;
        return $var;
    }
}
