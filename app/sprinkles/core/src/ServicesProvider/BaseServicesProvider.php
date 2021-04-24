<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\ServicesProvider;

use Psr\Container\ContainerInterface;

/**
 * UserFrosting services provider Interface.
 *
 * @TODO : Rename this "ServiceProvider" and move the definition in the current "ServiceProvider" to separate classes (Target: UF 5.0)
 */
abstract class BaseServicesProvider
{
    /**
     * @var ContainerInterface The base Container
     */
    protected $ci;

    /**
     * Create a new service provider instance.
     *
     * @param ContainerInterface $ci
     */
    public function __construct(ContainerInterface $ci)
    {
        $this->ci = $ci;
    }

    /**
     * Register defined services.
     */
    abstract public function register(): void;
}
