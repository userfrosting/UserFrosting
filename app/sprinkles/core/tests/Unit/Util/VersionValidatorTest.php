<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Tests\Unit;

use phpmock\phpunit\PHPMock;
use PHPUnit\Framework\TestCase;
use UserFrosting\Sprinkle\Core\Exceptions\VersionCompareException;
use UserFrosting\Sprinkle\Core\Util\VersionValidator;

/**
 * Unit tests for VersionCheck trait.
 */
class VersionValidatorTest extends TestCase
{
    use PHPMock;

    /**
     * Assert PHP related methods.
     *
     * @dataProvider phpVersionProvider
     * @runInSeparateProcess
     *
     * @param string $version
     * @param string $sanitized
     * @param bool   $valid
     * @param bool   $deprecated
     */
    public function testPhp(string $version, string $sanitized, bool $valid, bool $deprecated): void
    {
        // Mock `phpversion` function
        $class = new \ReflectionClass(VersionValidator::class);
        $namespace = $class->getNamespaceName();
        $mock = $this->getFunctionMock($namespace, 'phpversion');
        $mock->expects($this->any())->willReturn($version);

        // Assert GetPHPVersion
        $this->assertSame($sanitized, VersionValidator::getPhpVersion());

        // Assert validatePhpVersion
        if ($valid) {
            $this->assertTrue(VersionValidator::validatePhpVersion());
        } else {
            try {
                VersionValidator::validatePhpVersion();
            } catch (VersionCompareException $e) {
                $this->assertSame(VersionValidator::getPhpConstraint(), $e->getConstraint());
                $this->assertSame($sanitized, $e->getVersion());

                return;
            }

            $this->fail();
        }

        // Assert validatePhpDeprecation
        if (!$deprecated) {
            $this->assertTrue(VersionValidator::validatePhpDeprecation());
        } else {
            try {
                VersionValidator::validatePhpDeprecation();
            } catch (VersionCompareException $e) {
                $this->assertSame(VersionValidator::getPhpRecommended(), $e->getConstraint());
                $this->assertSame($sanitized, $e->getVersion());

                return;
            }

            $this->fail();
        }
    }

    /**
     * Assert Node related methods.
     *
     * @dataProvider nodeVersionProvider
     * @runInSeparateProcess
     *
     * @param string $version
     * @param string $sanitized
     * @param bool   $valid
     */
    public function testNode(string $version, string $sanitized, bool $valid): void
    {
        // Mock `exec` function
        $class = new \ReflectionClass(VersionValidator::class);
        $namespace = $class->getNamespaceName();
        $mock = $this->getFunctionMock($namespace, 'exec');
        $mock->expects($this->any())->willReturn($version);

        // Assert getNodeVersion
        $this->assertSame($sanitized, VersionValidator::getNodeVersion());

        // Assert validateNodeVersion
        if ($valid) {
            $this->assertTrue(VersionValidator::validateNodeVersion());
        } else {
            try {
                VersionValidator::validateNodeVersion();
            } catch (VersionCompareException $e) {
                $this->assertSame(VersionValidator::getNodeConstraint(), $e->getConstraint());
                $this->assertSame($sanitized, $e->getVersion());

                return;
            }

            $this->fail();
        }
    }

    /**
     * Assert Npm related methods.
     *
     * @dataProvider npmVersionProvider
     * @runInSeparateProcess
     *
     * @param string $version
     * @param string $sanitized
     * @param bool   $valid
     */
    public function testNpm(string $version, string $sanitized, bool $valid): void
    {
        // Mock `exec` function
        $class = new \ReflectionClass(VersionValidator::class);
        $namespace = $class->getNamespaceName();
        $mock = $this->getFunctionMock($namespace, 'exec');
        $mock->expects($this->any())->willReturn($version);

        // Assert getNpmVersion
        $this->assertSame($sanitized, VersionValidator::getNpmVersion());

        // Assert validateNpmVersion
        if ($valid) {
            $this->assertTrue(VersionValidator::validateNpmVersion());
        } else {
            try {
                VersionValidator::validateNpmVersion();
            } catch (VersionCompareException $e) {
                $this->assertSame(VersionValidator::getNpmConstraint(), $e->getConstraint());
                $this->assertSame($sanitized, $e->getVersion());

                return;
            }

            $this->fail();
        }
    }

    /**
     * PHP version provider.
     *
     * @return array [version, sanitized, valid, deprecated]
     */
    public function phpVersionProvider(): array
    {
        return [
            ['7.2.3', '7.2.3', false, true],
            ['7.3.14', '7.3.14', true, true],
            ['7.3', '7.3', true, true],
            ['7.4', '7.4', true, true],
            ['7.4.13', '7.4.13', true, true],
            ['8.0.3', '8.0.3', true, false],
            ['7.4.34-18+ubuntu20.04.1+deb.sury.org+1', '7.4.34', true, true],
        ];
    }

    /**
     * Node version provider.
     *
     * @return array [version, sanitized, valid]
     */
    public function nodeVersionProvider(): array
    {
        return [
            ['v12.18.1', 'v12.18.1', true],
            ['v13.12.3', 'v13.12.3', false],
            ['v14.0.0 ', 'v14.0.0', true], // Test trim here
        ];
    }

    /**
     * Node version provider.
     *
     * @return array [version, sanitized, valid]
     */
    public function npmVersionProvider(): array
    {
        return [
            [' 6.14.10 ', '6.14.10', true], // Trim
            ['6.14.4', '6.14.4', true],
            ['5.12.14', '5.12.14', false],
        ];
    }
}
