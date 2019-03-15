<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Account\Tests\Unit;

use Birke\Rememberme\Storage\StorageInterface;
use Carbon\Carbon;
use UserFrosting\Sprinkle\Account\Database\Models\Persistence;
use UserFrosting\Sprinkle\Account\Rememberme\PDOStorage;
use UserFrosting\Sprinkle\Account\Tests\withTestUser;
use UserFrosting\Sprinkle\Core\Tests\TestDatabase;
use UserFrosting\Sprinkle\Core\Tests\RefreshDatabase;
use UserFrosting\Tests\TestCase;

/**
 * @author Louis Charette
 */
class PDOStorageTest extends TestCase
{
    use TestDatabase;
    use RefreshDatabase;
    use withTestUser;

    /**
     * @var PDOStorage
     */
    protected $storage;

    /**
     * @var \UserFrosting\Sprinkle\Account\Database\Models\Interfaces\UserInterface
     */
    protected $testUser;

    protected $validToken = '78b1e6d775cec5260001af137a79dbd5';
    protected $validPersistentToken = '0e0530c1430da76495955eb06eb99d95';
    protected $invalidToken = '7ae7c7caa0c7b880cb247bb281d527de';

    // SHA1 hashes of the tokens
    protected $validDBToken = 'e0e6d29addce0fbdd0f845799be7d0395ed087c3';
    protected $validDBPersistentToken = 'd27d330764ef61e99adf5d16f90b95a2a63c209a';
    protected $invalidDBToken = 'ec15fbc40cdff6a2050a1bcbbc1b2196222f13f4';

    protected $expire = '2022-12-21 21:21:00';

    protected function setUp()
    {
        parent::setUp();

        // Setup test database
        $this->setupTestDatabase();
        $this->refreshDatabase();

        // Create a test user
        $this->testUser = $this->createTestUser();

        $this->storage = new PDOStorage($this->ci->db);
    }

    public function testFindTripletReturnsFoundIfDataMatches()
    {
        $this->insertTestData();
        $result = $this->storage->findTriplet($this->testUser->id, $this->validToken, $this->validPersistentToken);
        $this->assertEquals(StorageInterface::TRIPLET_FOUND, $result);
    }

    public function testFindTripletReturnsNotFoundIfNoDataMatches()
    {
        $result = $this->storage->findTriplet($this->testUser->id, $this->validToken, $this->validPersistentToken);
        $this->assertEquals(StorageInterface::TRIPLET_NOT_FOUND, $result);
    }

    public function testFindTripletReturnsInvalidTokenIfTokenIsInvalid()
    {
        $this->insertTestData();
        $result = $this->storage->findTriplet($this->testUser->id, $this->invalidToken, $this->validPersistentToken);
        $this->assertEquals(StorageInterface::TRIPLET_INVALID, $result);
    }

    public function testStoreTripletSavesValuesIntoDatabase()
    {
        $this->storage->storeTriplet($this->testUser->id, $this->validToken, $this->validPersistentToken, strtotime($this->expire));
        $row = Persistence::select(['user_id', 'token', 'persistent_token', 'expires_at'])->first()->toArray();
        $this->assertEquals([$this->testUser->id, $this->validDBToken, $this->validDBPersistentToken, $this->expire], array_values($row));
    }

    public function testCleanTripletRemovesEntryFromDatabase()
    {
        $this->insertTestData();
        $this->storage->cleanTriplet($this->testUser->id, $this->validPersistentToken);
        $this->assertEquals(0, Persistence::count());
    }

    public function testCleanAllTripletsRemovesAllEntriesWithMatchingCredentialsFromDatabase()
    {
        $this->insertTestData();
        $persistence = new Persistence([
            'user_id'          => $this->testUser->id,
            'token'            => 'dummy',
            'persistent_token' => 'dummy',
            'expires_at'       => null
        ]);
        $persistence->save();
        $this->storage->cleanAllTriplets($this->testUser->id);
        $this->assertEquals(0, Persistence::count());
    }

    public function testReplaceTripletRemovesAndSavesEntryFromDatabase()
    {
        $this->insertTestData();
        $this->storage->replaceTriplet($this->testUser->id, $this->invalidToken, $this->validPersistentToken, strtotime($this->expire));
        $this->assertEquals(1, Persistence::count());
        $row = Persistence::select(['user_id', 'token', 'persistent_token', 'expires_at'])->first()->toArray();
        $this->assertEquals([$this->testUser->id, $this->invalidDBToken, $this->validDBPersistentToken, $this->expire], array_values($row));
    }

    public function testCleanExpiredTokens()
    {
        $this->insertTestData();
        $persistence = new Persistence([
            'user_id'          => $this->testUser->id,
            'token'            => 'dummy',
            'persistent_token' => 'dummy',
            'expires_at'       => Carbon::now()->subHour(1)
        ]);
        $persistence->save();
        $this->assertEquals(2, Persistence::count());
        $this->storage->cleanExpiredTokens(Carbon::now()->timestamp);
        $this->assertEquals(1, Persistence::count());
    }

    /**
     * Insert test dataset
     * @return Persistence
     */
    protected function insertTestData()
    {
        $persistence = new Persistence([
            'user_id'          => $this->testUser->id,
            'token'            => $this->validDBToken,
            'persistent_token' => $this->validDBPersistentToken,
            'expires_at'       => $this->expire
        ]);
        $persistence->save();

        return $persistence;
    }
}
