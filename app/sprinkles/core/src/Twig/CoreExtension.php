<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2013-2016 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */
namespace UserFrosting\Sprinkle\Core\Twig;

use Interop\Container\ContainerInterface;
use RocketTheme\Toolbox\ResourceLocator\UniformResourceLocator;
use Slim\Http\Uri;

/**
 * Extends Twig functionality for the Core sprinkle.
 *
 * @author Alex Weissman (https://alexanderweissman.com)
 */
class CoreExtension extends \Twig_Extension
{

    /**
     * @var ContainerInterface The global container object, which holds all your services.
     */
    protected $services;
    
    /**
     * Constructor.
     *
     * @param ContainerInterface $services The global container object, which holds all your services.
     */
    public function __construct(ContainerInterface $services)
    {
        $this->services = $services;
    }

    /**
     * Get the name of this extension.
     *
     * @return string
     */
    public function getName()
    {
        return 'userfrosting/core';
    }
    
    /**
     * Adds Twig functions `getAlerts` and `translate`.
     *
     * @return array[\Twig_SimpleFunction]
     */
    public function getFunctions()
    {        
        return array(
            // Add Twig function for fetching alerts
            new \Twig_SimpleFunction('getAlerts', function ($clear = true) {
                if ($clear) {
                    return $this->services['alerts']->getAndClearMessages();
                } else {
                    return $this->services['alerts']->messages();
                }
            }),
            new \Twig_SimpleFunction('translate', function ($hook, $params = array()) {
                return $this->services['translator']->translate($hook, $params);
            })
        );
    }
    
    /**
     * Adds Twig filters `unescape`.
     *
     * @return array[\Twig_SimpleFilter]
     */    
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('unescape', function ($string) {
                return html_entity_decode($string);
            })
        );
    }
    
    /**
     * Adds Twig global variables `site` and `assets`.
     *
     * @return array[mixed]
     */   
    public function getGlobals()
    {
        return array(
            'site'   => $this->services->config['site'],
            'assets' => $this->services->assets
        );
    }
}
