<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */
namespace UserFrosting\Sprinkle\Account\Twig;

use Interop\Container\ContainerInterface;
use RocketTheme\Toolbox\ResourceLocator\UniformResourceLocator;
use Slim\Http\Uri;

/**
 * Extends Twig functionality for the Account sprinkle.
 *
 * @author Alex Weissman (https://alexanderweissman.com)
 */
class AccountExtension extends \Twig_Extension implements \Twig_Extension_GlobalsInterface
{

    protected $services;
    protected $config;

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
        return array(
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
        );
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
