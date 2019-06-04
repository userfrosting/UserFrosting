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
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use UserFrosting\System\Bakery\BaseCommand;

class LocaleMissingKeysCommand extends BaseCommand
{
    protected $missing = [];

    protected function configure()
    {
        $this->setName('locale:missing-keys')
        ->addOption('locale', 'l', InputOption::VALUE_REQUIRED, 'The locale to compare against.', 'en_US');

        $this->setDescription('Identify missing locale keys through comparison.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // The locale that other locales are compared to. Defaults to en_US if not set.
        $baseLocale = $input->getOption('locale');

        $baseLocaleFileNames = $this->getBaseFileNames($baseLocale);
        //  print_r($baseLocaleFileNames);
        $localesAvailable = $this->getAvailableLocales();

        foreach ($localesAvailable as $altLocale) {
            //    print_r($altLocale);
            $test[$altLocale] = $this->compareFiles($baseLocale, $altLocale, $baseLocaleFileNames);
        }
        //print_r($test);

        $difference[$localeDirectory][$path] = array_diff_key($baseFile, $altFile);

        $table = new Table($output);

        foreach ($difference[$localeDirectory][$path] as $k => $v) {
            //    print_r(gettype($k) . "\r\n");
            //    print_r($v . "\r\n");
            $table->setHeaders([$localeDirectory, key($difference[$localeDirectory])]);
            $table->addRow([$k]);
        }
        $table->render();
    }

    public function getDifference($array1, $array2)
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
     * Returns filenames and paths for a locale type.
     *
     * @param string The locale to get filenames and paths for. This should be a locale as listed in config['site']['locales']['available']
     * @return array Locale filenames and paths.
     */
    private function compareFiles($baseLocale, $altLocale, $filenames)
    {
        //    print_r($filenames);
        foreach ($filenames as $file) {
            //    print_r($file);
            $base = $this->getFile($this->ci->locator->getResource("locale://{$baseLocale}/{$file}"));

            $alt = $this->getFile($this->ci->locator->getResource("locale://{$altLocale}/{$file}"));

            $difference[$file] = $this->getDifference($base, $alt);
        }

        return $difference;
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

    private function getBaseFileNames($locale)
    {
        $file = ($this->ci->locator->listResources("locale://{$locale}", true));
        //  print_r($file);
        foreach ($file as $filename => $path) {
            print_r($path->getLocatorBasePath());
            $files[$path->getAbsolutePath()] = $path->getBaseName();
        }

        return $files;
    }

    private function getAvailableLocales()
    {
        return array_keys($this->ci->config['site']['locales']['available']);
    }
}
