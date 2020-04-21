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

/**
 * locale:info command.
 * List all available locales.
 *
 * @author Louis Charette
 */
class LocaleInfoCommand extends BaseCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('locale:info')
             ->setHelp('This command list all available locale as well as the defaut locale.')
             ->setDescription('Informations about available locales');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->io->title('Available locales');

        /** @var \UserFrosting\Sprinkle\Core\I18n\SiteLocale */
        $localeService = $this->ci->locale;

        // Get available locales
        /** @var \UserFrosting\I18n\Locale[] $available */
        $available = $localeService->getAvailable();

        // Prepare table headers and lines array
        $table = new Table($output);
        $table->setHeaders(['Identifier', 'Name', 'Regional', 'Parents', 'Default']);

        foreach ($available as $locale) {
            $table->addRow([
                $locale->getIdentifier(),
                $locale->getName(),
                $locale->getRegionalName(),
                implode(', ', $locale->getDependentLocalesIdentifier()),
                ($locale->getIdentifier() === $localeService->getDefaultLocale()) ? 'Yes' : '',
            ]);
        }

        $table->render();

        // Everything went fine, return 0 exit code
        return 0;
    }
}
