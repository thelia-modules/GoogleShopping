<?php
/**
 * Created by PhpStorm.
 * User: tompradat
 * Date: 10/11/2016
 * Time: 09:40
 */

namespace GoogleShopping\Service;


use GoogleShopping\Event\GoogleProductEvent;
use GoogleShopping\Event\GoogleShoppingEvents;
use GoogleShopping\GoogleShopping;
use GoogleShopping\Handler\GoogleShoppingHandler;
use GoogleShopping\Model\GoogleshoppingConfiguration;
use GoogleShopping\Model\GoogleshoppingConfigurationQuery;
use GoogleShopping\Model\GoogleshoppingProductSyncQueue;
use GoogleShopping\Model\GoogleshoppingProductSyncQueueQuery;
use GoogleShopping\Model\Map\GoogleshoppingConfigurationTableMap;
use GoogleShopping\Model\Map\GoogleshoppingProductSyncQueueTableMap;
use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\ActiveQuery\Join;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Thelia\Model\CountryQuery;
use Thelia\Model\CurrencyQuery;
use Thelia\Model\LangQuery;
use Thelia\Model\Map\CountryTableMap;
use Thelia\Model\Map\LangTableMap;
use Thelia\Model\Map\ProductCategoryTableMap;
use Thelia\Model\Map\ProductSaleElementsTableMap;
use Thelia\Model\ProductSaleElements;
use Thelia\Model\ProductSaleElementsQuery;

class CatalogService
{
    protected $container;

    /** @var RequestStack */
    protected $request;

    public function __construct(ContainerInterface $container, RequestStack $requestStack)
    {
        $this->container = $container;
        $this->request = $requestStack;
    }

    public function syncCatalog()
    {
        $syncSuccess = false;

        $googleShoppingHandler = (new GoogleShoppingHandler($this->container, $this->request));
        $client = $googleShoppingHandler->createGoogleClient();
        $googleShoppingService = new \Google_Service_ShoppingContent($client);

        //PRODUCTS IN QUEUE
        $syncQueue = ProductSaleElementsQuery::create()
            ->joinProduct();

        $googleSyncQueueJoin = new Join();
        $googleSyncQueueJoin->addExplicitCondition(
            ProductSaleElementsTableMap::TABLE_NAME,
            'ID',
            null,
            GoogleshoppingProductSyncQueueTableMap::TABLE_NAME,
            'PRODUCT_SALE_ELEMENTS_ID',
            'sync_queue'
        );
        $googleSyncQueueJoin->setJoinType(Criteria::JOIN);

        $productCategoryJoin = new Join();
        $productCategoryJoin->addExplicitCondition(
            ProductSaleElementsTableMap::TABLE_NAME,
            'PRODUCT_ID',
            null,
            ProductCategoryTableMap::TABLE_NAME,
            'PRODUCT_ID',
            null
        );
        $productCategoryJoin->setJoinType(Criteria::LEFT_JOIN);

        $syncQueue->addJoinObject($googleSyncQueueJoin, 'sync_queue_join')
            ->withColumn('sync_queue.created_at', 'sync_date')
            ->addJoinObject($productCategoryJoin, 'product_category_join')
            ->addJoinCondition('product_category_join', 'product_category.default_category = 1')
            ->withColumn('product_category.category_id', 'category_id')
            ->orderBy('sync_date')
            ->find();

        //CONFIGURATIONS
        $googleConfigurations = GoogleshoppingConfigurationQuery::create()
            ->filterByMerchantId(null, Criteria::ISNOTNULL)
            ->filterBySync(true);

        $langJoin = new Join();
        $langJoin->addExplicitCondition(
            GoogleshoppingConfigurationTableMap::TABLE_NAME,
            'LANG_ID',
            null,
            LangTableMap::TABLE_NAME,
            'ID',
            null
        );
        $langJoin->setJoinType(Criteria::LEFT_JOIN);

        $countryJoin = new Join();
        $countryJoin->addExplicitCondition(
            GoogleshoppingConfigurationTableMap::TABLE_NAME,
            'COUNTRY_ID',
            null,
            CountryTableMap::TABLE_NAME,
            'ID',
            null
        );
        $countryJoin->setJoinType(Criteria::LEFT_JOIN);


        $googleConfigurations->addJoinObject($langJoin, 'lang_join')
            ->withColumn('lang.code')
            ->addJoinObject($countryJoin, 'country_join')
            ->withColumn('country.isoalpha2')
            ->find();


        /** @var ProductSaleElements $productSaleElements */
        foreach ($syncQueue as $productSaleElements) {
            /** @var GoogleshoppingConfiguration $googleConfiguration */
            foreach ($googleConfigurations as $googleConfiguration) {
                $googleProductId = "online:" . $googleConfiguration->getVirtualColumn('langcode') . ":" . $googleConfiguration->getVirtualColumn('countryisoalpha2') . ":" . $productSaleElements->getId();
                $googleProduct = $googleShoppingHandler->getProduct($googleConfiguration->getMerchantId(),
                    $googleProductId);
                if (false !== $googleProduct) {
                    try {
                        $this->updateProduct($googleConfiguration, $productSaleElements);
                        $syncSuccess = true;
                    } catch (\Exception $e) {
                        GoogleShopping::log($e->getMessage());
                    }
                }
            }

            if ($syncSuccess) {
                $pseSyncs = GoogleshoppingProductSyncQueueQuery::create()
                    ->findByProductSaleElementsId($productSaleElements->getId());

                /** @var GoogleshoppingProductSyncQueue $pseSync */
                foreach ($pseSyncs as $pseSync) {
                    $pseSync->delete();
                }
            }
        }

        return $syncSuccess;
    }

    /**
     * @param ProductSaleElements $productSaleElements
     * @param GoogleshoppingConfiguration $googleConfiguration
     */
    protected function updateProduct($googleConfiguration, $productSaleElements)
    {
        $eventArgs = [];

        $eventArgs['lang'] = LangQuery::create()->findOneById($googleConfiguration->getLangId());
        $eventArgs['targetCountry'] = CountryQuery::create()->findOneById($googleConfiguration->getCountryId());

        $googleShoppingHandler = (new GoogleShoppingHandler($this->container, $this->request));

        //Init google client
        $client = $googleShoppingHandler->createGoogleClient();
        $googleShoppingService = new \Google_Service_ShoppingContent($client);

        $googleProductEvent = new GoogleProductEvent($productSaleElements->getProduct(), $productSaleElements, $googleShoppingService, $eventArgs);

        $googleProductEvent->setMerchantId($googleConfiguration->getMerchantId())
            ->setCurrency(CurrencyQuery::create()->findOneById($googleConfiguration->getCurrencyId()));

        $this->container->get('event_dispatcher')->dispatch($googleProductEvent, GoogleShoppingEvents::GOOGLE_PRODUCT_CREATE_PRODUCT);
    }
}
