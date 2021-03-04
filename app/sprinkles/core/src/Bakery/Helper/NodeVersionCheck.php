<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Bakery\Helper;

use Composer\Semver\Semver;

/**
 * Helper trait to check NodeJS and NPM version dependencies.
 *
 * @author Louis Charette
 */
trait NodeVersionCheck
{
    /**
     * Check the minimum version requirement of Node installed.
     *
     * @return string Node version
     */
    protected function checkNodeVersion()
    {
        $nodeVersion = trim(exec('node -v'));

        if (Semver::satisfies($nodeVersion, \UserFrosting\NODE_MIN_VERSION)) {
            $this->io->error('UserFrosting requires a Node version that satisfies ' . \UserFrosting\NODE_MIN_VERSION . '. Check the documentation for more details.');
            exit(1);
        }

        return $npmVersion;
    }

    /**
     * Check the minimum version requirement for Npm.
     *
     * @return string NPM version
     */
    protected function checkNpmVersion()
    {
        $npmVersion = trim(exec('npm -v'));

        if (Semver::satisfies($nodeVersion, \UserFrosting\NPM_MIN_VERSION)) {
            $this->io->error('UserFrosting requires a NPM version that satisfies ' . \UserFrosting\NPM_MIN_VERSION . ' or above. Check the documentation for more details.');
            exit(1);
        }

        return $npmVersion;
    }
}
