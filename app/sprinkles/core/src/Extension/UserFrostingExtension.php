<?php

namespace UserFrosting\Core\Extension;

use RocketTheme\Toolbox\ResourceLocator\UniformResourceLocator;
use Slim\Http\Uri;

class UserFrostingExtension extends \Twig_Extension
{

    protected $services;
    protected $config;

    public function __construct(\Slim\Container $services)
    {
        $this->services = $services;
        $this->config = $services->get('config');
    }

    public function getName()
    {
        return 'userfrosting';
    }
    
    public function getFunctions()
    {        
        return array(
            // Add Twig function for fetching alerts
            new \Twig_SimpleFunction('getAlerts', function ($clear = true) {
                if ($clear)
                    return $this->services['alerts']->getAndClearMessages();
                else
                    return $this->services['alerts']->messages();
            }),
            new \Twig_SimpleFunction('translate', function ($hook, $params = []) {
                return $this->services['translator']->translate($hook, $params);
            })
        );
    }
    
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('unescape', function ($string) {
                return html_entity_decode($string);
            })
        );
    }
    
    public function getGlobals()
    {
        return array(
            'site'   => $this->config['site'],
            'assets' => $this->services->get('assets')
        );
    }

}
