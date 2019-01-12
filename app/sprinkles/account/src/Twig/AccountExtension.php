<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Account\Twig;

use Interop\Container\ContainerInterface;
use UserFrosting\Support\Repository\Repository as Config;

/**
 * Extends Twig functionality for the Account sprinkle.
 *
 * @author Alex Weissman (https://alexanderweissman.com)
 */
class AccountExtension extends \Twig_Extension implements \Twig_Extension_GlobalsInterface
{
    /**
     * @var ContainerInterface
     */
    protected $services;

    /**
     * @var Config
     */
    protected $config;

    /**
     * @param ContainerInterface $services
     */
    public function __construct(ContainerInterface $services)
    {
        $this->services = $services;
        $this->config = $services->config;
    }

    public function getName()
    {
        return 'userfrosting/account';
    }

    public function getFunctions()
    {
        return [
            // Add Twig function for checking permissions during dynamic menu rendering
            new \Twig_SimpleFunction('checkAccess', function ($slug, $params = []) {
                $authorizer = $this->services->authorizer;
                $currentUser = $this->services->currentUser;

                return $authorizer->checkAccess($currentUser, $slug, $params);
            }),
            new \Twig_SimpleFunction('checkAuthenticated', function () {
                $authenticator = $this->services->authenticator;

                return $authenticator->check();
            })
        ];
    }

    public function getGlobals()
    {
        try {
            $currentUser = $this->services->currentUser;
        } catch (\Exception $e) {
            $currentUser = null;
        }

        return [
            'current_user'   => $currentUser
        ];
    }
}
