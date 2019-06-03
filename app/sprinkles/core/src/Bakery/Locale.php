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
use UserFrosting\Sprinkle\Core\Facades\Debug;

class Locale extends BaseCommand
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
        $this->setDescription('Finds missing keys by comparing en_US with other locale files.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $localesArray = $this->locales;

        $enUS = $this->getBase();

        foreach ($enUS as $filename => $path) {
            $enUSFile = $this->fetchArray($path);

            foreach ($localesArray as $key => $value) {
                $altLocale = $this->fetchArray($this->ci->locator->getResource("locale://{$value}/{$filename}"));

                if (!empty(array_diff_key($enUSFile, $altLocale))) {
                    $difference[$value][$path] = array_diff_key($enUSFile, $altLocale);

                    //    Debug::debug(print_r($difference));

                    $table = new Table($output);

                    foreach ($difference[$value][$path] as $k => $v) {
                        //    Debug::debug(print_r($k));
                        //    Debug::debug(print_r($v));
                        $table->setHeaders([$value, key($difference[$value])]);
                        $table->addRow([$k]);
                    }
                    $table->render();
                }
            }
        }
    }

    private function fetchArray($included)
    {
        return include "$included";
    }

    private function getBase()
    {
        $en = $this->ci->locator->listResources('locale://en_US');

        foreach ($en as $filename => $path) {
            $enUS[$path->getBasename()] = $path->getAbsolutePath();
        }

        return $enUS;
    }
}
