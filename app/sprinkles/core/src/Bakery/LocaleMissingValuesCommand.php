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
use UserFrosting\I18n\LocalePathBuilder;
use UserFrosting\I18n\MessageTranslator;
use UserFrosting\Support\Repository\Loader\ArrayFileLoader;

/**
 * locale:missing-values command.
 * Find missing values in locale translation files.
 *
 * @author Amos Folz
 */
class LocaleMissingValuesCommand extends LocaleMissingKeysCommand
{
    /**
     * @var string
     */
    protected $localesToCheck;

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
        ->addOption('base', 'b', InputOption::VALUE_REQUIRED, 'The base locale used for comparison.', 'en_US')
        ->addOption('check', 'c', InputOption::VALUE_REQUIRED, 'One or more specific locales to check. E.g. "en_US,es_ES"', null)
        ->addOption('length', 'l', InputOption::VALUE_REQUIRED, 'Set max length for preview column text', 255);

        $this->setDescription('Generate a table of keys with missing values.');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->table = new Table($output);

        // Option -c. The locales to be checked.
        $this->localesToCheck = $input->getOption('check');

        $this->maxLength = $input->getOption('length');

        // The locale for the 'preview' column. Defaults to en_US if not set.
        $baseLocale = $input->getOption('base');

        $this->setTranslation($baseLocale);

        $locales = $this->getLocales();
        print_r($locales);
        $files = $this->getFilePaths($locales);

        $missing[] = $this->searchFilesForNull($files);

        $baseLocaleFileNames = $this->getFilenames($baseLocale);

        foreach ($locales as $key => $altLocale) {
            $missing[] = $this->compareFiles($baseLocale, $altLocale, $baseLocaleFileNames);
        }

        $this->table->setHeaders([
          [new TableCell('LOCALES SEARCHED: |' . implode('|', $locales) . '|', ['colspan' => 3])],
          [new TableCell("USING | $baseLocale | FOR TRANSLATION PREVIEW", ['colspan' => 3])],

        ]);
        $this->table->setColumnWidth(2, 50);

        $this->table->addRows([
          ['FILE PATH', 'KEY MISSING VALUE', 'TRANSLATION PREVIEW'],
          new TableSeparator(),
        ]);

        // Build the table.
        $this->buildTable($missing);

        //  return $this->table->render();
    }

    public function compareFiles($baseLocale, $altLocale, $filenames)
    {
        foreach ($filenames as $sprinklePath => $files) {
            foreach ($files as $key => $file) {
                $base = $this->parseFile("$sprinklePath/locale/{$baseLocale}/{$file}");
                $alt = $this->parseFile("$sprinklePath/locale/{$altLocale}/{$file}");
                $missing[$sprinklePath . '/locale' . '/' . $altLocale . '/' . $file] = $this->recursive_array_intersect_key($alt, $base);
            }
        }
        print_r($missing);

        return $missing;
    }

    public function recursive_array_intersect_key(array $array1, $array2)
    {
        $array1 = array_intersect_key($array1, $array2);
        foreach ($array1 as $key => &$value) {
            if (is_array($value) && is_array($array2[$key])) {
                $value = $this->recursive_array_intersect_key($value, $array2[$key]);
            }
        }

        return $array1;
    }

    /**
     * Search through locale files and find empty values.
     *
     * @param string $locale    Locale that is being searched.
     * @param array  $filenames Filenames to search.
     *
     * @return array Filepath,
     */
    public function searchFilesForNull($files)
    {
        foreach ($files as $key => $file) {
            $missing[$file] = $this->findMissing($this->parseFile($file));
        }

        return $missing;
    }

    /**
     * Get a list of locale file paths.
     *
     * @param array $locale Array of locale(s) to get files for.
     *
     * @return array
     */
    public function getFilePaths($locale)
    {
        // Set up a locator class
        $locator = $this->ci->locator;
        $builder = new LocalePathBuilder($locator, 'locale://', $locale);
        $loader = new ArrayFileLoader($builder->buildPaths());

        // Get nested array [0].
        return array_values((array) $loader)[0];
    }

    /**
     * Find keys with missing values. Collapses keys into array dot syntax.
     *
     * @param array $array Locale translation file.
     *
     * @return array Keys with missing values.
     */
    public function findMissing($array, $prefix = '')
    {
        $result = [];
        foreach ($array as $key=>$value) {
            if (is_array($value)) {
                $result = $result + $this->findMissing($value, $prefix . $key . '.');
            } else {
                $result[$prefix . $key] = $value;
            }
        }

        // We only want empty values.
        return array_filter($result, function ($key) {
            return empty($key);
        });
    }

    /**
     * Populate table with file paths, keys of missing values, and a preview in a specific locale.
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
            } else {
                $this->table->addRow([$this->path, $key, substr($this->translator->translate($key), 0, $this->maxLength)]);
            }
        }
    }

    /**
     * Sets up translator for a specific locale.
     *
     * @param string $locale Locale to be used for translation.
     */
    public function setTranslation(string $locale)
    {
        // Setup the translator. Set with -t or defaults to en_US
        $locator = $this->ci->locator;
        $builder = new LocalePathBuilder($locator, 'locale://', [$locale]);
        $loader = new ArrayFileLoader($builder->buildPaths());
        $this->translator = new MessageTranslator($loader->load());
    }

    /**
     * @return array Locales to check for missing keys.
     */
    public function getLocales()
    {
        // If set, use the locale from the -c option.
        if ($this->localesToCheck) {
            return explode(',', $this->localesToCheck);
        } else {
            return array_keys($this->ci->config['site']['locales']['available']);
        }
    }
}
