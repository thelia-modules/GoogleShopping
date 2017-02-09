<?php
/**
 * Created by PhpStorm.
 * User: tompradat
 * Date: 04/11/2016
 * Time: 16:07
 */

namespace GoogleShopping\Commands;


use GoogleShopping\Handler\GoogleShoppingHandler;
use GoogleShopping\Model\GoogleshoppingConfiguration;
use GoogleShopping\Model\GoogleshoppingConfigurationQuery;
use GoogleShopping\Model\GoogleshoppingProductSyncQueue;
use GoogleShopping\Model\GoogleshoppingProductSyncQueueQuery;
use GoogleShopping\Model\Map\GoogleshoppingTaxonomyTableMap;
use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\ActiveQuery\Join;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Thelia\Command\ContainerAwareCommand;
use Thelia\Core\HttpFoundation\Request;
use Thelia\Model\Country;
use Thelia\Model\CountryQuery;
use Thelia\Model\Lang;
use Thelia\Model\LangQuery;
use Thelia\Model\Map\ProductCategoryTableMap;
use Thelia\Model\Map\ProductTableMap;
use Thelia\Model\Product;
use Thelia\Model\ProductQuery;

class AddExpiratedGoogleProductsToSyncQueueCommand extends ContainerAwareCommand
{
    //The number of days left before google expiration when we consider adding product to sync queue
    const GOOGLE_EXPIRATION_CLOSE = 3;

    protected function configure()
    {
        $this
            ->setName("googleshopping:expirated-products")
            ->setDescription("Add soon expirated google shopping products to sync queue");
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        //GET PRODUCTS FROM GOOGLE SHOPPING CATEGORY
        $categoryJoin = new Join(ProductTableMap::ID, ProductCategoryTableMap::PRODUCT_ID, Criteria::INNER_JOIN);
        $googleShoppingTaxonomyJoin = new Join(ProductCategoryTableMap::CATEGORY_ID, GoogleshoppingTaxonomyTableMap::THELIA_CATEGORY_ID, Criteria::INNER_JOIN);

        $products = ProductQuery::create()
            ->addJoinObject($categoryJoin)
            ->addJoinObject($googleShoppingTaxonomyJoin)
            ->distinct()
            ->find()
        ;

        $configurations = GoogleshoppingConfigurationQuery::create()->find();

        foreach ($configurations as $configuration) {
            $pseList = $this->getExpiratingPse($configuration, $products);

            foreach ($pseList as $pseId) {
                $pseSync = GoogleshoppingProductSyncQueueQuery::create()
                    ->filterByProductSaleElementsId($pseId)
                    ->findOne()
                ;

                if ($pseSync === null) {
                    $pseSync = new GoogleshoppingProductSyncQueue();
                    $pseSync->setProductSaleElementsId($pseId)->save();

                    $output->writeln('Pse ' . $pseId . ' has been added to google shopping sync queue');
                }
            }

        }

    }

    protected function getExpiratingPse(GoogleshoppingConfiguration $configuration, $products)
    {
        $merchantId = $configuration->getMerchantId();
        $langId = $configuration->getLangId();
        $targetCountryId = $configuration->getCountryId();

        $pseList = [];

        $this->initRequest();

        $targetCountry = CountryQuery::create()->findOneById($targetCountryId);

        if ($targetCountry) {
            $isoAlpha2 = $targetCountry->getIsoalpha2();
        } else {
            $isoAlpha2 = Country::getDefaultCountry()->getIsoalpha2();
        }

        $lang = LangQuery::create()->findOneById($langId);

        if ($lang) {
            $langCode = $lang->getCode();
        } else {
            $langCode = Lang::getDefaultLanguage()->getCode();
        }

        $i = 0;
        $entries = [];
        $pseCorresp = [];

        /** @var Product $product */
        foreach ($products as $product) {
            $i++;
            $pse = $product->getDefaultSaleElements();

            $pseCorresp[$product->getId()] = $pse->getId();

            $googleProductId = "online:".$langCode.":".$isoAlpha2.":".$pse->getId();

            $entry =  [
                'batchId' => $product->getId(),
                'merchantId' => $merchantId,
                'method' => 'get',
                'productId' => $googleProductId
            ];

            $entries[] = $entry;
        }

        /** @var RequestStack $requestStack */
        $requestStack = $this->getContainer()->get('request_stack');

        $googleShoppingHandler = (new GoogleShoppingHandler($this->getContainer(), $requestStack));
        $client = $googleShoppingHandler->createGoogleClient();
        $googleShoppingService = new \Google_Service_ShoppingContent($client);

        $statusesRequest = new \Google_Service_ShoppingContent_ProductstatusesCustomBatchRequest();
        $statusesRequest->setEntries($entries);

        $params['fields'] = 'entries(batchId, productStatus/googleExpirationDate)';
        $googleStatuses = $googleShoppingService->productstatuses->custombatch($statusesRequest, $params);
        $simpleResponse = $googleStatuses->toSimpleObject();

        foreach ($simpleResponse->entries as $responseEntry) {
            if (isset($responseEntry['productStatus'])) {

                $googleExpirationDate = new \DateTime($responseEntry['productStatus']['googleExpirationDate']);

                $criticalDate = $googleExpirationDate->sub(new \DateInterval('P' . self::GOOGLE_EXPIRATION_CLOSE . 'D'));

                if ((new \DateTime()) > $criticalDate) {
                    $pseList[] = $pseCorresp[$responseEntry['batchId']];
                }
            }
        }

        return $pseList;
    }
}