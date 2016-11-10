<?php
/**
 * Created by PhpStorm.
 * User: tompradat
 * Date: 04/11/2016
 * Time: 16:06
 */

namespace GoogleShopping\Commands;


use GoogleShopping\Service\CatalogService;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Thelia\Command\ContainerAwareCommand;

class SyncCatalogCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName("googleshopping:update-catalog")
            ->setDescription("Update google shopping products prices and availability");
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // ADD THELIA PRODUCTS RELATED TO GOOGLE SHOPPING CATEGORIES IN THE SYNC QUEUE
        $command = $this->getApplication()->find('googleshopping:expirated-products');
        $command->run(new ArrayInput([]), $output);

        $this->initRequest();

        /** @var CatalogService $catalogService */
        $catalogService = $this->getContainer()->get('googleshopping.catalog.service');

        $syncSuccess = $catalogService->syncCatalog();

        if ($syncSuccess) {
            $output->writeln('The google shopping products have been updated');
        } else {
            $output->writeln('Something went wrong, the google shopping products could not be updated');
        }
    }
}