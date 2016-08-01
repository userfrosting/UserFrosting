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
     * @return string The translated message.
     */
    public function translate($message_id, $placeholders = [])
    {
		// Return if language string does not exist
		if (!$this->has($message_id))
		{
			return $message_id;
		}

		 // Get the message, translated into the currently set language
        $message = $this->get($message_id);

        // Interpolate placeholders
        foreach ($placeholders as $name => $value){
            if (gettype($value) != "array" && gettype($value) != "object") {
                $find = '{{' . trim($name) . '}}';
                $message = str_replace($find, $value, $message);
            }
        }

        return $message;
    }
}
