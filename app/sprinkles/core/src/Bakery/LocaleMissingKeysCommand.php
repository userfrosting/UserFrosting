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
use Symfony\Component\Console\Helper\TableStyle;
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
    protected $path;

    /**
     * @var array
     */
    protected $table = [];

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('locale:missing-keys')
             ->setHelp("This command provides a summary of missing keys for locale translation files. E.g. running 'locale:missing-keys -b en_US -c es_ES' will compare all es_ES and en_US locale files and generate a table listing the filepath and missing keys found from the `-c` locale.")
             ->addOption('base', 'b', InputOption::VALUE_REQUIRED, 'The base locale to compare against.', 'en_US')
             ->addOption('check', 'c', InputOption::VALUE_REQUIRED, 'One or more specific locales to check. E.g. "fr_FR,es_ES"', null)
             ->setDescription('Generate a table of missing locale keys.');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->io->title('Missing Locale Keys');

        // The "base" locale to compare other locales against. Defaults to en_US if not set.
        $baseLocale = $input->getOption('base');
        $baseLocaleFileNames = $this->getFilenames($baseLocale);

        // Option -c. Set to compare one or more specific locales.
        $localesToCheck = $input->getOption('check');

        // Get locales to check
        $locales = $this->getLocales($baseLocale, $localesToCheck);

        $this->io->writeln('Locales to check: |' . implode('|', $locales) . '|');
        $this->io->section("Searching for missing keys using $baseLocale for comparison.");

        foreach ($locales as $locale) {

            // Make sure locale exist
            if (!in_array($locale, array_keys($this->ci->config['site']['locales']['available']))) {
                $this->io->warning("Locale '$locale' is not available in config.");
            } else {
                $difference[] = $this->compareFiles($baseLocale, $locale, $baseLocaleFileNames);
            }
        }

        // Build the table.
        if (!empty($difference[0])) {
            $this->newTable($output);
            $this->table->setHeaders(['File path', 'Missing key']);
            $this->buildTable($difference);
            $this->table->render();
        } else {
            $this->io->writeln('No missing keys found!');
        }
    }

    /**
     * Flattens a nested array into dot syntax.
     *
     * @param array  $array  The array to flatten.
     * @param string $prefix (Default '')
     *
     * @return array Keys with missing values.
     */
    protected function arrayFlatten(array $array, string $prefix = ''): array
    {
        $result = [];
        foreach ($array as $key => $value) {
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
            } elseif ($value != '0') {
                //It is not an array and not '0', so add the row.
                $this->table->addRow([$this->path, $key]);
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
    protected function compareFiles(string $baseLocale, string $altLocale, array $filenames): array
    {
        foreach ($filenames as $sprinklePath => $files) {
            foreach ($files as $key => $file) {
                $base = $this->parseFile("$sprinklePath/locale/{$baseLocale}/{$file}");
                $alt = $this->parseFile("$sprinklePath/locale/{$altLocale}/{$file}");
                $diff = $this->getDifference($base, $alt);
                $difference[$sprinklePath . '/locale' . '/' . $altLocale . '/' . $file] = $this->arrayFlatten($diff);
            }
        }

        return array_filter($difference);
    }

    /**
     * Find the missing keys between two arrays.
     *
     * @param array $array1
     * @param array $array2
     *
     * @return array
     */
    protected function getDifference(array $array1, array $array2): array
    {
        $difference = [];

        foreach ($array1 as $key => $value) {
            if (is_array($value)) {
                if (!isset($array2[$key])) {
                    $difference[$key] = $value;
                } else {
                    if (is_array($array2[$key])) {
                        $difference[$key] = $this->getDifference($value, $array2[$key]);
                    } else {
                        // If the second array returns a string for a key while
                        // the first is an array, the whole first array is considered missing
                        $difference[$key] = $value;
                    }
                }
            } elseif (!isset($array2[$key])) {
                $difference[$key] = $value;
            }
        }

        return $difference;
    }

    /**
     * Gets all locale files for a specific locale.
     *
     * @param string $locale The locale being compared against.
     *
     * @return array Locale files and locations for the locale being compared against.
     */
    public function getFilenames(string $locale): array
    {
        $file = ($this->ci->locator->listResources("locale://$locale", true));
        foreach ($file as $filename => $path) {
            $files[$path->getLocation()->getPath()][] = $path->getBaseName();
        }

        return $files;
    }

    /**
     * @param string      $baseLocale     The "base" locale to compare to
     * @param string|null $localesToCheck Comma delimited list of locales to check
     *
     * @return array Locales to check.
     */
    protected function getLocales(string $baseLocale, ?string $localesToCheck): array
    {
        // If set, use the locale from the -c option.
        if ($localesToCheck) {
            return explode(',', $localesToCheck);
        } else {
            //Need to filter the base locale to prevent false positive.
            return array_diff(array_keys($this->ci->config['site']['locales']['available']), [$baseLocale]);
        }
    }

    /**
     * Set up new table with Bakery formatting.
     *
     * @param OutputInterface $output
     */
    protected function newTable(OutputInterface $output): void
    {
        $tableStyle = new TableStyle();
        $tableStyle->setVerticalBorderChars(' ')
                   ->setDefaultCrossingChar(' ')
                   ->setCellHeaderFormat('<info>%s</info>');

        $this->table = new Table($output);
        $this->table->setStyle($tableStyle);
    }

    /**
     * Access file contents through inclusion.
     *
     * @param string $path The path of file to be included.
     *
     * @return array The array returned in the included locale file
     */
    protected function parseFile(string $path): array
    {
        $content = include "$path";

        // Consider not found file returns an empty array
        if ($content === false) {
            return [];
        }

        return $content;
    }
}
