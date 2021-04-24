<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Twig;

use Psr\Container\ContainerInterface;
use Twig\Extension\AbstractExtension;
use Twig\Extension\GlobalsInterface;
use Twig\TwigFilter;
use Twig\TwigFunction;
use UserFrosting\Assets\AssetsTemplatePlugin;
use UserFrosting\Sprinkle\Core\Util\Util;

/**
 * Extends Twig functionality for the Core sprinkle.
 *
 * @author Alex Weissman (https://alexanderweissman.com)
 */
class CoreExtension extends AbstractExtension implements GlobalsInterface
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
     * Adds Twig functions `getAlerts` and `translate`.
     *
     * @return TwigFunction[]
     */
    public function getFunctions()
    {
        return [
            // Add Twig function for fetching alerts
            new TwigFunction('getAlerts', function ($clear = true) {
                $alerts = $this->services->alerts;
                if ($clear) {
                    return $alerts->getAndClearMessages();
                } else {
                    return $alerts->messages();
                }
            }),
            new TwigFunction('translate', function ($hook, $params = []) {
                return $this->services['translator']->translate($hook, $params);
            }, [
                'is_safe' => ['html'],
            ]),
        ];
    }

    /**
     * Adds Twig filters `unescape`.
     *
     * @return TwigFilter[]
     */
    public function getFilters()
    {
        return [
            /*
             * Converts phone numbers to a standard format.
             *
             * @param   string   $num   A unformatted phone number
             * @return  string   Returns the formatted phone number
             */
            new TwigFilter('phone', function ($num) {
                return Util::formatPhoneNumber($num);
            }),
            new TwigFilter('unescape', function ($string) {
                return html_entity_decode($string);
            }),
        ];
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
                    'value' => $csrfValueKey,
                ],
                'name'  => $csrfName,
                'value' => $csrfValue,
            ],
        ];

        $site = array_replace_recursive($this->services->config['site'], $csrf);

        return [
            'site'          => $site,
            'assets'        => new AssetsTemplatePlugin($this->services->assets),
            'currentLocale' => $this->services->locale->getLocaleIndentifier(),
        ];
    }
}
