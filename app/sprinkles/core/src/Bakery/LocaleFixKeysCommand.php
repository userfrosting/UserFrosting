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
use UserFrosting\Support\Repository\Repository;

/**
 * locale:fix-keys command.
 * Find missing values in locale translation files.
 *
 * @author Amos Folz
 */
class LocaleFixKeysCommand extends LocaleMissingValuesCommand
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
        $this->setName('locale:fix-keys')
        ->addOption('translate', 't', InputOption::VALUE_REQUIRED, 'The locale of preview.', 'en_US')
        ->addOption('check', 'c', InputOption::VALUE_REQUIRED, 'Specific locales to check.', null);

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

        // The locale for the 'preview' column. Defaults to en_US if not set.
        $translationLocale = $input->getOption('translate');

        $this->setTranslation($translationLocale);

        $locales = $this->getLocales();

        $files = $this->getFilePaths($locales);

        $baseLocale = 'en_US';

        $baseLocaleFileNames = $this->getFilenames($baseLocale);

        $missing[] = $this->searchFiles($files);

        $this->fix('en_US', 'fa');
//        $this->test('en_US', 'fa');

        $localesAvailable = ['fa', 'en_US'];

        /*
                foreach ($localesAvailable as $key => $altLocale) {
                    $difference[] = $this->test($baseLocale, $altLocale, $baseLocaleFileNames);
                }
        */

        $this->test();

        $this->table->setHeaders([
          [new TableCell('LOCALES SEARCHED: |' . implode('|', $locales) . '|', ['colspan' => 3])],
          [new TableCell("USING | $translationLocale | FOR TRANSLATION PREVIEW", ['colspan' => 3])],

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

    private function parseArrayToBrackets($input)
    {
        $output = json_decode(str_replace(['(', ')'], ['&#40', '&#41'], json_encode($input)), true);
        $output = var_export($output, true);
        $output = str_replace(['array (', ')', '&#40', '&#41'], ['[', ']', '(', ')'], $output);

        return $output;
    }

    public function test()
    {

        // Set up a locator class
        $locator = $this->ci->locator;
        $builder = new LocalePathBuilder($locator, 'locale://', [$base, $altLocale]);

        $test = $builder->buildPaths();
        //    print_r($test);

        $temp = new Repository();

        $testA = $this->useFile('/home/vagrant/userfrosting/app/sprinkles/pastries/locale/en_US/pastries.php');
        $testB = $this->useFile('/home/vagrant/userfrosting/app/sprinkles/pastries/locale/es_ES/pastries.php');

        $temp->mergeItems(null, $testA);
        $temp->mergeItems(null, $testB);

        //  print_r(get_class_methods($temp));

        //  $temp->all();
        $test1 = $temp->all();
        print_r($test1);

        $test1 = $this->parseArrayToBrackets($test1);

        //Encode the array into a JSON string.
        $encodedString = json_encode($temp->all());

        passthru('cat header > test.php');

        //Save the JSON string to a text file.
        file_put_contents('test.php', $test1, FILE_APPEND);

        passthru('pwd');

        //  passthru("echo $encodedString >> test.php");

        $loader = new ArrayFileLoader($builder->buildPaths());

        foreach ($filenames as $sprinklePath => $files) {
            foreach ($files as $key => $file) {
                $base = $this->useFile('/home/vagrant/userfrosting/app/sprinkles/pastries/locale/en_US/message.php');
                $alt = $this->useFile('/home/vagrant/userfrosting/app/sprinkles/pastries/locale/es_ES/message.php');

                //  $base = "$sprinklePath/locale/{$baseLocale}/{$file}";
                //  $alt = "$sprinklePath/locale/{$altLocale}/{$file}";

                $difference[$sprinklePath . '/locale' . '/' . $altLocale . '/' . $file] = $this->getDifference($base, $alt);

                $loader = new ArrayFileLoader($builder->buildPaths([$base, $alt]));

                // Create the MessageTranslator object
                $translator = new MessageTranslator($loader->load());

                //    print_r($translator);
            }
        }

        return $difference;
    }

    public function fix($base, $localeToFix)
    {
        $temp = new Repository();

        // Set up a locator class
        //      $locator = $this->ci->locator;
        //      $builder = new LocalePathBuilder($locator, 'locale://', [$base, $localeToFix]);

        //  print_r($test);
        $base = '/home/vagrant/userfrosting/app/sprinkles/pastries/locale/en_US/pastries.php';
        $alt = '/home/vagrant/userfrosting/app/sprinkles/pastries/locale/es_ES/message.php';

        $loader = new ArrayFileLoader([$base, $alt]);

        //print_r($loader);

        // Create the MessageTranslator object
        $translator = new MessageTranslator($loader->load());

        //  print_r($translator);

        // Get nested array [0].
        // return array_values((array) $loader)[0];
    }

    /**
     * Search through locale files and find empty values.
     *
     * @param string $locale    Locale that is being searched.
     * @param array  $filenames Filenames to search.
     *
     * @return array Filepath,
     */
    public function searchFiles($files)
    {
        foreach ($files as $key => $file) {
            $missing[$file] = $this->findMissing($this->useFile($file));
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
     * Find keys with missing values.
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
        // print_r($result);
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
                //It is not an array and not '0', so add the row.
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
