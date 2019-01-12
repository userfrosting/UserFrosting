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

/**
 * ThrottleRule Class
 *
 * Represents a request throttling rule.
 * @author Alex Weissman (https://alexanderweissman.com)
 */
class ThrottleRule
{
    /** @var string Set to 'ip' for ip-based throttling, 'data' for request-data-based throttling. */
    protected $method;

    /** @var int The amount of time, in seconds, to look back in determining attempts to consider. */
    protected $interval;

    /**
     * @var int[] A mapping of minimum observation counts (x) to delays (y), in seconds.
     *            Any throttleable event that has occurred more than x times in this rule's interval,
     *            must wait y seconds after the last occurrence before another attempt is permitted.
     */
    protected $delays;

    /**
     * Create a new ThrottleRule object.
     *
     * @param string $method   Set to 'ip' for ip-based throttling, 'data' for request-data-based throttling.
     * @param int    $interval The amount of time, in seconds, to look back in determining attempts to consider.
     * @param int[]  $delays   A mapping of minimum observation counts (x) to delays (y), in seconds.
     */
    public function __construct($method, $interval, $delays)
    {
        $this->setMethod($method);
        $this->setInterval($interval);
        $this->setDelays($delays);
    }

    /**
     * Get the current delay on this rule for a particular number of event counts.
     *
     * @param Carbon $lastEventTime The timestamp for the last countable event.
     * @param int    $count         The total number of events which have occurred in an interval.
     */
    public function getDelay(Carbon $lastEventTime, $count)
    {
        // Zero occurrences always maps to a delay of 0 seconds.
        if ($count == 0) {
            return 0;
        }

        foreach ($this->delays as $observations => $delay) {
            // Skip any delay rules for which we haven't met the requisite number of observations
            if ($count < $observations) {
                continue;
            }

            // If this rule meets the observed number of events, and violates the required delay, then return the remaining time left
            if ($lastEventTime->diffInSeconds() < $delay) {
                return $lastEventTime->addSeconds($delay)->diffInSeconds();
            }
        }

        return 0;
    }

    /**
     * Gets the current mapping of attempts (int) to delays (seconds).
     *
     * @return int[]
     */
    public function getDelays()
    {
        return $this->delays;
    }

    /**
     * Gets the current throttling interval (seconds).
     *
     * @return int
     */
    public function getInterval()
    {
        return $this->interval;
    }

    /**
     * Gets the current throttling method ('ip' or 'data').
     *
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * Sets the current mapping of attempts (int) to delays (seconds).
     *
     * @param int[] $delays A mapping of minimum observation counts (x) to delays (y), in seconds.
     */
    public function setDelays($delays)
    {
        // Sort the array by key, from highest to lowest value
        $this->delays = $delays;
        krsort($this->delays);

        return $this;
    }

    /**
     * Sets the current throttling interval (seconds).
     *
     * @param int $interval The amount of time, in seconds, to look back in determining attempts to consider.
     */
    public function setInterval($interval)
    {
        $this->interval = $interval;

        return $this;
    }

    /**
     * Sets the current throttling method ('ip' or 'data').
     *
     * @param string $method Set to 'ip' for ip-based throttling, 'data' for request-data-based throttling.
     */
    public function setMethod($method)
    {
        $this->method = $method;

        return $this;
    }
}
