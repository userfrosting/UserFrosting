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

class LocalMissingValuesCommand extends BaseCommand
{
    protected $locales = [
        'en_US',
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
        $this->setName('localeUtil:missing-values');
        // A missing value is equal to an empty string.
        $this->setDescription('Identifies missing locale values by comparing en_US with other locale files.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $localesArray = $this->locales;

        $enUS = $this->getEnglishUS();

        foreach ($enUS as $filename => $path) {
            $enUSFile = $this->fetchArray($path);

            foreach ($localesArray as $index => $localeDirectory) {
                $locale = $this->fetchArray($this->ci->locator->getResource("locale://{$localeDirectory}/{$filename}"));

                foreach ($locale as $key => $value) {
                    $table = new Table($output);

                    if (empty($value)) {
                        $table->setHeaders([$localeDirectory, $path]);
                        $table->addRow([$key]);
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
