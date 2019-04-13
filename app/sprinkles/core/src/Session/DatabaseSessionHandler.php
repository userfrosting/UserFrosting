<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Session;

use Illuminate\Session\DatabaseSessionHandler as LaravelDatabaseSessionHandler;

/**
 * Temp class until we update to Laravel 5.5.
 * A bug was fixed in 5.5, which caused https://github.com/userfrosting/UserFrosting/issues/952
 * @see https://github.com/laravel/framework/commit/24356a8ca677ba589b8f2d00f24ce3e9a7a1e02d#diff-9a772ff9941d635b86a27ca1ea149e73
 */
class DatabaseSessionHandler extends LaravelDatabaseSessionHandler
{
    /**
     * {@inheritdoc}
     */
    public function read($sessionId)
    {
        $session = (object) $this->getQuery()->find($sessionId);

        if ($this->expired($session)) {
            $this->exists = true;

            return '';
        }

        if (isset($session->payload)) {
            $this->exists = true;

            return base64_decode($session->payload);
        }
        
        return '';
    }
}
