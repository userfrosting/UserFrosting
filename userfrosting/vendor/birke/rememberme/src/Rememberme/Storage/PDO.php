<?php

namespace Birke\Rememberme\Storage;

/**
 * Store login tokens in database with PDO class
 *
 * @author birke
 */
class PDO extends DB
{
    /**
     * @var \PDO
     */
    protected $connection;

    /**
     * @param mixed $credential
     * @param string $token
     * @param string $persistentToken
     * @return int
     */
    public function findTriplet($credential, $token, $persistentToken)
    {
        // We don't store the sha1 as binary values because otherwise we could not use
        // proper XML test data
        $sql = "SELECT IF(SHA1(?) = {$this->tokenColumn}, 1, -1) AS token_match " .
            "FROM {$this->tableName} WHERE {$this->credentialColumn} = ? " .
            "AND {$this->persistentTokenColumn} = SHA1(?) AND {$this->expiresColumn} > NOW() LIMIT 1";

        $query = $this->connection->prepare($sql);
        $query->execute(array($token, $credential, $persistentToken));

        $result = $query->fetchColumn();

        if (!$result) {
            return self::TRIPLET_NOT_FOUND;
        } elseif ($result == 1) {
            return self::TRIPLET_FOUND;
        }

        return self::TRIPLET_INVALID;
    }

    /**
     * @param mixed $credential
     * @param string $token
     * @param string $persistentToken
     * @param int $expire
     */
    public function storeTriplet($credential, $token, $persistentToken, $expire = 0)
    {
        $sql = "INSERT INTO {$this->tableName}({$this->credentialColumn}, " .
            "{$this->tokenColumn}, {$this->persistentTokenColumn}, " .
            "{$this->expiresColumn}) VALUES(?, SHA1(?), SHA1(?), ?)";

        $query = $this->connection->prepare($sql);
        $query->execute(array($credential, $token, $persistentToken, date("Y-m-d H:i:s", $expire)));
    }

    /**
     * @param mixed $credential
     * @param string $persistentToken
     */
    public function cleanTriplet($credential, $persistentToken)
    {
        $sql = "DELETE FROM {$this->tableName} WHERE {$this->credentialColumn} = ? "
             . "AND {$this->persistentTokenColumn} = SHA1(?)";

        $query = $this->connection->prepare($sql);
        $query->execute(array($credential, $persistentToken));
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
        try {
            $this->connection->beginTransaction();
            $this->cleanTriplet($credential, $persistentToken);
            $this->storeTriplet($credential, $token, $persistentToken, $expire);
            $this->connection->commit();
        }
        catch (\PDOException $e) {
            $this->connection->rollBack();
            throw $e;
        }
    }

    /**
     * @param $credential
     */
    public function cleanAllTriplets($credential)
    {
        $sql = "DELETE FROM {$this->tableName} WHERE {$this->credentialColumn} = ? ";

        $query = $this->connection->prepare($sql);
        $query->execute(array($credential));
    }

    /**
     * @return \PDO
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * @param PDO $connection
     */
    public function setConnection(\PDO $connection)
    {
        $this->connection = $connection;
    }
}
