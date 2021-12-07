<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\App;

use Fullpipe\TwigWebpackExtension\WebpackExtension;
use UserFrosting\ServicesProvider\ServicesProviderInterface;
use UserFrosting\UniformResourceLocator\ResourceLocatorInterface;

class Services implements ServicesProviderInterface
{
    public function register(): array
    {
        return [
            WebpackExtension::class => function (ResourceLocatorInterface $locator) {
                $publicPath = $locator->getResource('public://');
                $manifest = $locator->getResource('public://assets/manifest.json');
                $extension = new WebpackExtension((string) $manifest, (string) $publicPath);

                return $extension;
            },
        ];
    }
}
