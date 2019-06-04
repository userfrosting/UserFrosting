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
use Symfony\Component\Console\Output\OutputInterface;
use UserFrosting\System\Bakery\BaseCommand;

class LocaleMissingKeysCommand extends BaseCommand
{
    protected $locales = [
        'zh_CN',
        'es_ES',
        'ar',
        'pt_PT',
        'ru_RU',
        'de_DE',
        'fr_FR',
        'tr',
        'it_IT',
        'th_TH',
        'fa',
        'el',
      ];

    protected function configure()
    {
        // the name of the command (the part after "php bakery")
        $this->setName('localeUtil:missing-keys');

        // the short description shown while running "php bakery list"
        $this->setDescription('Identifies missing locale keys by comparing en_US with other locale files.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $localesArray = $this->locales;

        $enUS = $this->getEnglishUS();

        foreach ($enUS as $filename => $path) {
            $enUSFile = $this->fetchArray($path);

            foreach ($localesArray as $index => $localeDirectory) {
                $altLocale = $this->fetchArray($this->ci->locator->getResource("locale://{$localeDirectory}/{$filename}"));

                if (!empty(array_diff_key($enUSFile, $altLocale))) {
                    $difference[$localeDirectory][$path] = array_diff_key($enUSFile, $altLocale);

                    $table = new Table($output);

                    foreach ($difference[$localeDirectory][$path] as $k => $v) {
                        $table->setHeaders([$localeDirectory, key($difference[$localeDirectory])]);
                        $table->addRow([$k]);
                    }
                    $table->render();
                }
            }
        }
    }

    /**
     * Access file contents through inclusion.
     *
     * @param string $path The path of file to be included.
     */
    private function fetchArray($path)
    {
        return include "$path";
    }

    /**
     * Returns filenames and paths for en_US locale files.
     *
     * @return array en_US locale filenames and paths.
     */
    private function getEnglishUS()
    {
        $en = $this->ci->locator->listResources('locale://en_US');

        foreach ($en as $filename => $path) {
            $enUS[$path->getBasename()] = $path->getAbsolutePath();
        }

        return $enUS;
    }
}
