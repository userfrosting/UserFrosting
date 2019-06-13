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
 * locale:missing-keys command.
 * Find missing keys in locale translation files.
 *
 * @author Amos Folz
 */
class LocaleMissingKeysCommand extends BaseCommand
{
    /**
     * @var string
     */
    protected static $locales;

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
        $this->setName('locale:missing-keys')
        ->addOption('base', 'b', InputOption::VALUE_REQUIRED, 'The base locale to compare against.', 'en_US')
        ->addOption('compare', 'c', InputOption::VALUE_REQUIRED, 'One or more specific locales to check. E.g. "fr_FR,es_ES"', null);

        $this->setDescription('Generate a table of missing locale keys.');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->$table = new Table($output);

        // The "base" locale to compare other locales against. Defaults to en_US if not set.
        $baseLocale = $input->getOption('base');

        // Option -c. Set to only compare two locales.
        $this->locales = $input->getOption('compare');

        $baseLocaleFileNames = $this->getFilenames($baseLocale);

        $locales = $this->getLocales();

        foreach ($locales as $key => $altLocale) {
            $difference[] = $this->compareFiles($baseLocale, $altLocale, $baseLocaleFileNames);
        }

        $this->$table->setHeaders([new TableCell('COMPARING AGAINST: ' . $baseLocale, ['colspan' => 2])]);
        $this->$table->addRows([['FILE PATH', 'MISSING KEY'], new TableSeparator()]);

        // Build the table.
        if (!empty($difference)) {
            $this->buildTable($difference);
        }

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
    protected function buildTable(array $array, $level = 1)
    {
        foreach ($array as $key => $value) {
            //Level 2 has the filepath.
            if ($level == 2) {
                // Make path easier to read by removing anything before 'sprinkles'
                $this->path = strstr($key, 'sprinkles');
            }
            if (is_array($value)) {
                //We need to loop through it.
                $this->buildTable($value, ($level + 1));
            } elseif ($value != '0') {
                //It is not an array and not '0', so add the row.
                $this->$table->addRow([$this->path, $key]);
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
    protected function compareFiles($baseLocale, $altLocale, $filenames)
    {
        foreach ($filenames as $sprinklePath => $files) {
            foreach ($files as $key => $file) {
                $base = $this->parseFile("$sprinklePath/locale/{$baseLocale}/{$file}");
                $alt = $this->parseFile("$sprinklePath/locale/{$altLocale}/{$file}");
                $difference[$sprinklePath . '/locale' . '/' . $altLocale . '/' . $file] = $this->arrayFlatten($this->getDifference($base, $alt));
            }
        }

        return $difference;
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
     * Gets all locale files for a specific locale.
     *
     * @param string $locale The locale being compared against.
     *
     * @return array Locale files and locations for the locale being compared against.
     */
    public function getFilenames($locale)
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
    protected function getLocales()
    {
        // If set, use the locale from the -c option.
        if ($this->locales) {
            $locales = explode(',', $this->locales);
        } else {
            return array_keys($this->ci->config['site']['locales']['available']);
        }
    }

    /**
     * Access file contents through inclusion.
     *
     * @param string $path The path of file to be included.
     */
    protected function parseFile($path)
    {
        return include "$path";
    }
}
