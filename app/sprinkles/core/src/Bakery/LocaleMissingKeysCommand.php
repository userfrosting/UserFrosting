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
    protected $missing = [];

    protected $table = [];

    protected $auxLocale;

    protected function configure()
    {
        $this->setName('locale:missing-keys')
        ->addOption('base', 'b', InputOption::VALUE_REQUIRED, 'The base locale to compare against.', 'en_US')
        ->addOption('compare', 'c', InputOption::VALUE_REQUIRED, 'A optional second locale to compare against', null);

        $this->setDescription('Identify missing locale keys through comparison.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->$table = new Table($output);

        // The locale that other locales are compared to. Defaults to en_US if not set.
        $baseLocale = $input->getOption('base');

        $this->auxLocale = $input->getOption('compare');
        //print_r($this->auxLocale = $input->getOption('compare'));

        $baseLocaleFileNames = $this->getBaseFileNames($baseLocale);

        $localesAvailable = $this->getAvailableLocales();

        foreach ($localesAvailable as $key => $altLocale) {
            $difference[] = $this->compareFiles($baseLocale, $altLocale, $baseLocaleFileNames);
        }

        $this->$table->setHeaders([new TableCell('COMPARING AGAINST: ' . $baseLocale, ['colspan' => 2])]);
        $this->$table->addRows([['FILE PATH', 'MISSING KEY'], new TableSeparator()]);

        return $this->buildTable($difference);
    }

    public function buildTable($difference)
    {
        foreach ($difference as $key => $value) {
            {
              foreach ($value as $k => $v) {
                  if (!is_array($v) && $v != '0') {
                      $this->$table->addRow([$v]);
                  } else {
                      foreach ($v as $a => $b) {
                          if (!is_array($b) && $b != '0') {
                              $this->$table->addRow([$k, $b]);
                          }
                      }
                  }
              }
           }
        }

        return $this->$table->render();
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
     * @return array
     */
    private function compareFiles($baseLocale, $altLocale, $filenames)
    {
        foreach ($filenames as $sprinklePath => $files) {
            foreach ($files as $key => $file) {
                $base = $this->getFile("$sprinklePath/locale/{$baseLocale}/{$file}");
                $alt = $this->getFile("$sprinklePath/locale/{$altLocale}/{$file}");
                //  print_r($this->ci->locator->getResource("locale://{$altLocale}/{$file}"));

                $difference[$sprinklePath . '/locale' . '/' . $altLocale . '/' . $file] = $this->getDifference($base, $alt);
            }
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
        print_r($path . "\r\n");

        return include "$path";
    }

    /**
     * Gets all locale files for a specific locale.
     *
     * @param  string $locale The locale to get files for.
     * @return array  Locale files per sprinkle.
     */
    private function getBaseFileNames($locale)
    {
        $file = ($this->ci->locator->listResources("locale://{$locale}", true));
        foreach ($file as $filename => $path) {
            $files[$path->getLocation()->getPath()][] = $path->getBaseName();
            //  print_r($path->getLocation()->getPath());
        }
        //  print_r($files);

        return $files;
    }

    private function getAvailableLocales()
    {
        if ($this->auxLocale) {
            return [$this->auxLocale];
        } else {
            return array_keys($this->ci->config['site']['locales']['available']);
        }
    }
}
