<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Tests\Unit;

use PHPUnit\Framework\TestCase;
use UserFrosting\Sprinkle\Core\Exceptions\VersionCompareException;
use UserFrosting\Sprinkle\Core\Util\VersionValidator;

/**
 * Unit tests for VersionCheck trait.
 */
class VersionValidatorTest extends TestCase
{
    /**
     * N.B.: This test only attest the method doesn't throw errors and
     * returns the correct type. The actual value can't be tested.
     */
    public function testGetter(): void
    {
        $this->assertIsString(VersionValidator::getPhpVersion());
        $this->assertIsString(VersionValidator::getNodeVersion());
        $this->assertIsString(VersionValidator::getNpmVersion());
        $this->assertIsString(VersionValidator::getPhpConstraint());
        $this->assertIsString(VersionValidator::getPhpRecommended());
        $this->assertIsString(VersionValidator::getNodeConstraint());
        $this->assertIsString(VersionValidator::getNpmConstraint());
    }

    public function testValidatePHP(): void
    {
        $this->assertTrue(VersionCheckSuccessStub::validatePhpVersion());
    }

    public function testFailledPHPValidation(): void
    {
        $this->expectException(VersionCompareException::class);
        VersionCheckFaillureStub::validatePhpVersion();
    }

    public function testValidatePHPDeprecation(): void
    {
        $this->assertTrue(VersionCheckSuccessStub::validatePhpDeprecation());
    }

    public function testFailledPHPDeprecationValidation(): void
    {
        $this->expectException(VersionCompareException::class);
        VersionCheckFaillureStub::validatePhpDeprecation();
    }

    public function testValidateNode(): void
    {
        $this->assertTrue(VersionCheckSuccessStub::validateNodeVersion());
    }

    public function testFailledNodeValidation(): void
    {
        $this->expectException(VersionCompareException::class);
        VersionCheckFaillureStub::validateNodeVersion();
    }

    public function testValidateNpm(): void
    {
        $this->assertTrue(VersionCheckSuccessStub::validateNpmVersion());
    }

    public function testFailledNpmValidation(): void
    {
        $this->expectException(VersionCompareException::class);
        VersionCheckFaillureStub::validateNpmVersion();
    }
}

/**
 * Return hardcoded versions for testing.
 */
class VersionCheckSuccessStub extends VersionValidator
{
    public static function getPhpVersion(): string
    {
        return '7.4.13';
    }

    public static function getNodeVersion(): string
    {
        return 'v12.18.3';
    }

    public static function getNpmVersion(): string
    {
        return '6.14.10';
    }

    public static function getPhpConstraint(): string
    {
        return '^7.2';
    }

    public static function getPhpRecommended(): string
    {
        return '^7.4';
    }

    public static function getNodeConstraint(): string
    {
        return '^12.17.0 || >=14.0.0';
    }

    public static function getNpmConstraint(): string
    {
        return '>=6.14.4';
    }
}

/**
 * Return hardcoded versions for testing faillure.
 */
class VersionCheckFaillureStub extends VersionValidator
{
    public static function getPhpVersion(): string
    {
        return '7.1.3';
    }

    public static function getNodeVersion(): string
    {
        return 'v11.13.1';
    }

    public static function getNpmVersion(): string
    {
        return '5.12.14';
    }

    public static function getPhpConstraint(): string
    {
        return '^7.2';
    }

    public static function getPhpRecommended(): string
    {
        return '^7.4';
    }

    public static function getNodeConstraint(): string
    {
        return '^12.17.0 || >=14.0.0';
    }

    public static function getNpmConstraint(): string
    {
        return '>=6.14.4';
    }
}
