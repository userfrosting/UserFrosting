<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Account\Log;

/**
 * Monolog processor for constructing the user activity message.
 *
 * @author Alex Weissman (https://alexanderweissman.com)
 */
class UserActivityProcessor
{
    /**
     * @var int
     */
    protected $userId;

    /**
     * @param int $userId The id of the user for whom we will be logging activities.
     */
    public function __construct($userId)
    {
        $this->userId = $userId;
    }

    /**
     * @param  array $record
     * @return array
     */
    public function __invoke(array $record)
    {
        $additionalFields = [
            'ip_address'  => isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : null,
            'user_id'     => $this->userId,
            'occurred_at' => $record['datetime'],
            'description' => $record['message']
        ];

        $record['extra'] = array_replace_recursive($record['extra'], $additionalFields, $record['context']);

        return $record;
    }
}
