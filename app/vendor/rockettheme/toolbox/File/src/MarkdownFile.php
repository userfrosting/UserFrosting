<?php
namespace RocketTheme\Toolbox\File;

use \Symfony\Component\Yaml\Yaml as YamlParser;

/**
 * Implements Markdown File reader.
 *
 * @package RocketTheme\Toolbox\File
 * @author RocketTheme
 * @license MIT
 */
class MarkdownFile extends File
{
    /**
     * @var string
     */
    protected $extension = '.md';

    /**
     * @var array|File[]
     */
    static protected $instances = array();

    /**
     * Get/set file header.
     *
     * @param array $var
     *
     * @return array
     */
    public function header(array $var = null)
    {
        $content = $this->content();

        if ($var !== null) {
            $content['header'] = $var;
            $this->content($content);
        }

        return $content['header'];
    }

    /**
     * Get/set markdown content.
     *
     * @param string $var
     *
     * @return string
     */
    public function markdown($var = null)
    {
        $content = $this->content();

        if ($var !== null) {
            $content['markdown'] = (string) $var;
            $this->content($content);
        }

        return $content['markdown'];
    }

    /**
     * Get/set frontmatter content.
     *
     * @param string $var
     *
     * @return string
     */
    public function frontmatter($var = null)
    {
        $content = $this->content();

        if ($var !== null) {
            $content['frontmatter'] = (string) $var;
            $this->content($content);
        }

        return $content['frontmatter'];
    }

    /**
     * Check contents and make sure it is in correct format.
     *
     * @param array $var
     * @return array
     */
    protected function check($var)
    {
        $var = (array) $var;
        if (!isset($var['header']) || !is_array($var['header'])) {
            $var['header'] = array();
        }
        if (!isset($var['markdown']) || !is_string($var['markdown'])) {
            $var['markdown'] = '';
        }

        return $var;
    }

    /**
     * Encode contents into RAW string.
     *
     * @param string $var
     * @return string
     */
    protected function encode($var)
    {
        // Create Markdown file with YAML header.
        $o = (!empty($var['header']) ? "---\n" . trim(YamlParser::dump($var['header'], 5)) . "\n---\n\n" : '') . $var['markdown'];

        // Normalize line endings to Unix style.
        $o = preg_replace("/(\r\n|\r)/", "\n", $o);

        return $o;
    }

    /**
     * Decode RAW string into contents.
     *
     * @param string $var
     * @return array mixed
     */
    protected function decode($var)
    {
        $content = [
            'header' => false,
            'frontmatter' => ''
        ];

        $frontmatter_regex = "/^---\n(.+?)\n---\n{0,}(.*)$/uis";

        // Normalize line endings to Unix style.
        $var = preg_replace("/(\r\n|\r)/", "\n", $var);

        // Parse header.
        preg_match($frontmatter_regex, ltrim($var), $m);
        if(!empty($m)) {
            $content['frontmatter'] = preg_replace("/\n\t/", "\n    ", $m[1]);

            // Try native PECL YAML PHP extension first if available.
            if ($this->setting('native') && function_exists('yaml_parse')) {
                $data = $content['frontmatter'];
                if ($this->setting('compat', true)) {
                    // Fix illegal @ start character.
                    $data = preg_replace('/ (@[\w\.\-]*)/', " '\${1}'", $data);
                }

                // Safely decode YAML.
                $saved = @ini_get('yaml.decode_php');
                @ini_set('yaml.decode_php', 0);
                $content['header'] = @yaml_parse("---\n" . $data . "\n...");
                @ini_set('yaml.decode_php', $saved);
            }

            if ($content['header'] === false) {
                // YAML hasn't been parsed yet (error or extension isn't available). Fall back to Symfony parser.
                $content['header'] = (array) YamlParser::parse($content['frontmatter']);
            }
            $content['markdown'] = $m[2];
        } else {
            $content['header'] = [];
            $content['markdown'] = $var;
        }

        return $content;
    }
}
