<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */
namespace UserFrosting\Tests\Unit;

use UserFrosting\Sprinkle\Account\Authenticate\Hasher;
use UserFrosting\Tests\TestCase;

/**
 * Tests the password Hasher class.
 *
 * @extends TestCase
 */
class HasherTest extends TestCase
{
    protected $plainText = 'hodleth';

    /**
     * @var string Legacy hash from UserCake (sha1)
     */
    protected $userCakeHash = '87e995bde9ebdc73fc58cc75a9fadc4ae630d8207650fbe94e148ccc8058d5de5';

    /**
     * @var string Legacy hash from UF 0.1.x
     */
    protected $legacyHash = '$2y$12$rsXGznS5Ky23lX9iNzApAuDccKRhQFkiy5QfKWp0ACyDWBPOylPB.rsXGznS5Ky23lX9iNzApA9';

    /**
     * @var string Modern hash that uses password_hash()
     */
    protected $modernHash = '$2y$10$ucxLwloFso6wJoct1baBQefdrttws/taEYvavi6qoPsw/vd1u4Mha';

    public function testGetHashType()
    {
        $hasher = new Hasher;

        $type = $hasher->getHashType($this->modernHash);

        $this->assertEquals('modern', $type);

        $type = $hasher->getHashType($this->legacyHash);

        $this->assertEquals('legacy', $type);

        $type = $hasher->getHashType($this->userCakeHash);

        $this->assertEquals('sha1', $type);
    }

    public function testVerify()
    {
        $hasher = new Hasher;

        $this->assertTrue($hasher->verify($this->plainText, $this->modernHash));
        $this->assertTrue($hasher->verify($this->plainText, $this->legacyHash));
        $this->assertTrue($hasher->verify($this->plainText, $this->userCakeHash));
    }

    public function testVerifyReject()
    {
        $hasher = new Hasher;

        $this->assertFalse($hasher->verify('selleth', $this->modernHash));
        $this->assertFalse($hasher->verify('selleth', $this->legacyHash));
        $this->assertFalse($hasher->verify('selleth', $this->userCakeHash));
    }
}
