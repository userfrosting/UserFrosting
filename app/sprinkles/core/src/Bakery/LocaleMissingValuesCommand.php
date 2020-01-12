<?php

/*
 * UserFrosting (http://www.userfrosting.com)
 *
 * @link      https://github.com/userfrosting/UserFrosting
 * @copyright Copyright (c) 2019 Alexander Weissman
 * @license   https://github.com/userfrosting/UserFrosting/blob/master/LICENSE.md (MIT License)
 */

namespace UserFrosting\Sprinkle\Core\Bakery;

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
     * @var MessageTranslator
     */
    protected $translator;

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('locale:missing-values')
             ->setHelp("This command provides a summary of missing values for locale translation files. Missing keys are found by searching for empty and/or duplicate values. Either option can be turned off - see options for this command. E.g. running 'locale:missing-values -b en_US -c es_ES' will compare all es_ES and en_US locale files and find any values that are identical between the two locales, as well as searching all es_ES locale files for empty ('') values. This can be helpful to list all values in a specific languages that are present, but might need translation. For example, listing all English strings found in the French locale.")
             ->addOption('base', 'b', InputOption::VALUE_REQUIRED, 'The base locale used for comparison and translation preview.', 'en_US')
             ->addOption('check', 'c', InputOption::VALUE_REQUIRED, 'One or more specific locales to check. E.g. "fr_FR,es_ES"', null)
             ->addOption('length', 'l', InputOption::VALUE_REQUIRED, 'Set the length for preview column text.', 50)
             ->addOption('empty', 'e', InputOption::VALUE_NONE, 'Setting this will skip check for empty strings.')
             ->addOption('duplicates', 'd', InputOption::VALUE_NONE, 'Setting this will skip comparison check.')
             ->setDescription('Generate a table of keys with missing values.');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->io->title('Missing Locale Values');

        // Option -c. The locales to be checked.
        $localesToCheck = $input->getOption('check');

        // The locale for the 'preview' column. Defaults to en_US if not set.
        $baseLocale = $input->getOption('base');
        $baseLocaleFileNames = $this->getFilenames($baseLocale);

        // Get `length` option
        $length = $input->getOption('length');

        // Set translator to base locale
        $this->setTranslation($baseLocale);

        // Get locales and files for said locales
        $locales = $this->getLocales($baseLocale, $localesToCheck);
        $files = $this->getFilePaths($locales);

        $this->io->writeln(['Locales to check: |' . implode('|', $locales) . '|']);

        // Proccess empty
        if ($input->getOption('empty') === false) {
            $this->io->section('Searching for empty values.');

            $missing[] = $this->searchFilesForNull($files);

            if (!empty($missing[0])) {
                $this->newTable($output, $length);

                $this->table->setHeaders([
                    ['File path', 'Key', 'Translation preview'],
                ]);

                // Build the table.
                $this->buildTable($missing);

                $this->table->render();
                $this->io->newline(2);
            } else {
                $this->io->writeln('No empty values found!');
            }
        }

        if ($input->getOption('duplicates') === false) {
            $this->io->section('Searching for duplicate values.');

            foreach ($locales as $locale) {
                $duplicates[] = $this->compareFiles($baseLocale, $locale, $baseLocaleFileNames);
            }

            if (!empty($duplicates[0])) {
                $this->newTable($output, $length);

                $this->table->setHeaders([
                    ['File path', 'Key', 'Translation preview'],
                ]);

                $this->newTable($output, $length);
                $this->table->setHeaders([
                    ['File path', 'Key', 'Duplicate value'],
                ]);
                $this->buildTable($duplicates);
                $this->table->render();
            } else {
                $this->io->writeln('No empty values found!');
            }
        }
    }

    /**
     * Intersect two arrays with considertaion of both keys and values.
     *
     * @param array $primary_array
     * @param array $secondary_array
     *
     * @return array Matching keys and values that are found in both arrays.
     */
    protected function arrayIntersect(array $primary_array, array $secondary_array): array
    {
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
            // We only want empty values.
            return array_filter($primary_array, function ($key) {
                return strpos($key, '@') === false;
            }, ARRAY_FILTER_USE_KEY);
        } else {
            return [];
        }
    }

    /**
     * Populate a table with data.
     *
     * @param array $array File paths and missing keys.
     * @param int   $level Nested array depth.
     */
    protected function buildTable(array $array, int $level = 1): void
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
                $this->table->addRow([$this->path, $key, $this->translator->translate($key)]);
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function compareFiles(string $baseLocale, string $altLocale, array $filenames): array
    {
        foreach ($filenames as $sprinklePath => $files) {
            foreach ($files as $key => $file) {
                $base = $this->arrayFlatten($this->parseFile("$sprinklePath/locale/$baseLocale/$file"));
                $alt = $this->arrayFlatten($this->parseFile("$sprinklePath/locale/$altLocale/$file"));

                $missing[$sprinklePath . '/locale' . '/' . $altLocale . '/' . $file] = $this->arrayIntersect($base, $alt);
            }
        }

        return array_filter($missing);
    }

    /**
     * Find keys with missing values.
     * Collapses keys into array dot syntax.
     * Missing values are identified using the same rules as the empty() method.
     *
     * @see https://www.php.net/manual/en/function.empty.php#refsect1-function.empty-returnvalues
     *
     * @param array  $array  Locale translation file.
     * @param string $prefix
     *
     * @return array Keys with missing values.
     */
    protected function findMissing(array $array, string $prefix = ''): array
    {
        $result = [];
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $result = $result + $this->findMissing($value, $prefix . $key . '.');
            } else {
                $result[$prefix . $key] = $value;
            }
        }

        // We only want empty values.
        return array_filter($result, function ($val, $key) {
            return empty($val) && strpos($key, '@') === false;
        }, ARRAY_FILTER_USE_BOTH);
    }

    /**
     * Get a list of locale file paths.
     *
     * @param array $locale Array of locale(s) to get files for.
     *
     * @return array
     */
    protected function getFilePaths(array $locale): array
    {
        // Set up a locator class
        $locator = $this->ci->locator;
        $builder = new LocalePathBuilder($locator, 'locale://', $locale);
        $loader = new ArrayFileLoader($builder->buildPaths());

        // Get nested array [0].
        return array_values((array) $loader)[0];
    }

    /**
     * {@inheritdoc}
     */
    protected function newTable(OutputInterface $output, int $length): void
    {
        parent::newTable($output);
        $this->table->setColumnMaxWidth(2, $length);
    }

    /**
     * Search through locale files and find empty values.
     *
     * @param array $files File paths to search.
     *
     * @return array
     */
    protected function searchFilesForNull(array $files): array
    {
        foreach ($files as $file) {
            $missing[$file] = $this->findMissing($this->parseFile($file));

            if (empty($missing[$file])) {
                unset($missing[$file]);
            }
        }

        return $missing;
    }

    /**
     * Sets up translator for a specific locale.
     *
     * @param string $locale Locale to be used for translation.
     */
    protected function setTranslation(string $locale): void
    {
        // Setup the translator. Set with -b or defaults to en_US
        $locator = $this->ci->locator;
        $builder = new LocalePathBuilder($locator, 'locale://', [$locale]);
        $loader = new ArrayFileLoader($builder->buildPaths());
        $this->translator = new MessageTranslator($loader->load());
    }
}
