<?php

namespace Birke\Rememberme\Storage;

/**
 * This abstract class contains properties with getters and setters for all
 * database storage classes
 *
 * @author Gabriel Birke
 */
abstract class DB implements StorageInterface
{
    /**
     *
     * @var string
     */
    protected $tableName = "";

    /**
     *
     * @var string
     */
    protected $credentialColumn = "";

    /**
     *
     * @var string
     */
    protected $tokenColumn = "";

    /**
     *
     * @var string
     */
    protected $persistentTokenColumn = "";

    /**
     *
     * @var string
     */
    protected $expiresColumn = "";

    /**
     * @param $options
     */
    public function __construct($options)
    {
        foreach ($options as $prop => $value) {
            $setter = "set" . ucfirst($prop);
            if (method_exists($this, $setter)) {
                $this->$setter($value);
            }
        }
    }

    /**
     * @return string
     */
    public function getTableName()
    {
        return $this->tableName;
    }

    /**
     * @param $tableName
     * @return $this
     */
    public function setTableName($tableName)
    {
        $this->tableName = $tableName;
        return $this;
    }

    /**
     * @return string
     */
    public function getCredentialColumn()
    {
        return $this->credentialColumn;
    }

    /**
     * @param $credentialColumn
     * @return $this
     */
    public function setCredentialColumn($credentialColumn)
    {
        $this->credentialColumn = $credentialColumn;
        return $this;
    }

    /**
     * @return string
     */
    public function getTokenColumn()
    {
        return $this->tokenColumn;
    }

    /**
     * @param $tokenColumn
     * @return $this
     */
    public function setTokenColumn($tokenColumn)
    {
        $this->tokenColumn = $tokenColumn;
        return $this;
    }

    /**
     * @return string
     */
    public function getPersistentTokenColumn()
    {
        return $this->persistentTokenColumn;
    }

    /**
     * @param $persistentTokenColumn
     * @return $this
     */
    public function setPersistentTokenColumn($persistentTokenColumn)
    {
        $this->persistentTokenColumn = $persistentTokenColumn;
        return $this;
    }

    /**
     * @return string
     */
    public function getExpiresColumn()
    {
        return $this->expiresColumn;
    }

    /**
     * @param $expiresColumn
     * @return $this
     */
    public function setExpiresColumn($expiresColumn)
    {
        $this->expiresColumn = $expiresColumn;
        return $this;
    }
}
