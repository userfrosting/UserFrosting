<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2013-2024 Alexander Weissman & Louis Charette
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\App;

use Slim\App;
use UserFrosting\App\Controller\AppController;
use UserFrosting\Routes\RouteDefinitionInterface;

class MyRoutes implements RouteDefinitionInterface
{
    public function register(App $app): void
    {
        $app->get('/', [AppController::class, 'pageIndex'])->setName('index');
        $app->get('/about', [AppController::class, 'pageAbout'])->setName('about');
        $app->get('/legal', [AppController::class, 'pageLegal'])->setName('legal');
        $app->get('/privacy', [AppController::class, 'pagePrivacy'])->setName('privacy');
    }
}
