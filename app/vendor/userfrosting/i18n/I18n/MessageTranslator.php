<?php

/**
 * MessageTranslator Class
 *
 * Translate message ids to a message in a specified language.
 *
 * @package   userfrosting/i18n
 * @link      https://github.com/userfrosting/i18n
 * @author    Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/licenses/UserFrosting.md (MIT License)
 */
namespace UserFrosting\I18n;

use Illuminate\Config\Repository;
use UserFrosting\Support\Exception\FileNotFoundException;

class MessageTranslator extends Repository {

	/**
     * @var array an array of paths to search for locale files.
     */
    protected $paths = [];

    /**
     * Add a path to search for locale files.
     *
     * @param string $path
     */
    public function addPath($path)
    {
        if (!is_dir($path)) {
            throw new FileNotFoundException("The locale path '$path' could not be found or is not readable.");
        }

        $this->paths[] = $path;
    }

    /**
     * Set an array of paths to search for locale files.
     *
     * @param array $paths
     */
    public function setPaths(array $paths = [])
    {
        $this->paths = $paths;
    }

    /**
     * Return a list of all paths to search for locale files containing the translation table to be used.  A translation table is an associative array of message ids => translated messages.
     *
     * @return array
     */
    public function getPaths()
    {
        return $this->paths;
    }

    /**
     * Recursively merge locale values (scalar or array) into this repository.
     *
     * If no key is specified, the items will be merged in starting from the top level of the array.
     * If a key IS specified, items will be merged into that key.
     * Nested keys may be specified using dot syntax.
     * @param string|null $key
     * @param mixed $items
     */
    public function mergeItems($key = null, $items)
    {
        $target_values = array_get($this->items, $key);
        if (is_array($target_values)) {
            $modified_values = array_replace_recursive($target_values, $items);
        } else {
            $modified_values = $items;
        }

        array_set($this->items, $key, $modified_values);
    }

    /**
     * Recursively merge a locale file into this repository.
     *
     * @param string $file_with_path
     */
    public function mergeLocaleFile($file_with_path)
    {
        if (!(file_exists($file_with_path) && is_readable($file_with_path))) {
            throw new FileNotFoundException("The locale file '$file_with_path' could not be found or is not readable.");
        } else {
            // Use null key to merge the entire locale array
            $this->mergeItems(null, require $file_with_path);
        }
    }

    /**
     * Load the locale items from all of the files.
     *
     * @param string|null $base_locale
     * @param string|null $user_locale
     */
    public function loadLocaleFiles($base_locale = 'en_US', $user_locale)
    {
        // Search each locale path for default and environment-specific locale files
        foreach ($this->paths as $path) {
            // Merge in default locale file
            $default_files = $this->getLocaleFiles($path, $base_locale);
            foreach ($default_files as $file_with_path) {
            	$this->mergeLocaleFile($file_with_path);
			}

            // Then, merge in environment-specific locale file, if it exists
            if ($user_locale != "") {
	            $user_files = $this->getLocaleFiles($path, $user_locale);
	            foreach ($user_files as $file_with_path) {
	            	$this->mergeLocaleFile($file_with_path);
	            }
	        }
        }
    }

    /**
     * Get an array of full paths found in a locale directory
     *
     * @param string $path
     * @param string $locale
     *
     * @return array List of file found at this path
     */
    protected function getLocaleFiles($path, $locale)
    {
        // Find all the php files in the locale directory
		$files_with_path = glob(rtrim($path, '/\\') . '/' . $locale . "/*.php");

        return $files_with_path;
    }

    /**
     * Translate the given message id into the currently configured language, substituting any placeholders that appear in the translated string.
     *
     * Return the $message_id if not match is found
     * @param string $message_id The id of the message id to translate. can use dot notation for array
     * @param array $placeholders[optional] An optional hash of placeholder names => placeholder values to substitute.
     * @param string $int_key[optional] The key that associate to the plural in $placeholders
     * @return string The translated message.
     */
    public function translate($message_id, $placeholders = [], $int_key = 'int')
    {
		// Return if language string does not exist
		if (!$this->has($message_id)) {
			return $message_id;
		}

		 // Get the message, translated into the currently set language
        $message = $this->get($message_id);

		// If $message is an array, then it's for a plurial form
		// N.B.: Plurals is based on phpBB and Mozilla work : https://developer.mozilla.org/en-US/docs/Mozilla/Localization/Localization_and_Plurals
		if (is_array($message)) {

			// One special case before we go further:
			// If the $message is an empty array, we return $message_id
			if (count($message) == 0) {
				return $message_id;
			}

			// Ok great. Now get the right plural form.
			// The `int` placeholder dictate which plural we are using. No Int = same as finding no key
			// We also allow for a shortcut using the second argument as a numeric value for simple strings.
			$plural_key = (isset($placeholders[$int_key]) ? (int) $placeholders[$int_key] : (!is_array($placeholders) && is_numeric($placeholders) ? $placeholders : null));
			$key_found = false;

			if ($plural_key !== null) {

				// 0 is handled differently. We use it so that "0 users" may be displayed as "No users".
				if ($plural_key == 0 && isset($message[0])) {

					$key_found = 0;

				} else {

					$use_plural_form = $this->get_plural_form($plural_key);
					if (isset($message[$use_plural_form]))
					{
						// The key we need exists, so we use it.
						$key_found = $use_plural_form;
					}
					else
					{
						// If the key we need doesn't exist, we use the previous one.
						$numbers = array_keys($message);
						foreach ($numbers as $num)
						{
							if ($num > $use_plural_form)
							{
								break;
							}
							$key_found = $num;
						}
					}
				}
			}

			// If no key was found, use the last entry (because it is mostly the plural form)
			if ($key_found === false)
			{
				$numbers = array_keys($message);
				$key_found = end($numbers);
			}

			$message = $message[$key_found];
		}

		// Make sure $placeholders is an array otherwise foreach will fail
		// We also allow for the plural system shortcut. This shortcut make $placeholders a numeric value
		// That must be passed back as an array for replacement in the main $message
		if (!is_array($placeholders) && !is_numeric($placeholders)) {
			return $message;
		} else if (is_numeric($placeholders)) {
			$placeholders = array($int_key => $placeholders);
		}

		// Interpolate placeholders
        foreach ($placeholders as $name => $value){
            if (gettype($value) != "array" && gettype($value) != "object") {
                $find = '{{' . trim($name) . '}}';
                $message = str_replace($find, $value, $message);
            }
        }

        return $message;
    }

    /**
	* Determine which plural form we should use.
	* For some languages this is not as simple as for English.
	*
	* @param $number        int|float   The number we want to get the plural case for. Float numbers are floored.
	* @param $force_rule    mixed   False to use the plural rule of the language package
	*                               or an integer to force a certain plural rule
	* @return   int     The plural-case we need to use for the number plural-rule combination
	*/
	public function get_plural_form($number, $force_rule = false)
	{
		$number = (int) $number;

		// Default to English rule (1) or the forced one
		$rule = ($force_rule !== false) ? $force_rule : (($this->has('PLURAL_RULE')) ? $this->get('PLURAL_RULE') : 1);

		if ($rule > 15 || $rule < 0)
		{
			throw new OutOfRangeException("The rule number '$rule' must be between 0 and 16.");
		}

		/**
		* The following plural rules are based on a list published by the Mozilla Developer Network & code from phpBB Group
		* https://developer.mozilla.org/en-US/docs/Mozilla/Localization/Localization_and_Plurals
		*/
		switch ($rule)
		{
			case 0:
				/**
				* Families: Asian (Chinese, Japanese, Korean, Vietnamese), Persian, Turkic/Altaic (Turkish), Thai, Lao
				* 1 - everything: 0, 1, 2, ...
				*/
				return 1;
			case 1:
				/**
				* Families: Germanic (Danish, Dutch, English, Faroese, Frisian, German, Norwegian, Swedish), Finno-Ugric (Estonian, Finnish, Hungarian), Language isolate (Basque), Latin/Greek (Greek), Semitic (Hebrew), Romanic (Italian, Portuguese, Spanish, Catalan)
				* 1 - 1
				* 2 - everything else: 0, 2, 3, ...
				*/
				return ($number == 1) ? 1 : 2;
			case 2:
				/**
				* Families: Romanic (French, Brazilian Portuguese)
				* 1 - 0, 1
				* 2 - everything else: 2, 3, ...
				*/
				return (($number == 0) || ($number == 1)) ? 1 : 2;
			case 3:
				/**
				* Families: Baltic (Latvian)
				* 1 - 0
				* 2 - ends in 1, not 11: 1, 21, ... 101, 121, ...
				* 3 - everything else: 2, 3, ... 10, 11, 12, ... 20, 22, ...
				*/
				return ($number == 0) ? 1 : ((($number % 10 == 1) && ($number % 100 != 11)) ? 2 : 3);
			case 4:
				/**
				* Families: Celtic (Scottish Gaelic)
				* 1 - is 1 or 11: 1, 11
				* 2 - is 2 or 12: 2, 12
				* 3 - others between 3 and 19: 3, 4, ... 10, 13, ... 18, 19
				* 4 - everything else: 0, 20, 21, ...
				*/
				return ($number == 1 || $number == 11) ? 1 : (($number == 2 || $number == 12) ? 2 : (($number >= 3 && $number <= 19) ? 3 : 4));
			case 5:
				/**
				* Families: Romanic (Romanian)
				* 1 - 1
				* 2 - is 0 or ends in 01-19: 0, 2, 3, ... 19, 101, 102, ... 119, 201, ...
				* 3 - everything else: 20, 21, ...
				*/
				return ($number == 1) ? 1 : ((($number == 0) || (($number % 100 > 0) && ($number % 100 < 20))) ? 2 : 3);
			case 6:
				/**
				* Families: Baltic (Lithuanian)
				* 1 - ends in 1, not 11: 1, 21, 31, ... 101, 121, ...
				* 2 - ends in 0 or ends in 10-20: 0, 10, 11, 12, ... 19, 20, 30, 40, ...
				* 3 - everything else: 2, 3, ... 8, 9, 22, 23, ... 29, 32, 33, ...
				*/
				return (($number % 10 == 1) && ($number % 100 != 11)) ? 1 : ((($number % 10 < 2) || (($number % 100 >= 10) && ($number % 100 < 20))) ? 2 : 3);
			case 7:
				/**
				* Families: Slavic (Croatian, Serbian, Russian, Ukrainian)
				* 1 - ends in 1, not 11: 1, 21, 31, ... 101, 121, ...
				* 2 - ends in 2-4, not 12-14: 2, 3, 4, 22, 23, 24, 32, ...
				* 3 - everything else: 0, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 25, 26, ...
				*/
				return (($number % 10 == 1) && ($number % 100 != 11)) ? 1 : ((($number % 10 >= 2) && ($number % 10 <= 4) && (($number % 100 < 10) || ($number % 100 >= 20))) ? 2 : 3);
			case 8:
				/**
				* Families: Slavic (Slovak, Czech)
				* 1 - 1
				* 2 - 2, 3, 4
				* 3 - everything else: 0, 5, 6, 7, ...
				*/
				return ($number == 1) ? 1 : ((($number >= 2) && ($number <= 4)) ? 2 : 3);
			case 9:
				/**
				* Families: Slavic (Polish)
				* 1 - 1
				* 2 - ends in 2-4, not 12-14: 2, 3, 4, 22, 23, 24, 32, ... 104, 122, ...
				* 3 - everything else: 0, 5, 6, ... 11, 12, 13, 14, 15, ... 20, 21, 25, ...
				*/
				return ($number == 1) ? 1 : ((($number % 10 >= 2) && ($number % 10 <= 4) && (($number % 100 < 12) || ($number % 100 > 14))) ? 2 : 3);
			case 10:
				/**
				* Families: Slavic (Slovenian, Sorbian)
				* 1 - ends in 01: 1, 101, 201, ...
				* 2 - ends in 02: 2, 102, 202, ...
				* 3 - ends in 03-04: 3, 4, 103, 104, 203, 204, ...
				* 4 - everything else: 0, 5, 6, 7, 8, 9, 10, 11, ...
				*/
				return ($number % 100 == 1) ? 1 : (($number % 100 == 2) ? 2 : ((($number % 100 == 3) || ($number % 100 == 4)) ? 3 : 4));
			case 11:
				/**
				* Families: Celtic (Irish Gaeilge)
				* 1 - 1
				* 2 - 2
				* 3 - is 3-6: 3, 4, 5, 6
				* 4 - is 7-10: 7, 8, 9, 10
				* 5 - everything else: 0, 11, 12, ...
				*/
				return ($number == 1) ? 1 : (($number == 2) ? 2 : (($number >= 3 && $number <= 6) ? 3 : (($number >= 7 && $number <= 10) ? 4 : 5)));
			case 12:
				/**
				* Families: Semitic (Arabic)
				* 1 - 1
				* 2 - 2
				* 3 - ends in 03-10: 3, 4, ... 10, 103, 104, ... 110, 203, 204, ...
				* 4 - ends in 11-99: 11, ... 99, 111, 112, ...
				* 5 - everything else: 100, 101, 102, 200, 201, 202, ...
				* 6 - 0
				*/
				return ($number == 1) ? 1 : (($number == 2) ? 2 : ((($number % 100 >= 3) && ($number % 100 <= 10)) ? 3 : ((($number % 100 >= 11) && ($number % 100 <= 99)) ? 4 : (($number != 0) ? 5 : 6))));
			case 13:
				/**
				* Families: Semitic (Maltese)
				* 1 - 1
				* 2 - is 0 or ends in 01-10: 0, 2, 3, ... 9, 10, 101, 102, ...
				* 3 - ends in 11-19: 11, 12, ... 18, 19, 111, 112, ...
				* 4 - everything else: 20, 21, ...
				*/
				return ($number == 1) ? 1 : ((($number == 0) || (($number % 100 > 1) && ($number % 100 < 11))) ? 2 : ((($number % 100 > 10) && ($number % 100 < 20)) ? 3 : 4));
			case 14:
				/**
				* Families: Slavic (Macedonian)
				* 1 - ends in 1: 1, 11, 21, ...
				* 2 - ends in 2: 2, 12, 22, ...
				* 3 - everything else: 0, 3, 4, ... 10, 13, 14, ... 20, 23, ...
				*/
				return ($number % 10 == 1) ? 1 : (($number % 10 == 2) ? 2 : 3);
			case 15:
				/**
				* Families: Icelandic
				* 1 - ends in 1, not 11: 1, 21, 31, ... 101, 121, 131, ...
				* 2 - everything else: 0, 2, 3, ... 10, 11, 12, ... 20, 22, ...
				*/
				return (($number % 10 == 1) && ($number % 100 != 11)) ? 1 : 2;
		}
	}
}
