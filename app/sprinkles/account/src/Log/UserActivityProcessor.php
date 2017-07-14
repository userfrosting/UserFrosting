<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */
namespace UserFrosting\Sprinkle\Account\Log;

use Monolog\Logger;

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

    public function __invoke(array $record)
    {
        $additionalFields = [
            'ip_address'  => $_SERVER['REMOTE_ADDR'],
            'user_id'     => $this->userId,
            'occurred_at' => $record['datetime'],
            'description' => $record['message']
        ];

        $record['extra'] = array_replace_recursive($record['extra'], $additionalFields, $record['context']);

        return $record;
    }
}
