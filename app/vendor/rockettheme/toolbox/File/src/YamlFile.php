<?php
namespace RocketTheme\Toolbox\File;

use Symfony\Component\Yaml\Exception\DumpException;
use Symfony\Component\Yaml\Exception\ParseException;
use \Symfony\Component\Yaml\Yaml as YamlParser;

/**
 * Implements YAML File reader.
 *
 * @package RocketTheme\Toolbox\File
 * @author RocketTheme
 * @license MIT
 */
class YamlFile extends File
{
    /**
     * @var array|File[]
     */
    static protected $instances = array();

    /**
     * Constructor.
     */
    protected function __construct()
    {
        parent::__construct();

        $this->extension = '.yaml';
    }

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
     * @return string
     * @throws DumpException
     */
    protected function encode($var)
    {
        return (string) YamlParser::dump($var, $this->setting('inline', 5), $this->setting('indent', 2), true, false);
    }

    /**
     * Decode RAW string into contents.
     *
     * @param string $var
     * @return array mixed
     * @throws ParseException
     */
    protected function decode($var)
    {
        $data = false;

        // Try native PECL YAML PHP extension first if available.
        if ($this->setting('native') && function_exists('yaml_parse')) {
            if ($this->setting('compat', true)) {
                // Fix illegal @ start character.
                $data = preg_replace('/ (@[\w\.\-]*)/', " '\${1}'", $var);
            } else {
                $data = $var;
            }

            // Safely decode YAML.
            $saved = @ini_get('yaml.decode_php');
            @ini_set('yaml.decode_php', 0);
            $data = @yaml_parse($data);
            @ini_set('yaml.decode_php', $saved);
        }

        return $data !== false ? $data : (array) YamlParser::parse($var);
    }
}
