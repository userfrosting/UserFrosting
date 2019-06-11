<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Bakery;

use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableCell;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use UserFrosting\Support\Repository\Repository;

/**
 * locale:fix-keys command.
 * Generate missing keys in locale translation files.
 *
 * @author Amos Folz
 */
class LocaleFixKeysCommand extends LocaleMissingKeysCommand
{
    /**
     * @var string
     */
    protected $locales;

    /**
     * $var string
     */
    protected static $baseLocale;

    /**
     * @var array
     */
    protected static $table = [];

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('locale:fix-keys')
        ->addOption('base', 'b', InputOption::VALUE_REQUIRED, 'The base locale used to generate values for any keys that are fixed. ', 'en_US')
        ->addOption('fix', 'f', InputOption::VALUE_REQUIRED, 'One or more specific locales to fix. E.g. "en_US,es_ES" ', null);

        $this->setDescription('Fix locale missing files and key values');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->$table = new Table($output);

        // The "base" locale to compare other locales against. Defaults to en_US if not set.
        $this->baseLocale = $input->getOption('base');

        // Option -c. Set to compare one or more specific locales.
        $this->locales = $input->getOption('fix');

        $baseLocaleFileNames = $this->getFilenames($this->baseLocale);

        $localesAvailable = $this->getLocales();

        $filesFixed = [];

        foreach ($localesAvailable as $key => $altLocale) {
            $filesFixed[$altLocale] = $this->compareFiles($this->baseLocale, $altLocale, $baseLocaleFileNames);
        }

        $this->$table->setHeaders([new TableCell('MISSING KEY VALUES WILL BE SET USING: ' . $this->baseLocale, ['colspan' => 1])]);
        $this->$table->addRows([['FILES FIXED'], new TableSeparator()]);

        // Build the table.
        $this->buildTable($filesFixed);

        return $this->$table->render();
    }

    /**
     * Flattens a nested array into dot syntax.
     *
     * @param array $array The array to flatten.
     *
     * @return array Keys with missing values.
     */
    protected function arrayFlatten($array, $prefix = '')
    {
        $result = [];
        foreach ($array as $key=>$value) {
            if (is_array($value)) {
                $result = $result + $this->arrayFlatten($value, $prefix . $key . '.');
            } else {
                $result[$prefix . $key] = $value;
            }
        }

        return $result;
    }

    /**
     * Populate table with file paths and missing keys.
     *
     * @param array $array File paths and missing keys.
     * @param int   $level Nested array depth.
     */
    protected function buildTable(array $array)
    {
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                //We need to loop through it.
                $this->buildTable($value);
            } elseif ($value != '0') {
                //It is not an array and not '0', so add the row.
                $this->$table->addRow([$value]);
            }
        }
    }

    /**
     * Find the missing keys between two arrays.
     *
     * @param array $array1
     * @param array $array2
     *
     * @return array
     */
    protected function getDifference($array1, $array2)
    {
        foreach ($array1 as $key => $value) {
            if (is_array($value)) {
                if (!isset($array2[$key])) {
                    $difference[$key] = $key;
                } elseif (!is_array($array2[$key])) {
                    $difference[$key] = $key;
                } else {
                    $new_diff = $this->getDifference($value, $array2[$key]);
                    if ($new_diff != false) {
                        $difference[$key] = $new_diff;
                    }
                }
            } elseif (!isset($array2[$key])) {
                $difference[$key] = $key;
            }
        }

        return !isset($difference) ? 0 : $difference;
    }

    /**
     * Iterate over sprinkle locale files and find the difference for two locales.
     *
     * @param string $baseLocale Locale being compared against.
     * @param string $altLocale  Locale to find missing keys for.
     * @param array  $filenames  Sprinkle locale files that will be compared.
     *
     * @return array Filepaths that were fixed.
     */
    public function compareFiles($baseLocale, $altLocale, $filenames)
    {
        foreach ($filenames as $sprinklePath => $files) {
            foreach ($files as $key => $file) {
                $base = $this->parseFile("$sprinklePath/locale/{$baseLocale}/{$file}");
                $alt = $this->parseFile("$sprinklePath/locale/{$altLocale}/{$file}");
                $filePath = "$sprinklePath/locale/{$altLocale}/{$file}";
                $missing = $this->arrayFlatten($this->getDifference($base, $alt));

                // The files with missing keys.
                if (!empty($missing)) {
                    $filesFixed[] = $this->write($base, $alt, $filePath);
                }
            }
        }

        return $filesFixed;
    }

    /**
     * Fixes locale files by adding missing keys.
     *
     * @param array  $base
     * @param array  $alt
     * @param string $filePath The path of fixed file.
     *
     * @return string
     */
    public function write($base, $alt, $filePath)
    {
        //If the directory does not exist we need to create it recursively.
        if (!file_exists(dirname($filePath))) {
            mkdir(dirname($filePath), 0777, true);
        }

        // Build the respository and then merge in each locale file.
        // Any keys not in the $alt locale will be added with the $base locales value.
        $repository = new Repository();
        $repository->mergeItems(null, $base, $alt);

        // We will fix the file by completely rebuilding it.
        passthru("echo \<?php > $filePath");
        file_put_contents($filePath, var_export($repository->all(), true), FILE_APPEND);
        passthru("echo \; >> $filePath");

        // Check the file with php-cs-fixer
        passthru("php ./app/vendor/friendsofphp/php-cs-fixer/php-cs-fixer fix $filePath --quiet --using-cache no --config ./.php_cs");

        // FInally, we need to insert 'return' to the file.
        file_put_contents($filePath, preg_replace('/\[/', 'return [', file_get_contents($filePath), 1));

        return "$filePath";
    }

    /**
     * @return array Locales to check for missing keys.
     */
    public function getLocales()
    {
        $configuredLocales = array_keys($this->ci->config['site']['locales']['available']);

        // If set, use the locale from the -c option.
        if ($this->locales) {
            $locales = explode(',', $this->locales);
            foreach ($locales as $key => $value) {
                if (!in_array($value, $configuredLocales)) {
                    $this->io->warning("The $value locale was not found in your current configuration. Proceeding may results in a large number of files being created. Are you sure you want to continue?");
                    if (!$this->io->confirm('Continue?', false)) {
                        exit;
                    }
                }
            }
        } else {
            return $configuredLocales;
        }
    }
}
