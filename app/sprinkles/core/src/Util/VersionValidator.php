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
class VersionValidator
{
    /**
     * Check the minimum version of php.
     *
     * @throws VersionCompareException If contraint version is not matched.
     *
     * @return true Version is valid
     */
    public static function validatePhpVersion(): bool
    {
        $phpVersion = static::getPhpVersion();
        $constraint = static::getPhpConstraint();

        if (!Semver::satisfies($phpVersion, $constraint)) {
            $message = 'UserFrosting requires a PHP version that satisfies "' . $constraint . '", but found ' . $phpVersion . ". You'll need to update you PHP version before you can continue.";
            $exception = new VersionCompareException($message);
            $exception->setContraint($constraint)->setVersion($phpVersion);

            throw $exception;
        }

        return true;
    }

    /**
     * Check the minimum version of php.
     * This should be done by composer itself, but we do it again for good mesure.
     *
     * @throws VersionCompareException If contraint version is not matched.
     *
     * @return true Version is valid
     */
    public static function validatePhpDeprecation(): bool
    {
        $phpVersion = static::getPhpVersion();
        $constraint = static::getPhpRecommended();

        if (!Semver::satisfies($phpVersion, $constraint)) {
            $message = 'UserFrosting recommend a PHP version that satisfies "' . $constraint . '". While your PHP version (' . $phpVersion . ') is still supported by UserFrosting, we recommend upgrading as your current version will soon be unsupported. See http://php.net/supported-versions.php for more info.';
            $exception = new VersionCompareException($message);
            $exception->setContraint($constraint)->setVersion($phpVersion);

            throw $exception;
        }

        return true;
    }

    /**
     * Check the minimum version requirement of Node installed.
     *
     * @throws VersionCompareException If contraint version is not matched.
     *
     * @return true Version is valid
     */
    public static function validateNodeVersion(): bool
    {
        $nodeVersion = static::getNodeVersion();
        $constraint = static::getNodeConstraint();

        if (!Semver::satisfies($nodeVersion, $constraint)) {
            $message = 'UserFrosting requires a Node version that satisfies "' . $constraint . '", but found ' . $nodeVersion . '. Check the documentation for more details.';
            $exception = new VersionCompareException($message);
            $exception->setContraint($constraint)->setVersion($nodeVersion);

            throw $exception;
        }

        return true;
    }

    /**
     * Check the minimum version requirement for Npm.
     *
     * @throws VersionCompareException If contraint version is not matched.
     *
     * @return true Version is valid
     */
    public static function validateNpmVersion(): bool
    {
        $npmVersion = static::getNpmVersion();
        $constraint = static::getNpmConstraint();

        if (!Semver::satisfies($npmVersion, $constraint)) {
            $message = 'UserFrosting requires a NPM version that satisfies "' . $constraint . '", but found ' . $npmVersion . '. Check the documentation for more details.';
            $exception = new VersionCompareException($message);
            $exception->setContraint($constraint)->setVersion($npmVersion);

            throw $exception;
        }

        return true;
    }

    /**
     * Returns system php version.
     * Handle non semver compliant version of PHP returned by some OS.
     *
     * @see https://github.com/composer/semver/issues/125
     *
     * @return string
     */
    public static function getPhpVersion(): string
    {
        $version = (string) phpversion();
        $version = preg_replace('#^([^~+-]+).*$#', '$1', $version);

        return $version;
    }

    /**
     * Returns system Node version.
     *
     * @return string
     */
    public static function getNodeVersion(): string
    {
        return trim(exec('node -v'));
    }

    /**
     * Returns system NPM version.
     *
     * @return string
     */
    public static function getNpmVersion(): string
    {
        return trim(exec('npm -v'));
    }

    /**
     * Returns the required PHP semver range.
     *
     * @return string
     */
    public static function getPhpConstraint(): string
    {
        return \UserFrosting\PHP_MIN_VERSION;
    }

    /**
     * Returns the recommended PHP semver range.
     *
     * @return string
     */
    public static function getPhpRecommended(): string
    {
        return \UserFrosting\PHP_RECOMMENDED_VERSION;
    }

    /**
     * Returns the required Node semver range.
     *
     * @return string
     */
    public static function getNodeConstraint(): string
    {
        return \UserFrosting\NODE_MIN_VERSION;
    }

    /**
     * Returns the required NPM semver range.
     *
     * @return string
     */
    public static function getNpmConstraint(): string
    {
        return \UserFrosting\NPM_MIN_VERSION;
    }
}
