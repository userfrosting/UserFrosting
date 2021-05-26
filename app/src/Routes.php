<?php

/*
 * UserFrosting Framework (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/framework
 * @copyright Copyright (c) 2013-2021 Alexander Weissman, Louis Charette, Jordan Mele
 * @license   https://github.com/userfrosting/framework/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\App;

use Slim\App;
use UserFrosting\App\Controller\AppController;
use UserFrosting\Routes\RouteDefinitionInterface;

class Routes implements RouteDefinitionInterface
{
    public function register(App $app): void
    {
        $app->get('/', [AppController::class, 'pageIndex'])->setName('index');
        $app->get('/about', [AppController::class, 'pageAbout']);
        $app->get('/legal', [AppController::class, 'pageLegal']);
        $app->get('/privacy', [AppController::class, 'pagePrivacy']);
    }
}
