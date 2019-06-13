<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Account\Tests\Unit;

use UserFrosting\Sprinkle\Account\Authenticate\PasswordSecurity;
use UserFrosting\Tests\TestCase;

/**
 * Tests the Password Security class.
 */
class PasswordSecurityTest extends TestCase
{
    /**
     * @var array
     */
    protected $hashArray = [
    'FF7B6CD1334AF86C15087D4C7D1EE0858C3:2',
    'FC3D5E5EE6E427971A2A17728BE3CF7BE90:1',
    'FC446EB88938834178CB9322C1EE273C2A7:2',
    'FD9BE57E9AA7DF04366694A3CA480256EC2:8',
    'FDF342FCD8C3611DAE4D76E8A992A3E4169:8',
    'FE074D46ADA9E63C912AE3AE2BE6AA81D71:2',
    'FE36C085E615BB5E9F30E5EA9322C498AD4:3',
    'FE391596FAB81655455E437319127E7EFB9:2',
    'FEB3321CE615A2B986544888D42C17C417F:1',
    'FF0C145449A6D428BDFC46961A5ED09ADB0:3',
    'FBD6D76BB5D2041542D7D2E3FAC5BB05593:22572',
  ];

    /**
     * @var string Potential password hash prefix.
     */
    protected $hashPrefix = 'E6B6A';

    /**
     * @var string The potential password to use for testing.
     */
    protected $password = 'password1234';

    /**
     * @return PasswordSecurity
     */
    public function testConstructor()
    {
        $passwordSecurity = $this->getPasswordSecurity();
        $this->assertInstanceOf(PasswordSecurity::class, $passwordSecurity);

        return $passwordSecurity;
    }

    /**
     * testCheckPasswordInCache
     */
    public function testCheckPasswordInCache()
    {
        $cache = $this->ci->cache;

        $cache->forget($this->hashPrefix);
        $cache->add($this->hashPrefix, $this->hashArray, 10);

        $passwordSecurity = $this->getPasswordSecurity();
        $breaches = $passwordSecurity->checkPassword('password1234');

        $this->assertEquals('22572', $breaches);
    }

    /**
     * @return PasswordSecurity
     */
    protected function getPasswordSecurity()
    {
        return new PasswordSecurity($this->ci->cache, $this->ci->config);
    }
}
