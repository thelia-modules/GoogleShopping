<?php


namespace GoogleShopping\Controller\Admin;

use GoogleShopping\GoogleShopping;
use GoogleShopping\Handler\GoogleShoppingHandler;
use GoogleShopping\Model\GoogleshoppingConfiguration;
use GoogleShopping\Model\GoogleshoppingConfigurationQuery;
use GoogleShopping\Model\GoogleshoppingProductSyncQueue;
use GoogleShopping\Model\GoogleshoppingProductSyncQueueQuery;
use GoogleShopping\Model\GoogleshoppingTaxonomyQuery;
use GoogleShopping\Model\Map\GoogleshoppingConfigurationTableMap;
use GoogleShopping\Model\Map\GoogleshoppingProductSyncQueueTableMap;
use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\ActiveQuery\Join;
use Thelia\Core\Security\AccessManager;
use Thelia\Core\Security\Resource\AdminResources;
use Thelia\Model\CountryQuery;
use Thelia\Model\CurrencyQuery;
use Thelia\Model\Map\CountryTableMap;
use Thelia\Model\Map\LangTableMap;
use Thelia\Model\Map\ProductCategoryTableMap;
use Thelia\Model\Map\ProductSaleElementsTableMap;
use Thelia\Model\ProductPriceQuery;
use Thelia\Model\ProductSaleElements;
use Thelia\Model\ProductSaleElementsQuery;
use Thelia\TaxEngine\Calculator;
use Thelia\Tools\URL;

class CatalogController extends BaseGoogleShoppingController
{
    public function categoryManagementView($categoryId, $configurationId = null)
    {
        if (null !== $response = $this->checkAuth(array(AdminResources::MODULE), 'GoogleShopping', AccessManager::VIEW)) {
            return $response;
        }

        $configuration = GoogleshoppingConfigurationQuery::create()
            ->findOneById($configurationId);

        if (null === $configuration && null !== $this->getRequest()->get('lang_id')) {
            $configuration = GoogleshoppingConfigurationQuery::create()
                ->findOneByLangId($this->getRequest()->get('lang_id'));

            if (null === $configuration) {
                throw new \Exception("There is no google configuration with this language please add one before continue");
            }

            return $this->generateRedirect(URL::getInstance()->absoluteUrl("/admin/module/GoogleShopping/category/management/$categoryId/".$configuration->getId()));
        }


        if (null === $configuration) {
            $configuration = GoogleshoppingConfigurationQuery::create()
                ->findOneByIsDefault(true);
        }

        $isAssociatedCategory =  GoogleshoppingTaxonomyQuery::create()
            ->filterByTheliaCategoryId($categoryId)
            ->filterByLangId($configuration->getLangId())
            ->findOne();

        if (null === $isAssociatedCategory) {
            throw new \Exception("This category is not associated with a Google's one in this language");
        }

        $params = [
            "configId" => $configuration->getId(),
            "categoryId" => $categoryId,
            "langId" => $configuration->getLangId(),
            "countryId" => $configuration->getCountryId(),
            'currencyId' => $configuration->getCurrencyId(),
            'merchantId' => $configuration->getMerchantId()
        ];

        return $this->render(
            "google-shopping/category-management",
            $params
        );
    }

    public function syncCatalog($secret = null)
    {
        if (null !== $secret && GoogleShopping::getConfigValue("sync_secret") !== $secret) {
            return null;
        } elseif (null !== $response = $this->checkAuth(array(AdminResources::MODULE), array('GoogleShopping'), AccessManager::CREATE)) {
            return $response;
        }
        $syncSuccess = false;

        $googleShoppingHandler = (new GoogleShoppingHandler($this->container, $this->getRequest()));
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
                    $availability = $productSaleElements->getQuantity() > 0 ? GoogleShopping::GOOGLE_IN_STOCK : GoogleShopping::GOOGLE_OUT_OF_STOCK;
                    $country = CountryQuery::create()->findPk($googleConfiguration->getCountryId());
                    $currency = CurrencyQuery::create()->findPk($googleConfiguration->getCurrencyId());
                    //Set price
                    $psePrice = ProductPriceQuery::create()->findOneByProductSaleElementsId($productSaleElements->getId());

                    $taxCalculator = new Calculator();
                    $taxCalculator->load($productSaleElements->getProduct(), $country);

                    $price = new \Google_Service_ShoppingContent_Price();
                    $productPrice = $productSaleElements->getPromo() == 0 ? $psePrice->getPrice() : $psePrice->getPromoPrice();
                    $currencyProductPrice = $productPrice * $currency->getRate();
                    $price->setValue($taxCalculator->getTaxedPrice($currencyProductPrice));

                    $price->setCurrency($currency->getCode());

                    $googleProduct->setPrice($price);
                    $googleProduct->setAvailability($availability);
                    try {
                        $googleShoppingService->products->insert($googleConfiguration->getMerchantId(), $googleProduct);
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
    }
}