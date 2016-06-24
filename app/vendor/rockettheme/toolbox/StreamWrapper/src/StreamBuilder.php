<?php
namespace RocketTheme\Toolbox\StreamWrapper;

/**
 * Class StreamBuilder
 * @package RocketTheme\Toolbox\StreamWrapper
 */
class StreamBuilder
{
    /**
     * @var array
     */
    protected $items = [];

    public function __construct(array $items = [])
    {
        foreach ($items as $scheme => $handler) {
            $this->add($scheme, $handler);
        }
    }

    /**
     * @param $scheme
     * @param $handler
     * @return $this
     * @throws \InvalidArgumentException
     */
    public function add($scheme, $handler)
    {
        if (isset($this->items[$scheme])) {
            if ($handler == $this->items[$scheme]) {
                return $this;
            }
            throw new \InvalidArgumentException("Stream '{$scheme}' has already been initialized.");
        }

        if (!is_subclass_of($handler, 'RocketTheme\Toolbox\StreamWrapper\StreamInterface')) {
            throw new \InvalidArgumentException("Stream '{$scheme}' has unknown or invalid type.");
        }

        if (!@stream_wrapper_register($scheme, $handler)) {
            throw new \InvalidArgumentException("Stream '{$scheme}' could not be initialized.");
        }

        $this->items[$scheme] = $handler;

        return $this;
    }

    /**
     * @param $scheme
     * @return $this
     */
    public function remove($scheme)
    {
        if (isset($this->items[$scheme])) {
            stream_wrapper_unregister($scheme);
            unset($this->items[$scheme]);
        }

        return $this;
    }

    /**
     * @return array
     */
    public function getStreams()
    {
        return $this->items;
    }

    /**
     * @param $scheme
     * @return bool
     */
    public function isStream($scheme)
    {
        return isset($this->items[$scheme]);
    }

    /**
     * @param $scheme
     * @return null
     */
    public function getStreamType($scheme)
    {
        return isset($this->items[$scheme]) ? $this->items[$scheme] : null;
    }
}
