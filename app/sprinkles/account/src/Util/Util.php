<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Account\Util;

use UserFrosting\Sprinkle\Core\Util\ClassMapper;
use UserFrosting\Sprinkle\Core\Util\Util as CoreUtil;

/**
 * Util Class
 *
 * Static utility functions for the account Sprinkle.
 *
 * @author Alex Weissman (https://alexanderweissman.com)
 */
class Util
{
    /**
     * Generate a random, unique username from a list of adjectives and nouns.
     *
     * @param  ClassMapper $classMapper
     * @param  int         $maxLength
     * @param  int         $maxTries
     * @return string
     */
    public static function randomUniqueUsername(ClassMapper $classMapper, $maxLength, $maxTries = 10)
    {
        for ($n = 1; $n <= 3; $n++) {
            for ($m = 0; $m < 10; $m++) {
                // Generate a random phrase with $n adjectives
                $suggestion = CoreUtil::randomPhrase($n, $maxLength, $maxTries, '.');
                if (!$classMapper->staticMethod('user', 'where', 'user_name', $suggestion)->first()) {
                    return $suggestion;
                }
            }
        }

        return '';
    }
}
