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

        // Option -c. Set to compare one or more specific locales.
        $localesToCheck = $input->getOption('check');

        // Get locales to check
        $locales = $this->getLocales($baseLocale, $localesToCheck);

        $this->io->writeln('Locales to check: |' . implode('|', $locales) . '|');
        $this->io->section("Searching for missing keys using $baseLocale for comparison.");

        $difference = [];

        foreach ($locales as $locale) {

            // Make sure locale exist
            if (!in_array($locale, array_keys($this->ci->config['site']['locales']['available']))) {
                $this->io->warning("Locale '$locale' is not available in config.");
            } else {
                $difference = array_merge($difference, $this->compareFiles($baseLocale, $locale));
            }
        }

        // Build the table.
        if (!empty($difference)) {
            $this->newTable($output);
            $this->table->setHeaders(['File path', 'Missing key']);
            $this->buildTable($difference);
            $this->table->render();

            $this->io->writeln('Missing keys found successfully');
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
     */
    protected function buildTable(array $array): void
    {
        foreach ($array as $file => $missing) {
            foreach ($missing as $key => $value) {
                $this->table->addRow([$file, $key]);
            }
        }
    }

    /**
     * Iterate over sprinkle locale files and find the difference for two locales.
     *
     * @param string $baseLocale Locale being compared against.
     * @param string $altLocale  Locale to find missing keys for.
     *
     * @return array The keys in $baseLocale that do not exist in $altLocale.
     */
    protected function compareFiles(string $baseLocale, string $altLocale): array
    {
        // Get all file for base locale
        $files = $this->ci->locator->listResources("locale://$baseLocale", true);

        // Return value
        $difference = [];

        foreach ($files as $basefile) {

            // Get alt locale path
            // Stream Path is used as security, in case a sprinkle would be called the same as a locale
            $streamPath = $basefile->getStream()->getPath();
            $altPath = str_replace("$streamPath/$baseLocale/", "$streamPath/$altLocale/", $basefile->getPath());

            $base = $this->parseFile($basefile);
            $alt = $this->parseFile($altPath);
            $diff = $this->getDifference($base, $alt);

            $difference[$altPath] = $this->arrayFlatten($diff);
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
        // Return empty array if file not found
        if (!file_exists($path)) {
            return [];
        }

        $content = include "$path";

        // Consider not found file returns an empty array
        if ($content === false || !is_array($content)) {
            return [];
        }

        return $content;
    }
}
