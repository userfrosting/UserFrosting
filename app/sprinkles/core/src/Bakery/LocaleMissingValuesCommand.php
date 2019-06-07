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
use UserFrosting\System\Bakery\BaseCommand;

/**
 * locale:missing-values command.
 * Find missing values in locale translation files.
 *
 * @author Amos Folz
 */
class LocaleMissingValuesCommand extends BaseCommand
{
    protected static $testBase;

    /**
     * @var string
     */
    protected $auxLocale;

    /**
     * @var string
     */
    protected static $path;

    /**
     * @var array
     */
    protected static $table = [];

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('locale:missing-values')
        ->addOption('base', 'b', InputOption::VALUE_REQUIRED, 'The base locale to compare against.', 'en_US')
        ->addOption('compare', 'c', InputOption::VALUE_REQUIRED, 'A optional second locale to compare against', null);

        $this->setDescription('Generate a table of missing locale keys.');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->table = new Table($output);

        // The "base" locale to compare other locales against. Defaults to en_US if not set.
        $baseLocale = $input->getOption('base');

        // Option -c. Set to only compare two locales.
        $this->auxLocale = $input->getOption('compare');

        $baseLocaleFileNames = $this->getBaseFileNames($baseLocale);

        $localesAvailable = $this->getLocales();

        foreach ($localesAvailable as $key => $altLocale) {
            $difference[] = $this->compareFiles($baseLocale, $altLocale, $baseLocaleFileNames);
        }

        $this->table->setHeaders([new TableCell('COMPARING AGAINST: ' . $baseLocale, ['colspan' => 3])]);
        $this->table->addRows([['FILE PATH', 'KEY MISSING VALUE', "$baseLocale VALUE"], new TableSeparator()]);

//        print_r($difference);

        // Build the table.
        $this->buildTable($difference);

        //    return $this->table->render();
    }

    /**
     * Populate table with file paths and missing keys.
     *
     * @param array $array File paths and missing keys.
     * @param int   $level Nested array depth.
     */
    protected function buildTable(array $array, $level = 1)
    {
        foreach ($array as $key => $value) {
            //Level 2 has the filepath.
            if ($level == 2) {
                // Make path easier to read by removing anything before 'app'
                $this->path = strstr($key, 'app');
            }
            if (is_array($value)) {
                //We need to loop through it.
                $this->buildTable($value, ($level + 1));
            } else {
                //It is not an array and not '0', so add the row.
                $this->table->addRow([$this->path, $key, $value]);
            }
        }
    }

    /**
     * Find the missing values between two arrays.
     *
     * @param array $array1
     * @param array $array2
     *
     * @return array [description]
     */
    protected function getDifference($array1, $array2)
    {
        foreach ($array1 as $key => $value) {
            if (is_array($value)) {
                if (!isset($array2[$key])) {
                    $difference[$key] = $value;
                } elseif (!is_array($array2[$key])) {
                    $difference[$key] = $value;
                } else {
                    $new_diff = $this->getDifference($value, $array2[$key]);
                    if ($new_diff != false) {
                        $difference[$key] = $new_diff;
                    }
                }
            } elseif (!isset($array2[$key])) {
                $difference[$key] = $value;
            }
        }

        return !isset($difference) ? 0 : $difference;
    }

    private function getEmptyValues($alt)
    {
        foreach ($alt as $key => $value) {
            if (is_array($value)) {
                //We need to loop through it.
                //        print_r($value);
                echo "The key is $key and the value is $value \r\n\r\n";
                $this->getEmptyValues($value);
            } else {
                $value = trim($value);
            }
            if (isset($value) && $value == '') {
                //    $baseTranslation = $this->getBaseTranslation($this->testBase, $key);
                echo "#2 The key is $key and the value is $value \r\n\r\n";
                $test[$key] = $key;
            }
        }
        //    print_r($test);

        return $test;
    }

    private function getBaseTranslation(array $haystack, $needle)
    {
        $iterator = new \RecursiveArrayIterator($haystack);
        $recursive = new \RecursiveIteratorIterator(
            $iterator,
            \RecursiveIteratorIterator::SELF_FIRST
        );
        foreach ($recursive as $key => $value) {
            if ($key === $needle) {
                return $value;
            }
        }
    }

    /**
     * Iterate over sprinkle locale files and find the difference for two locales.
     *
     * @param string $baseLocale Locale being compared against.
     * @param string $altLocale  Locale to find missing keys for.
     * @param array  $filenames  Sprinkle locale files that will be compared.
     *
     * @return array The keys in $baseLocale that do not exist in $altLocale.
     */
    public function compareFiles($baseLocale, $altLocale, $filenames)
    {
        foreach ($filenames as $sprinklePath => $files) {
            foreach ($files as $key => $file) {
                $this->testBase = $this->getFile("$sprinklePath/locale/{$baseLocale}/{$file}");
                $alt = $this->getFile("$sprinklePath/locale/{$altLocale}/{$file}");

                $missing[$sprinklePath . '/locale' . '/' . $altLocale . '/' . $file] = $this->getEmptyValues($alt);
                print_r($missing);
            }
        }

        return $missing;
    }

    /**
     * Access file contents through inclusion.
     *
     * @param string $path The path of file to be included.
     */
    private function getFile($path)
    {
        return include "$path";
    }

    /**
     * Gets all locale files for a specific locale.
     *
     * @param string $locale The locale being compared against.
     *
     * @return array Locale files and locations for the locale being compared against.
     */
    public function getBaseFileNames($locale)
    {
        $file = ($this->ci->locator->listResources("locale://{$locale}", true));
        foreach ($file as $filename => $path) {
            $files[$path->getLocation()->getPath()][] = $path->getBaseName();
        }

        return $files;
    }

    /**
     * @return array Locales to check for missing keys.
     */
    public function getLocales()
    {
        // If set, use the locale from the -c option.
        if ($this->auxLocale) {
            return [$this->auxLocale];
        } else {
            return array_keys($this->ci->config['site']['locales']['available']);
        }
    }
}
