<?php

namespace Birke\Rememberme\Storage;

/**
 * File-Based Storage
 */
class File implements StorageInterface
{
    /**
     * @var string
     */
    protected $path = "";

    /**
     * @var string
     */
    protected $suffix = ".txt";

    /**
     * @param string $path
     * @param string $suffix
     */
    public function __construct($path = "", $suffix = ".txt")
    {
        $this->path = $path;
        $this->suffix = $suffix;
    }

    /**
     * @param mixed $credential
     * @param string $token
     * @param string $persistentToken
     * @return int
     */
    public function findTriplet($credential, $token, $persistentToken)
    {
        // Hash the tokens, because they can contain a salt and can be accessed in the file system
        $persistentToken = sha1($persistentToken);
        $token = sha1($token);
        $fn = $this->getFilename($credential, $persistentToken);

        if (!file_exists($fn)) {
            return self::TRIPLET_NOT_FOUND;
        }

        $fileToken = trim(file_get_contents($fn));

        if ($fileToken == $token) {
            return self::TRIPLET_FOUND;
        }

        return self::TRIPLET_INVALID;
    }

    /**
     * @param mixed $credential
     * @param string $token
     * @param string $persistentToken
     * @param int $expire
     * @return $this
     */
    public function storeTriplet($credential, $token, $persistentToken, $expire = 0)
    {
        // Hash the tokens, because they can contain a salt and can be accessed in the file system
        $persistentToken = sha1($persistentToken);
        $token = sha1($token);
        $fn = $this->getFilename($credential, $persistentToken);
        file_put_contents($fn, $token);
        return $this;
    }

    /**
     * @param mixed $credential
     * @param string $persistentToken
     */
    public function cleanTriplet($credential, $persistentToken)
    {
        $persistentToken = sha1($persistentToken);
        $fn = $this->getFilename($credential, $persistentToken);

        if (file_exists($fn)) {
            unlink($fn);
        }
    }

    /**
     * Replace current token after successful authentication
     * @param $credential
     * @param $token
     * @param $persistentToken
     * @param int $expire
     */
    public function replaceTriplet($credential, $token, $persistentToken, $expire = 0)
    {
        $this->cleanTriplet($credential, $persistentToken);
        $this->storeTriplet($credential, $token, $persistentToken, $expire);
    }

    /**
     * @param $credential
     */
    public function cleanAllTriplets($credential)
    {
        foreach (glob($this->path . DIRECTORY_SEPARATOR . $credential . ".*" . $this->suffix) as $file) {
            unlink($file);
        }
    }

    /**
     * @param $credential
     * @param $persistentToken
     * @return string
     */
    protected function getFilename($credential, $persistentToken)
    {
        return $this->path . DIRECTORY_SEPARATOR . $credential . "." . $persistentToken . $this->suffix;
    }
}
