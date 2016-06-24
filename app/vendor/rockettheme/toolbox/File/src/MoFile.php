<?php
namespace RocketTheme\Toolbox\File;

/**
 * Implements Gettext Mo File reader (readonly).
 *
 * @package RocketTheme\Toolbox\File
 * @author RocketTheme
 * @license MIT
 */
class MoFile extends File
{
    /**
     * @var string
     */
    protected $extension = '.mo';

    protected $pos = 0;
    protected $str;
    protected $len;
    protected $endian;

    /**
     * @var array|File[]
     */
    static protected $instances = array();

    /**
     * File can never be written.
     *
     * @return bool
     */
    public function writable()
    {
        return false;
    }

    /**
     * Prevent saving file.
     *
     * @throws \BadMethodCallException
     */
    public function save($data = null)
    {
        throw new \BadMethodCallException('save() not supported for .mo files.');
    }

    /**
     * Prevent deleting file from filesystem.
     *
     * @return bool
     */
    public function delete()
    {
        return false;
    }

    /**
     * @param $var
     * @return array
     * @throws \RuntimeException
     */
    public function decode($var)
    {
        $this->endian = 'V';
        $this->str = $var;
        $this->len = strlen($var);

        $magic = $this->readInt() & 0xffffffff;

        if ($magic === 0x950412de) {
            // Low endian.
            $this->endian = 'V';
        } elseif ($magic === 0xde120495) {
            // Big endian.
            $this->endian = 'N';
        } else {
            throw new \RuntimeException('Not a Gettext file (.mo).');
        }

        // Skip revision number.
        $rev = $this->readInt();
        // Total count.
        $total = $this->readInt();
        // Offset of original table.
        $originals = $this->readInt();
        // Offset of translation table.
        $translations = $this->readInt();

        // Each table consists of string length and offset of the string.
        $this->seek($originals);
        $table_originals = $this->readIntArray($total * 2);
        $this->seek($translations);
        $table_translations = $this->readIntArray($total * 2);

        $items = [];
        for ($i = 0; $i < $total; $i++) {
            $this->seek($table_originals[$i * 2 + 2]);

            // TODO: Original string can have context concatenated on it. We do not yet support that.
            $original = $this->read($table_originals[$i * 2 + 1]);

            if ($original) {
                $this->seek($table_translations[$i * 2 + 2]);

                // TODO: Plural forms are stored by letting the plural of the original string follow the singular of the original string, separated through a NUL byte.
                $translated = $this->read($table_translations[$i * 2 + 1]);
                $items[$original] = $translated;
            }
        }

        return $items;
    }

    /**
     * @return int
     */
    protected function readInt()
    {
        $read = $this->read(4);

        if ($read === false) {
            return false;
        }

        $read = unpack($this->endian, $read);

        return array_shift($read);
    }

    /**
     * @param $count
     * @return array
     */
    protected function readIntArray($count)
    {
        return unpack($this->endian . $count, $this->read(4 * $count));
    }

    /**
     * @param $bytes
     * @return string
     */
    private function read($bytes)
    {
        $data = substr($this->str, $this->pos, $bytes);
        $this->seek($this->pos + $bytes);
        return $data;
    }

    /**
     * @param $pos
     * @return mixed
     */
    private function seek($pos)
    {
        $this->pos = $pos < $this->len ? $pos : $this->len;
        return $this->pos;
    }
}
