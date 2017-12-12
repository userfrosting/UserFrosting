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
use UserFrosting\Sprinkle\Core\Util\Util;
use UserFrosting\Assets\AssetsTemplatePlugin;

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
            }, [
                'is_safe' => ['html']
            ])
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
            /**
             * Converts phone numbers to a standard format.
             *
             * @param   String   $num   A unformatted phone number
             * @return  String   Returns the formatted phone number
             */
            new \Twig_SimpleFilter('phone', function ($num) {
                return Util::formatPhoneNumber($num);
            }),
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
        // CSRF token name and value
        $csrfNameKey = $this->services->csrf->getTokenNameKey();
        $csrfValueKey = $this->services->csrf->getTokenValueKey();
        $csrfName = $this->services->csrf->getTokenName();
        $csrfValue = $this->services->csrf->getTokenValue();

        $csrf = [
            'csrf'   => [
                'keys' => [
                    'name'  => $csrfNameKey,
                    'value' => $csrfValueKey
                ],
                'name'  => $csrfName,
                'value' => $csrfValue
            ]
        ];

        $site = array_replace_recursive($this->services->config['site'], $csrf);

        return [
            'site'   => $site,
            'assets' => new AssetsTemplatePlugin($this->services->assets)
        ];
    }
}
