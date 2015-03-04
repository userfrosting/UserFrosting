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
 * Twig view
 *
 * The Twig view is a custom View class that renders templates using the Twig
 * template language (http://www.twig-project.org/).
 *
 * Two fields that you, the developer, will need to change are:
 * - parserDirectory
 * - parserOptions
 */
class Twig extends \Slim\View
{
    /**
     * @var string The path to the Twig code directory WITHOUT the trailing slash
     */
    public $parserDirectory = null;

    /**
     * DEPRECATION WARNING! This method will be removed in the next major point release
     *
     * @var array Paths to directories to attempt to load Twig template from
     */
    public $twigTemplateDirs = array();

    /**
     * @var array The options for the Twig environment, see
     * http://www.twig-project.org/book/03-Twig-for-Developers
     */
    public $parserOptions = array();

    /**
     * @var TwigExtension The Twig extensions you want to load
     */
    public $parserExtensions = array();

    /**
     * @var TwigEnvironment The Twig environment for rendering templates.
     */
    private $parserInstance = null;

    /**
     * Render Twig Template
     *
     * This method will output the rendered template content
     *
     * @param string $template The path to the Twig template, relative to the Twig templates directory.
     * @param null $data
     * @return string
     */
    public function render($template, $data = null)
    {
        $env = $this->getInstance();
        $parser = $env->loadTemplate($template);

        $data = array_merge($this->all(), (array) $data);

        return $parser->render($data);
    }

    /**
     * DEPRECATION WARNING! This method will be removed in the next major point release
     *
     * Use getInstance method instead
     */
    public function getEnvironment()
    {
        return $this->getInstance();
    }

    /**
     * Creates new TwigEnvironment if it doesn't already exist, and returns it.
     *
     * @return \Twig_Environment
     */
    public function getInstance()
    {
        if (!$this->parserInstance) {
            /**
             * Check if Twig_Autoloader class exists
             * otherwise include it.
             */
            if (!class_exists('\Twig_Autoloader')) {
                require_once $this->parserDirectory . '/Autoloader.php';
            }

            \Twig_Autoloader::register();
            $loader = new \Twig_Loader_Filesystem($this->getTemplateDirs());
            $this->parserInstance = new \Twig_Environment(
                $loader,
                $this->parserOptions
            );

            foreach ($this->parserExtensions as $ext) {
                $extension = is_object($ext) ? $ext : new $ext;
                $this->parserInstance->addExtension($extension);
            }
        }

        return $this->parserInstance;
    }

    /**
     * DEPRECATION WARNING! This method will be removed in the next major point release
     *
     * Get a list of template directories
     *
     * Returns an array of templates defined by self::$twigTemplateDirs, falls
     * back to Slim\View's built-in getTemplatesDirectory method.
     *
     * @return array
     **/
    private function getTemplateDirs()
    {
        if (empty($this->twigTemplateDirs)) {
            return array($this->getTemplatesDirectory());
        }
        return $this->twigTemplateDirs;
    }
}
