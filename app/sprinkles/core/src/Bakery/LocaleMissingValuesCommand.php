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
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('locale:missing-values')
        ->setHelp("This command provides a summary of missing values for locale translation files. Missing keys are found by searching for empty and/or duplicate values. Either option can be turned off - see options for this command. E.g. running 'locale:missing-values -b en_US -c es_ES' will compare all es_ES and en_US locale files and find any values that are identical between the two locales, as well as searching all es_ES locale files for empty ('') values.")
        ->addOption('base', 'b', InputOption::VALUE_REQUIRED, 'The base locale used for comparison and translation preview.', 'en_US')
        ->addOption('check', 'c', InputOption::VALUE_REQUIRED, 'One or more specific locales to check. E.g. "fr_FR,es_ES"', null)
        ->addOption('length', 'l', InputOption::VALUE_REQUIRED, 'Set the length for preview column text.', 50)
        ->addOption('empty', 'e', InputOption::VALUE_NONE, 'Setting this will skip check for empty strings.')
        ->addOption('duplicates', 'd', InputOption::VALUE_NONE, 'Setting this will skip comparison check.');

        $this->setDescription('Generate a table of keys with missing values.');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->io->title('Missing Locale Values');

        // Option -c. The locales to be checked.
        $this->localesToCheck = $input->getOption('check');

        // The locale for the 'preview' column. Defaults to en_US if not set.
        $baseLocale = $input->getOption('base');

        $this->length = $input->getOption('length');

        $this->setTranslation($baseLocale);

        $locales = $this->getLocales($baseLocale);

        $files = $this->getFilePaths($locales);

        $baseLocaleFileNames = $this->getFilenames($baseLocale);

        $this->io->writeln(['Locales to check: |' . implode('|', $locales) . '|']);

        if ($input->getOption('empty') != true) {
            $this->io->section('Searching for empty values.');

            $this->newTable($output);

            $missing[] = $this->searchFilesForNull($files);

            $this->table->addRows([
              ['FILE PATH', 'KEY', 'TRANSLATION PREVIEW'],
              new TableSeparator(),
            ]);

            // Build the table.
            $this->buildTable($missing);

            $this->table->render();
            $this->io->newline(2);
        }

        if ($input->getOption('duplicates') != true) {
            $this->io->section('Searching for duplicate values.');

            foreach ($locales as $key => $altLocale) {
                $duplicates[] = $this->compareFiles($baseLocale, $altLocale, $baseLocaleFileNames);
            }

            $this->newTable($output);

            $this->table->addRows([
              ['FILE PATH', 'KEY', 'DUPLICATE VALUE'],
              new TableSeparator(),
            ]);
            $this->buildTable($duplicates);
        }

        return $this->table->render();
    }

    /**
     * Intersect two arrays with considertaion of both keys and values.
     *
     * @param array $primary_array
     * @param array $secondary_array
     *
     * @return array Matching keys and values that are found in both arrays.
     */
    protected function arrayIntersect($primary_array, $secondary_array)
    {
        if (!is_array($primary_array) || !is_array($secondary_array)) {
            return false;
        }

        if (!empty($primary_array)) {
            foreach ($primary_array as $key => $value) {
                if (!isset($secondary_array[$key])) {
                    unset($primary_array[$key]);
                } else {
                    if (serialize($secondary_array[$key]) != serialize($value)) {
                        unset($primary_array[$key]);
                    }
                }
            }

            return $primary_array;
        } else {
            return [];
        }
    }

    /**
     * Populate table with file paths, keys of missing/duplicate values, and a preview in a specific locale.
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
            } elseif (strpos($key, '@') === false) {
                $this->table->addRow([$this->path, $key, $this->translator->translate($key)]);
            }
        }
    }

    /**
     * Iterate over sprinkle locale files and find duplicates.
     *
     * @param string $baseLocale Locale being compared against.
     * @param string $altLocale  Locale to find missing values for.
     * @param array  $filenames  Sprinkle locale files that will be compared.
     *
     * @return array Intersect of keys with identical values.
     */
    protected function compareFiles($baseLocale, $altLocale, $filenames)
    {
        foreach ($filenames as $sprinklePath => $files) {
            foreach ($files as $key => $file) {
                $base = $this->arrayFlatten($this->parseFile("$sprinklePath/locale/{$baseLocale}/{$file}"));
                $alt = $this->arrayFlatten($this->parseFile("$sprinklePath/locale/{$altLocale}/{$file}"));

                $missing[$sprinklePath . '/locale' . '/' . $altLocale . '/' . $file] = $this->arrayIntersect($base, $alt);
            }
        }

        return $missing;
    }

    /**
     * Find keys with missing values. Collapses keys into array dot syntax.
     *
     * @param array $array Locale translation file.
     *
     * @return array Keys with missing values.
     */
    protected function findMissing($array, $prefix = '')
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
     * Get a list of locale file paths.
     *
     * @param array $locale Array of locale(s) to get files for.
     *
     * @return array
     */
    protected function getFilePaths($locale)
    {
        // Set up a locator class
        $locator = $this->ci->locator;
        $builder = new LocalePathBuilder($locator, 'locale://', $locale);
        $loader = new ArrayFileLoader($builder->buildPaths());

        // Get nested array [0].
        return array_values((array) $loader)[0];
    }

    protected function newTable($output)
    {
        $this->table = new Table($output);
        $this->table->setStyle('compact');
        $this->table->setColumnMaxWidth(2, $this->length);
    }

    /**
     * Search through locale files and find empty values.
     *
     * @param array $files Filenames to search.
     *
     * @return array
     */
    protected function searchFilesForNull($files)
    {
        foreach ($files as $key => $file) {
            $missing[$file] = $this->findMissing($this->parseFile($file));
        }

        return $missing;
    }

    /**
     * Sets up translator for a specific locale.
     *
     * @param string $locale Locale to be used for translation.
     */
    protected function setTranslation(string $locale)
    {
        // Setup the translator. Set with -t or defaults to en_US
        $locator = $this->ci->locator;
        $builder = new LocalePathBuilder($locator, 'locale://', [$locale]);
        $loader = new ArrayFileLoader($builder->buildPaths());
        $this->translator = new MessageTranslator($loader->load());
    }
}
