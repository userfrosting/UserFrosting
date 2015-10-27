<?php
/* 
 */

require_once dirname(__FILE__).'/../bootstrap.php';
require_once "PHPUnit/Extensions/Database/TestCase.php";

/**
 * @author birke
 */
class Rememberme_Storage_PDOTest extends PHPUnit_Extensions_Database_TestCase {

  /**
   *
   * @var PDO
   */
  protected $pdo;

  /**
   *
   * @var Rememberme_Storage_PDO
   */
  protected $storage;

  protected $userid = 'test';
  protected $validToken = "78b1e6d775cec5260001af137a79dbd5";
  protected $validPersistentToken = "0e0530c1430da76495955eb06eb99d95";
  protected $invalidToken = "7ae7c7caa0c7b880cb247bb281d527de";

  // SHA1 hashes of the tokens
  protected $validDBToken = 'e0e6d29addce0fbdd0f845799be7d0395ed087c3';
  protected $validDBPersistentToken = 'd27d330764ef61e99adf5d16f90b95a2a63c209a';
  protected $invalidDBToken = 'ec15fbc40cdff6a2050a1bcbbc1b2196222f13f4';

  protected $expire = "2012-12-21 21:21:00";
  protected $expireTS = 1356121260;

  protected function getConnection()
    {
        $this->pdo = new PDO('mysql:host=localhost;dbname=test', 'webuser', '');
        return $this->createDefaultDBConnection($this->pdo, 'test');
    }
 
    protected function getDataSet()
    {
        return $this->createFlatXMLDataSet(dirname(__FILE__).'/tokens.xml');
    }

  protected function setUp() {
    parent::setUp();
    $this->storage = new Rememberme_Storage_PDO(array(
      'connection' => $this->pdo,
      'tableName' => 'tokens',
      'credentialColumn' => 'credential',
      'tokenColumn' => 'token',
      'persistentTokenColumn' => 'persistent_token',
      'expiresColumn' => 'expires'
    ));
  }

  public function testFindTripletReturnsFoundIfDataMatches() {
    $result = $this->storage->findTriplet($this->userid, $this->validToken, $this->validPersistentToken);
    $this->assertEquals(Rememberme_Storage_StorageInterface::TRIPLET_FOUND, $result);
  }

  public function testFindTripletReturnsNotFoundIfNoDataMatches() {
    $this->pdo->exec("TRUNCATE tokens");
    $result = $this->storage->findTriplet($this->userid, $this->validToken, $this->validPersistentToken);
    $this->assertEquals(Rememberme_Storage_StorageInterface::TRIPLET_NOT_FOUND, $result);
  }

  public function testFindTripletReturnsInvalidTokenIfTokenIsInvalid() {
    $result = $this->storage->findTriplet($this->userid, $this->invalidToken, $this->validPersistentToken);
    $this->assertEquals(Rememberme_Storage_StorageInterface::TRIPLET_INVALID, $result);
  }

  public function testStoreTripletSavesValuesIntoDatabase() {
    $this->pdo->exec("TRUNCATE tokens");
    $this->storage->storeTriplet($this->userid, $this->validToken, $this->validPersistentToken, $this->expireTS);
    $result = $this->pdo->query("SELECT credential,token,persistent_token, expires FROM tokens");
    $row = $result->fetch(PDO::FETCH_NUM);
    $this->assertEquals(array($this->userid, $this->validDBToken, $this->validDBPersistentToken, $this->expire), $row);
    $this->assertFalse($result->fetch());
  }

  public function testCleanTripletRemovesEntryFromDatabase() {
    $this->storage->cleanTriplet($this->userid, $this->validPersistentToken);
    $this->assertEquals(0, $this->pdo->query("SELECT COUNT(*) FROM tokens")->fetchColumn());
  }

  public function testCleanAllTripletsRemovesAllEntriesWithMatchingCredentialsFromDatabase() {
    $this->pdo->exec("INSERT INTO tokens VALUES ('{$this->userid}', 'dummy', 'dummy', NOW())");
    $this->storage->cleanAllTriplets($this->userid);
    $this->assertEquals(0, $this->pdo->query("SELECT COUNT(*) FROM tokens")->fetchColumn());
  }

}
?>
