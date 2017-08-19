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
     *
     * @param mixed[] $inputArray
     * @param string[] $fieldArray
     * @param bool $remove
     * @return mixed[]
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
     *
     * @param string $str
     * @return string
     */
    static public function extractDigits($str)
    {
        return preg_replace('/[^0-9]/', '', $str);
    }

    /**
     * Formats a phone number as a standard 7- or 10-digit string (xxx) xxx-xxxx
     *
     * @param string $phone
     * @return string
     */
    static public function formatPhoneNumber($phone)
    {
        $num = static::extractDigits($phone);

        $len = strlen($num);

        if ($len == 7) {
            $num = preg_replace('/([0-9]{3})([0-9]{4})/', '$1-$2', $num);
        } elseif ($len == 10) {
            $num = preg_replace('/([0-9]{3})([0-9]{3})([0-9]{4})/', '($1) $2-$3', $num);
        }

        return $num;
    }

    /**
     * Nicely format an array for printing.
     * See https://stackoverflow.com/a/9776726/2970321
     *
     * @param array $arr
     * @return string
     */
    static public function prettyPrintArray(array $arr)
    {
        $json = json_encode($arr);
        $result = '';
        $level = 0;
        $inQuotes = false;
        $inEscape = false;
        $endsLineLevel = NULL;
        $jsonLength = strlen($json);

        for ($i = 0; $i < $jsonLength; $i++) {
            $char = $json[$i];
            $newLineLevel = NULL;
            $post = '';
            if ($endsLineLevel !== NULL) {
                $newLineLevel = $endsLineLevel;
                $endsLineLevel = NULL;
            }
            if ($inEscape) {
                $inEscape = false;
            } elseif ($char === '"') {
                $inQuotes = !$inQuotes;
            } elseif (!$inQuotes) {
                switch ($char) {
                    case '}': case ']':
                        $level--;
                        $endsLineLevel = NULL;
                        $newLineLevel = $level;
                        break;

                    case '{': case '[':
                        $level++;

                    case ',':
                        $endsLineLevel = $level;
                        break;

                    case ':':
                        $post = ' ';
                        break;

                    case ' ': case '\t': case '\n': case '\r':
                        $char = '';
                        $endsLineLevel = $newLineLevel;
                        $newLineLevel = NULL;
                        break;
                }
            } elseif ($char === '\\') {
                $inEscape = true;
            }

            if ($newLineLevel !== NULL) {
                $result .= '<br>'.str_repeat( '&nbsp;&nbsp;', $newLineLevel);
            }

            $result .= $char.$post;
        }

        return $result;
    }

    /**
     * Generate a random phrase, consisting of a specified number of adjectives, followed by a noun.
     *
     * @param int $numAdjectives
     * @param int $maxLength
     * @param int $maxTries
     * @param string $separator
     * @return string
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
