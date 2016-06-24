<?php
namespace RocketTheme\Toolbox\File;

/**
 * Implements Universal File Reader.
 *
 * @package RocketTheme\Toolbox\File
 * @author RocketTheme
 * @license MIT
 */
class File implements FileInterface
{
    /**
     * @var string
     */
    protected $filename;

    /**
     * @var resource
     */
    protected $handle;

    /**
     * @var bool|null
     */
    protected $locked;

    /**
     * @var string
     */
    protected $extension;

    /**
     * @var string  Raw file contents.
     */
    protected $raw;

    /**
     * @var array  Parsed file contents.
     */
    protected $content;

    /**
     * @var array
     */
    protected $settings = [];

    /**
     * @var array|File[]
     */
    static protected $instances = array();

    /**
     * Get file instance.
     *
     * @param  string  $filename
     * @return FileInterface
     */
    public static function instance($filename)
    {
        if (!is_string($filename) && $filename) {
            throw new \InvalidArgumentException('Filename should be non-empty string');
        }
        if (!isset(static::$instances[$filename])) {
            static::$instances[$filename] = new static;
            static::$instances[$filename]->init($filename);
        }
        return static::$instances[$filename];
    }

    /**
     * Set/get settings.
     *
     * @param array $settings
     * @return array
     */
    public function settings(array $settings = null)
    {
        if ($settings !== null) {
            $this->settings = $settings;
        }

        return $this->settings;
    }

    /**
     * Get setting.
     *
     * @param string $setting
     * @param mixed $default
     * @return mixed
     */
    public function setting($setting, $default = null)
    {
        return isset($this->settings[$setting]) ? $this->settings[$setting] : $default;
    }

    /**
     * Prevent constructor from being used.
     *
     * @internal
     */
    protected function __construct()
    {
    }

    /**
     * Prevent cloning.
     *
     * @internal
     */
    protected function __clone()
    {
        //Me not like clones! Me smash clones!
    }

    /**
     * Set filename.
     *
     * @param $filename
     */
    protected function init($filename)
    {
        $this->filename = $filename;
    }

    /**
     * Free the file instance.
     */
    public function free()
    {
        if ($this->locked) {
            $this->unlock();
        }
        $this->content = null;
        $this->raw = null;

        unset(static::$instances[$this->filename]);
    }

    /**
     * Get/set the file location.
     *
     * @param  string $var
     * @return string
     */
    public function filename($var = null)
    {
        if ($var !== null) {
            $this->filename = $var;
        }
        return $this->filename;
    }

    /**
     * Return basename of the file.
     *
     * @return string
     */
    public function basename()
    {
        return basename($this->filename, $this->extension);
    }

    /**
     * Check if file exits.
     *
     * @return bool
     */
    public function exists()
    {
        return is_file($this->filename);
    }

    /**
     * Return file modification time.
     *
     * @return int|bool Timestamp or false if file doesn't exist.
     */
    public function modified()
    {
        return is_file($this->filename) ? filemtime($this->filename) : false;
    }

    /**
     * Lock file for writing. You need to manually unlock().
     *
     * @param bool $block  For non-blocking lock, set the parameter to false.
     * @return bool
     * @throws \RuntimeException
     */
    public function lock($block = true)
    {
        if (!$this->handle) {
            if (!$this->mkdir(dirname($this->filename))) {
                throw new \RuntimeException('Creating directory failed for ' . $this->filename);
            }
            $this->handle = @fopen($this->filename, 'cb+');
            if (!$this->handle) {
                $error = error_get_last();

                throw new \RuntimeException("Opening file for writing failed on error {$error['message']}");
            }
        }
        $lock = $block ? LOCK_EX : LOCK_EX | LOCK_NB;
        return $this->locked = $this->handle ? flock($this->handle, $lock) : false;
    }

    /**
     * Returns true if file has been locked for writing.
     *
     * @return bool|null True = locked, false = failed, null = not locked.
     */
    public function locked()
    {
        return $this->locked;
    }

    /**
     * Unlock file.
     *
     * @return bool
     */
    public function unlock()
    {
        if (!$this->handle) {
            return;
        }
        if ($this->locked) {
            flock($this->handle, LOCK_UN);
            $this->locked = null;
        }
        fclose($this->handle);
        $this->handle = null;
    }

    /**
     * Check if file can be written.
     *
     * @return bool
     */
    public function writable()
    {
        return is_writable($this->filename) || $this->writableDir(dirname($this->filename));
    }

    /**
     * (Re)Load a file and return RAW file contents.
     *
     * @return string
     */
    public function load()
    {
        $this->raw = $this->exists() ? (string) file_get_contents($this->filename) : '';
        $this->content = null;

        return $this->raw;
    }

    /**
     * Get/set raw file contents.
     *
     * @param string $var
     * @return string
     */
    public function raw($var = null)
    {
        if ($var !== null) {
            $this->raw = (string) $var;
            $this->content = null;
        }

        if (!is_string($this->raw)) {
            $this->raw = $this->load();
        }

        return $this->raw;
    }

    /**
     * Get/set parsed file contents.
     *
     * @param mixed $var
     * @return string|array
     */
    public function content($var = null)
    {
        if ($var !== null) {
            $this->content = $this->check($var);

            // Update RAW, too.
            $this->raw = $this->encode($this->content);

        } elseif ($this->content === null) {
            // Decode RAW file.
            $this->content = $this->decode($this->raw());
        }

        return $this->content;
    }

    /**
     * Save file.
     *
     * @param  mixed  $data  Optional data to be saved, usually array.
     * @throws \RuntimeException
     */
    public function save($data = null)
    {
        if ($data !== null) {
            $this->content($data);
        }

        if (!$this->locked) {
            // Obtain blocking lock or fail.
            if (!$this->lock()) {
                throw new \RuntimeException('Obtaining write lock failed on file: ' . $this->filename);
            }
            $lock = true;
        }

        // As we are using non-truncating locking, make sure that the file is empty before writing.
        if (@ftruncate($this->handle, 0) === false || @fwrite($this->handle, $this->raw()) === false) {
            $this->unlock();
            throw new \RuntimeException('Saving file failed: ' . $this->filename);
        }

        if (isset($lock)) {
            $this->unlock();
        }

        // Touch the directory as well, thus marking it modified.
        @touch(dirname($this->filename));
    }

    /**
     * Rename file in the filesystem if it exists.
     *
     * @param $filename
     * @return bool
     */
    public function rename($filename)
    {
        if ($this->exists() && !@rename($this->filename, $filename)) {
            return false;
        }

        unset(static::$instances[$this->filename]);
        static::$instances[$filename] = $this;

        $this->filename = $filename;

        return true;
    }

    /**
     * Delete file from filesystem.
     *
     * @return bool
     */
    public function delete()
    {
        return unlink($this->filename);
    }

    /**
     * Check contents and make sure it is in correct format.
     *
     * Override in derived class.
     *
     * @param string $var
     * @return string
     */
    protected function check($var)
    {
        return (string) $var;
    }

    /**
     * Encode contents into RAW string.
     *
     * Override in derived class.
     *
     * @param string $var
     * @return string
     */
    protected function encode($var)
    {
        return (string) $var;
    }

    /**
     * Decode RAW string into contents.
     *
     * Override in derived class.
     *
     * @param string $var
     * @return string mixed
     */
    protected function decode($var)
    {
        return (string) $var;
    }

    /**
     * @param  string  $dir
     * @return bool
     * @throws \RuntimeException
     * @internal
     */
    protected function mkdir($dir)
    {
        // Silence error for open_basedir; should fail in mkdir instead.
        if (!@is_dir($dir)) {
            $success = @mkdir($dir, 0777, true);

            if (!$success) {
                $error = error_get_last();

                throw new \RuntimeException("Creating directory '{$dir}' failed on error {$error['message']}");
            }
        }

        return true;
    }

    /**
     * @param  string  $dir
     * @return bool
     * @internal
     */
    protected function writableDir($dir)
    {
        if ($dir && !file_exists($dir)) {
            return $this->writableDir(dirname($dir));
        }

        return $dir && is_dir($dir) && is_writable($dir);
    }
}
