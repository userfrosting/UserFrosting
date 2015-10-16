<?php

namespace Birke\Rememberme;

/**
 * Wrapper around setcookie function for better testability
 */
class Cookie
{
    /**
     * @var string
     */
    protected $path = "";

    /**
     * @var string
     */
    protected $domain = "";

    /**
     * @var bool
     */
    protected $secure = false;

    /**
     * @var bool
     */
    protected $httpOnly = true;

    /**
     * @param $name
     * @param string $value
     * @param int $expire
     * @return bool
     */
    public function setCookie($name, $value = "", $expire = 0)
    {
        return setcookie($name, $value, $expire, $this->path, $this->domain, $this->secure, $this->httpOnly);
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @param $path
     */
    public function setPath($path)
    {
        $this->path = $path;
    }

    /**
     * @return string
     */
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     * @param $domain
     */
    public function setDomain($domain)
    {
        $this->domain = $domain;
    }

    /**
     * @return bool
     */
    public function getSecure()
    {
        return $this->secure;
    }

    /**
     * @param $secure
     */
    public function setSecure($secure)
    {
        $this->secure = $secure;
    }

    /**
     * @return bool
     */
    public function getHttpOnly()
    {
        return $this->httpOnly;
    }

    /**
     * @param $httponly
     */
    public function setHttpOnly($httponly)
    {
        $this->httpOnly = $httponly;
    }
}
