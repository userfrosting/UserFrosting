<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */
namespace UserFrosting\System;

use RocketTheme\Toolbox\Event\Event;
use Slim\App;

/**
 * Used for events that need to access the Slim application.
 */
class SlimAppEvent extends Event
{
    protected $app;

    public function __construct(App $app)
    {
        $this->app = $app;
    }

    public function getApp()
    {
        return $this->app;
    }
}
