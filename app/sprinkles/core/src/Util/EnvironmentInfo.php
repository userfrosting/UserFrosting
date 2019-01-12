<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Util;

use Illuminate\Database\Capsule\Manager as Capsule;

/**
 * EnvironmentInfo Class
 *
 * Gets basic information about the application environment.
 *
 * @author Alex Weissman (https://alexanderweissman.com)
 */
class EnvironmentInfo
{
    /**
     * @var \Interop\Container\ContainerInterface The DI container for your application.
     */
    public static $ci;

    /**
     * Get an array of key-value pairs containing basic information about the default database.
     *
     * @return string[] the properties of this database.
     */
    public static function database()
    {
        static::$ci['db'];

        $pdo = Capsule::connection()->getPdo();
        $results = [];

        try {
            $results['type'] = $pdo->getAttribute(\PDO::ATTR_DRIVER_NAME);
        } catch (\Exception $e) {
            $results['type'] = 'Unknown';
        }

        try {
            $results['version'] = $pdo->getAttribute(\PDO::ATTR_SERVER_VERSION);
        } catch (\Exception $e) {
            $results['version'] = '';
        }

        return $results;
    }

    /**
     * Test whether a DB connection can be established.
     *
     * @return bool true if the connection can be established, false otherwise.
     */
    public static function canConnectToDatabase()
    {
        try {
            Capsule::connection()->getPdo();
        } catch (\PDOException $e) {
            return false;
        }

        return true;
    }
}
