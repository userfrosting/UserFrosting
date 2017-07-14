<?php
/**
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */
namespace UserFrosting\Sprinkle\Core\Util;

/**
 * Util Class
 *
 * Static utility functions.
 *
 * @author Alex Weissman (https://alexanderweissman.com)
 */
class Util
{
    /**
     * Extracts specific fields from one associative array, and places them into another.
     */
    static public function extractFields(&$inputArray, $fieldArray, $remove = true)
    {
        $result = [];

        foreach ($fieldArray as $name) {
            if (array_key_exists($name, $inputArray)) {
                $result[$name] = $inputArray[$name];

                // Optionally remove value from input array
                if ($remove) {
                    unset($inputArray[$name]);
                }
            }
        }

        return $result;
    }

    /**
     * Extracts numeric portion of a string (for example, for normalizing phone numbers).
     */
    static public function extractDigits($str)
    {
        return preg_replace('/[^0-9]/', '', $str);
    }

    /**
     * Formats a phone number as a standard 7- or 10-digit string (xxx) xxx-xxxx
     */
    static public function formatPhoneNumber($phone)
    {
        $num = static::extractDigits($phone);

        $len = strlen($num);

        if($len == 7) {
            $num = preg_replace('/([0-9]{3})([0-9]{4})/', '$1-$2', $num);
        } elseif ($len == 10) {
            $num = preg_replace('/([0-9]{3})([0-9]{3})([0-9]{4})/', '($1) $2-$3', $num);
        }

        return $num;
    }

    /**
     * Generate a random phrase, consisting of a specified number of adjectives, followed by a noun.
     */
    static public function randomPhrase($numAdjectives, $maxLength = 9999999, $maxTries = 10, $separator = '-')
    {
        $adjectives = include('extra://adjectives.php');
        $nouns = include('extra://nouns.php');

        for ($n = 0; $n < $maxTries; $n++) {
            $keys = array_rand($adjectives, $numAdjectives);
            $matches = array_only($adjectives, $keys);

            $result = implode($separator, $matches);
            $result .= $separator . $nouns[array_rand($nouns)];
            $result = str_slug($result, $separator);
            if (strlen($result) < $maxLength) {
                return $result;
            }
        }

        return '';
    }
}
