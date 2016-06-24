<?php
namespace RocketTheme\Toolbox\ResourceLocator;

use FilesystemIterator;

/**
 * Implements FilesystemIterator for uniform resource locator.
 *
 * @package RocketTheme\Toolbox\ResourceLocator
 * @author RocketTheme
 * @license MIT
 */
class UniformResourceIterator extends FilesystemIterator
{
    /**
     * @var FilesystemIterator
     */
    protected $iterator;

    /**
     * @var array
     */
    protected $found;

    /**
     * @var array
     */
    protected $stack;

    /**
     * @var string
     */
    protected $path;

    /**
     * @var int
     */
    protected $flags;

    /**
     * @var UniformResourceLocator
     */
    protected $locator;

    public function __construct($path, $flags = null, UniformResourceLocator $locator = null)
    {
        if (!$locator) {
            throw new \BadMethodCallException('Use $locator->getIterator() instead');
        }

        $this->path = $path;
        $this->setFlags($flags);
        $this->locator = $locator;
        $this->rewind();
    }

    public function current()
    {
        if ($this->flags & static::CURRENT_AS_SELF) {
            return $this;
        }
        return $this->iterator->current();
    }

    public function key()
    {
        return $this->iterator->key();
    }

    public function next()
    {
        do {
            $found = $this->findNext();
        } while ($found && !empty($this->found[$found]));

        if ($found) {
            // Mark the file as found.
            $this->found[$found] = true;
        }
    }

    public function valid()
    {
        return $this->iterator && $this->iterator->valid();
    }

    public function rewind()
    {
        $this->found = [];
        $this->stack = $this->locator->findResources($this->path);
        $this->next();
    }

    public function getUrl()
    {
        $path = $this->path . (substr($this->path, -1, 1) === '/' ? '' : '/');
        return $path . $this->iterator->getFilename();
    }

    public function seek($position)
    {
        throw new \RuntimeException('Seek not implemented');
    }

    public function getATime()
    {
        return $this->iterator->getATime();
    }

    public function getBasename($suffix = null)
    {
        return $this->iterator->getBasename($suffix);
    }

    public function getCTime()
    {
        return $this->iterator->getCTime();
    }

    public function getExtension()
    {
        return $this->iterator->getExtension();
    }

    public function getFilename()
    {
        return $this->iterator->getFilename();
    }

    public function getGroup()
    {
        return $this->iterator->getGroup();
    }

    public function getInode()
    {
        return $this->iterator->getInode();
    }

    public function getMTime()
    {
        return $this->iterator->getMTime();
    }

    public function getOwner()
    {
        return $this->iterator->getOwner();
    }

    public function getPath()
    {
        return $this->iterator->getPath();
    }

    public function getPathname()
    {
        return $this->iterator->getPathname();
    }

    public function getPerms()
    {
        return $this->iterator->getPerms();
    }

    public function getSize()
    {
        return $this->iterator->getSize();
    }

    public function getType()
    {
        return $this->iterator->getType();
    }

    public function isDir()
    {
        return $this->iterator->isDir();
    }

    public function isDot()
    {
        return $this->iterator->isDot();
    }

    public function isExecutable()
    {
        return $this->iterator->isExecutable();
    }

    public function isFile()
    {
        return $this->iterator->isFile();
    }

    public function isLink()
    {
        return $this->iterator->isLink();
    }

    public function isReadable()
    {
        return $this->iterator->isReadable();
    }

    public function isWritable()
    {
        return $this->iterator->isWritable();
    }

    public function __toString()
    {
        return $this->iterator->__toString();
    }

    public function getFlags()
    {
        return $this->flags;
    }

    public function setFlags($flags = null)
    {
        $this->flags = $flags === null ? static::KEY_AS_PATHNAME | static::CURRENT_AS_SELF | static::SKIP_DOTS : $flags;

        if ($this->iterator) {
            $this->iterator->setFlags($this->flags);
        }
    }

    protected function findNext()
    {
        if ($this->iterator) {
            $this->iterator->next();
        }

        if (!$this->valid()) {
            do {
                // Move to the next iterator if it exists.
                $path = array_shift($this->stack);

                if (!isset($path)) {
                    return null;
                }

                $this->iterator = new \FilesystemIterator($path, $this->getFlags());
            } while (!$this->iterator->valid());
        }

        return $this->getFilename();
    }
}
