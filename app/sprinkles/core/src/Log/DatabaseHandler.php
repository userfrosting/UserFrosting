<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */
namespace UserFrosting\Sprinkle\Core\Log;

use Monolog\Logger;
use Monolog\Handler\AbstractProcessingHandler;

/**
 * Monolog handler for storing the record to a database.
 *
 * @author Alex Weissman (https://alexanderweissman.com)
 */
class DatabaseHandler extends AbstractProcessingHandler
{
    /**
     * @var UserFrosting\Sprinkle\Core\Util\ClassMapper
     */
    protected $classMapper;

    /**
     * @var string
     */
    protected $modelIdentifier;

    /**
     * Create a new DatabaseHandler object.
     *
     * @param ClassMapper $classMapper Maps the modelIdentifier to the specific Eloquent model.
     * @param string $modelIdentifier
     * @param int     $level  The minimum logging level at which this handler will be triggered
     * @param Boolean $bubble Whether the messages that are handled can bubble up the stack or not
     */
    public function __construct($classMapper, $modelIdentifier, $level = Logger::DEBUG, $bubble = true)
    {
        $this->classMapper = $classMapper;
        $this->modelName = $modelIdentifier;
        parent::__construct($level, $bubble);
    }

    /**
     * {@inheritDoc}
     */
    protected function write(array $record)
    {
        $log = $this->classMapper->createInstance($this->modelName, $record['extra']);
        $log->save();
    }
}
