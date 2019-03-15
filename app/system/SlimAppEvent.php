<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\System;

use RocketTheme\Toolbox\Event\Event;
use Slim\App;

/**
 * Used for events that need to access the Slim application.
 */
class SlimAppEvent extends Event
{
    /**
     * @var App
     */
    protected $app;

    public function __construct(App $app)
    {
        $this->app = $app;
    }

    /**
     * @return App
     */
    public function getApp()
    {
        return $this->app;
    }
}
