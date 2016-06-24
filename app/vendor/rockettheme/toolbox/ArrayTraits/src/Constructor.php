<?php
namespace RocketTheme\Toolbox\ArrayTraits;

/**
 * Implements Constructor for setting items.
 *
 * @package RocketTheme\Toolbox\ArrayTraits
 * @author RocketTheme
 * @license MIT
 *
 * @property array $items
 */
trait Constructor
{
    /**
     * Constructor to initialize array.
     *
     * @param  array  $items  Initial items inside the iterator.
     */
    public function __construct(array $items = array())
    {
        $this->items = $items;
    }
}
