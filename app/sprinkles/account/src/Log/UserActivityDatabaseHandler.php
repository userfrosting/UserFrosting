<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Account\Log;

use UserFrosting\Sprinkle\Core\Log\DatabaseHandler;

/**
 * Monolog handler for storing user activities to the database.
 *
 * @author Alex Weissman (https://alexanderweissman.com)
 */
class UserActivityDatabaseHandler extends DatabaseHandler
{
    /**
     * {@inheritdoc}
     */
    protected function write(array $record)
    {
        $log = $this->classMapper->createInstance($this->modelName, $record['extra']);
        $log->save();

        if (isset($record['extra']['user_id'])) {
            $user = $this->classMapper->staticMethod('user', 'find', $record['extra']['user_id']);
            $user->lastActivity()->associate($log);
            $user->save();
        }
    }
}
