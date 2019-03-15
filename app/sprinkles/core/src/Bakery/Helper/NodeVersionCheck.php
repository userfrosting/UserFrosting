<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Bakery\Helper;

/**
 * Helper trait to check NodeJS and NPM version dependencies
 * @author Louis Charette
 */
trait NodeVersionCheck
{
    /**
     * Check the minimum version requirement of Node installed
     * @param bool $displayVersion
     */
    protected function checkNodeVersion($displayVersion = true)
    {
        $npmVersion = trim(exec('node -v'));

        if ($displayVersion) {
            $this->io->writeln("Node Version : $npmVersion");
        }

        if (version_compare($npmVersion, \UserFrosting\NODE_MIN_VERSION, '<')) {
            $this->io->error('UserFrosting requires Node version ' . \UserFrosting\NODE_MIN_VERSION . ' or above. Check the documentation for more details.');
            exit(1);
        }
    }

    /**
     * Check the minimum version requirement for Npm
     * @param bool $displayVersion
     */
    protected function checkNpmVersion($displayVersion = true)
    {
        $npmVersion = trim(exec('npm -v'));

        if ($displayVersion) {
            $this->io->writeln("NPM Version : $npmVersion");
        }

        if (version_compare($npmVersion, \UserFrosting\NPM_MIN_VERSION, '<')) {
            $this->io->error('UserFrosting requires npm version ' . \UserFrosting\NPM_MIN_VERSION . ' or above. Check the documentation for more details.');
            exit(1);
        }
    }
}
