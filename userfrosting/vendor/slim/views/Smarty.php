<?php
/**
 * Slim - a micro PHP 5 framework
 *
 * @author      Josh Lockhart
 * @author      Andrew Smith
 * @link        http://www.slimframework.com
 * @copyright   2013 Josh Lockhart
 * @version     0.1.3
 * @package     SlimViews
 *
 * MIT LICENSE
 *
 * Permission is hereby granted, free of charge, to any person obtaining
 * a copy of this software and associated documentation files (the
 * "Software"), to deal in the Software without restriction, including
 * without limitation the rights to use, copy, modify, merge, publish,
 * distribute, sublicense, and/or sell copies of the Software, and to
 * permit persons to whom the Software is furnished to do so, subject to
 * the following conditions:
 *
 * The above copyright notice and this permission notice shall be
 * included in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 * NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
 * LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
 * OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
 * WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */
namespace Slim\Views;

/**
 * SmartyView
 *
 * The SmartyView is a custom View class that renders templates using the Smarty
 * template language (http://www.smarty.net).
 *
 * Two fields that you, the developer, will need to change are:
 * - parserDirectory
 * - parserCompileDirectory
 * - parserCacheDirectory
 *
 * @package Slim
 * @author  Jose da Silva <http://josedasilva.net>
 */
class Smarty extends \Slim\View
{
    /**
     * @var string The path to the Smarty code directory WITHOUT the trailing slash
     */
    public $parserDirectory = null;

    /**
     * @var string The path to the Smarty compiled templates folder WITHOUT the trailing slash
     */
    public $parserCompileDirectory = null;

    /**
     * @var string The path to the Smarty cache folder WITHOUT the trailing slash
     */
    public $parserCacheDirectory = null;

    /**
     * @var SmartyExtensions The Smarty extensions directory you want to load plugins from
     */
    public $parserExtensions = array();

    /**
     * @var parserInstance persistent instance of the Parser object.
     */
    private $parserInstance = null;

    /**
     * Render Template
     *
     * This method will output the rendered template content
     *
     * @param string $template The path to the template, relative to the  templates directory.
     * @param null $data
     * @return string
     */
    public function render($template, $data = null)
    {
        $parser = $this->getInstance();
        $parser->assign($this->all());

        return $parser->fetch($template, $data);
    }

    /**
     * Creates new Smarty object instance if it doesn't already exist, and returns it.
     *
     * @throws \RuntimeException If Smarty lib directory does not exist
     * @return \Smarty Instance
     */
    public function getInstance()
    {
        if (!($this->parserInstance instanceof \Smarty)) {
            if (!class_exists('\Smarty')) {
                if (!is_dir($this->parserDirectory)) {
                    throw new \RuntimeException('Cannot set the Smarty lib directory : ' . $this->parserDirectory . '. Directory does not exist.');
                }
                require_once $this->parserDirectory . '/Smarty.class.php';
            }

            $this->parserInstance = new \Smarty();
            $this->parserInstance->template_dir = $this->getTemplatesDirectory();
            if ($this->parserExtensions) {
                $this->parserInstance->addPluginsDir($this->parserExtensions);
            }
            if ($this->parserCompileDirectory) {
                $this->parserInstance->compile_dir = $this->parserCompileDirectory;
            }
            if ($this->parserCacheDirectory) {
                $this->parserInstance->cache_dir = $this->parserCacheDirectory;
            }
        }

        return $this->parserInstance;
    }
}
