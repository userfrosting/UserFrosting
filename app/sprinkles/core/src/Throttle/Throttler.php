<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Throttle;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use UserFrosting\Sprinkle\Core\Util\ClassMapper;

/**
 * Handles throttling (rate limiting) of specific types of requests.
 *
 * @author Alex Weissman (https://alexanderweissman.com)
 */
class Throttler
{
    /**
     * @var ClassMapper
     */
    protected $classMapper;

    /**
     * @var ThrottleRule[] An array mapping throttle names to throttle rules.
     */
    protected $throttleRules;

    /**
     * Create a new Throttler object.
     *
     * @param ClassMapper $classMapper Maps generic class identifiers to specific class names.
     */
    public function __construct(ClassMapper $classMapper)
    {
        $this->classMapper = $classMapper;
        $this->throttleRules = [];
    }

    /**
     * Add a throttling rule for a particular throttle event type.
     *
     * @param string            $type The type of throttle event to check against.
     * @param ThrottleRule|null $rule The rule to use when throttling this type of event.
     */
    public function addThrottleRule($type, ThrottleRule $rule = null)
    {
        if (!($rule instanceof ThrottleRule || ($rule === null))) {
            throw new ThrottlerException('$rule must be of type ThrottleRule (or null).');
        }

        $this->throttleRules[$type] = $rule;

        return $this;
    }

    /**
     * Check the current request against a specified throttle rule.
     *
     * @param  string  $type        The type of throttle event to check against.
     * @param  mixed[] $requestData Any additional request parameters to use in checking the throttle.
     * @return bool
     */
    public function getDelay($type, $requestData = [])
    {
        $throttleRule = $this->getRule($type);

        if (is_null($throttleRule)) {
            return 0;
        }

        // Get earliest time to start looking for throttleable events
        $startTime = Carbon::now()
            ->subSeconds($throttleRule->getInterval());

        // Fetch all throttle events of the specified type, that match the specified rule
        if ($throttleRule->getMethod() == 'ip') {
            $events = $this->classMapper->staticMethod('throttle', 'where', 'type', $type)
                ->where('created_at', '>', $startTime)
                ->where('ip', $_SERVER['REMOTE_ADDR'])
                ->get();
        } else {
            $events = $this->classMapper->staticMethod('throttle', 'where', 'type', $type)
                ->where('created_at', '>', $startTime)
                ->get();

            // Filter out only events that match the required JSON data
            $events = $events->filter(function ($item, $key) use ($requestData) {
                $data = json_decode($item->request_data);

                // If a field is not specified in the logged data, or it doesn't match the value we're searching for,
                // then filter out this event from the collection.
                foreach ($requestData as $name => $value) {
                    if (!isset($data->$name) || ($data->$name != $value)) {
                        return false;
                    }
                }

                return true;
            });
        }

        // Check the collection of events against the specified throttle rule.
        return $this->computeDelay($events, $throttleRule);
    }

    /**
     * Get a registered rule of a particular type.
     *
     * @param  string             $type
     * @throws ThrottlerException
     * @return ThrottleRule[]
     */
    public function getRule($type)
    {
        if (!array_key_exists($type, $this->throttleRules)) {
            throw new ThrottlerException("The throttling rule for '$type' could not be found.");
        }

        return $this->throttleRules[$type];
    }

    /**
     * Get the current throttling rules.
     *
     * @return ThrottleRule[]
     */
    public function getThrottleRules()
    {
        return $this->throttleRules;
    }

    /**
     * Log a throttleable event to the database.
     *
     * @param string   $type        the type of event
     * @param string[] $requestData an array of field names => values that are relevant to throttling for this event (e.g. username, email, etc).
     */
    public function logEvent($type, $requestData = [])
    {
        // Just a check to make sure the rule exists
        $throttleRule = $this->getRule($type);

        if (is_null($throttleRule)) {
            return $this;
        }

        $event = $this->classMapper->createInstance('throttle', [
            'type'         => $type,
            'ip'           => $_SERVER['REMOTE_ADDR'],
            'request_data' => json_encode($requestData)
        ]);

        $event->save();

        return $this;
    }

    /**
     * Returns the current delay for a specified throttle rule.
     *
     * @param  Collection   $events       a Collection of throttle events.
     * @param  ThrottleRule $throttleRule a rule representing the strategy to use for throttling a particular type of event.
     * @return int          seconds remaining until a particular event is permitted to be attempted again.
     */
    protected function computeDelay(Collection $events, ThrottleRule $throttleRule)
    {
        // If no matching events found, then there is no delay
        if (!$events->count()) {
            return 0;
        }

        // Great, now we compare our delay against the most recent attempt
        $lastEvent = $events->last();

        return $throttleRule->getDelay($lastEvent->created_at, $events->count());
    }
}
