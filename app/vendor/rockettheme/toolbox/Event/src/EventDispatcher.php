<?php
namespace RocketTheme\Toolbox\Event;

use Symfony\Component\EventDispatcher\Event as BaseEvent;
use Symfony\Component\EventDispatcher\EventDispatcher as BaseEventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Implements Symfony EventDispatcher interface.
 *
 * @package RocketTheme\Toolbox\Event
 * @author RocketTheme
 * @license MIT
 */
class EventDispatcher extends BaseEventDispatcher implements EventDispatcherInterface
{
    public function dispatch($eventName, BaseEvent $event = null)
    {
        if (null === $event) {
            $event = new Event();
        }

        return parent::dispatch($eventName, $event);
    }
}
