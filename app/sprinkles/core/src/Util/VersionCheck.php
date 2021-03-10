<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Util;

use Composer\Semver\Semver;
use UserFrosting\Sprinkle\Core\Exceptions\VersionCompareException;

/**
 * Helper trait to check PHP, Node and NPM version dependencies.
 */
trait VersionCheck
{
    /**
     * Check the minimum version of php.
     *
     * @throws VersionCompareException If contraint version is not matched.
     * @return string                  The current PHP Version
     */
    protected function checkPhpVersion(): string
    {
        $phpVersion = (string) phpversion();

        if (!Semver::satisfies($phpVersion, \UserFrosting\PHP_MIN_VERSION)) {
            $exception = new VersionCompareException('UserFrosting requires php version ' . \UserFrosting\PHP_MIN_VERSION . ' or above. You have ' . $phpVersion . ". You'll need to update you PHP version before you can continue.");
            $exception->setContraint(\UserFrosting\PHP_MIN_VERSION)->setVersion($phpVersion);
            throw $exception;
        }

        return $phpVersion;
    }

    /**
     * Check the minimum version of php.
     * This should be done by composer itself, but we do it again for good mesure.
     *
     * @throws VersionCompareException If contraint version is not matched.
     * @return string                  The current PHP Version
     */
    protected function checkPhpDeprecatedVersion(): string
    {
        $phpVersion = (string) phpversion();

        if (!Semver::satisfies($phpVersion, \UserFrosting\PHP_RECOMMENDED_VERSION)) {
            $exception = new VersionCompareException('While your PHP version is still supported by UserFrosting, we recommend version ' . \UserFrosting\PHP_RECOMMENDED_VERSION . ' or above as ' . $phpVersion . ' will soon be unsupported. See http://php.net/supported-versions.php for more info.');
            $exception->setContraint(\UserFrosting\PHP_RECOMMENDED_VERSION)->setVersion($phpVersion);
            throw $exception;
        }

        return $phpVersion;
    }

    /**
     * Check the minimum version requirement of Node installed.
     *
     * @throws VersionCompareException If contraint version is not matched.
     * @return string                  Node version
     */
    protected function checkNodeVersion(): string
    {
        $nodeVersion = trim(exec('node -v'));

        if (!Semver::satisfies($nodeVersion, \UserFrosting\NODE_MIN_VERSION)) {
            $exception = new VersionCompareException('UserFrosting requires a Node version that satisfies "' . \UserFrosting\NODE_MIN_VERSION . '", but found . Check the documentation for more details.');
            $exception->setContraint(\UserFrosting\NODE_MIN_VERSION)->setVersion($nodeVersion);
            throw $exception;
        }

        return $nodeVersion;
    }

    /**
     * Check the minimum version requirement for Npm.
     *
     * @throws VersionCompareException If contraint version is not matched.
     * @return string                  NPM version
     */
    protected function checkNpmVersion(): string
    {
        $npmVersion = trim(exec('npm -v'));

        if (!Semver::satisfies($npmVersion, \UserFrosting\NPM_MIN_VERSION)) {
            $exception = new VersionCompareException('UserFrosting requires a NPM version that satisfies "' . \UserFrosting\NPM_MIN_VERSION . '" or above. Check the documentation for more details.');
            $exception->setContraint(\UserFrosting\NPM_MIN_VERSION)->setVersion($npmVersion);
            throw $exception;
        }

        return $npmVersion;
    }
}
