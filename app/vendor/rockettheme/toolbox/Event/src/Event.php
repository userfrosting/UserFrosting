<?php
namespace RocketTheme\Toolbox\Event;

use RocketTheme\Toolbox\ArrayTraits\ArrayAccess;
use RocketTheme\Toolbox\ArrayTraits\Constructor;
use Symfony\Component\EventDispatcher\Event as BaseEvent;

/**
 * Implements Symfony Event interface.
 *
 * @package RocketTheme\Toolbox\Event
 * @author RocketTheme
 * @license MIT
 */
class Event extends BaseEvent implements \ArrayAccess
{
    use ArrayAccess, Constructor;

    /**
     * @var array
     */
    protected $items = array();
}
