<?php

namespace GoogleShopping\Controller\Admin;

use GoogleShopping\Event\GoogleProductEvent;
use GoogleShopping\Event\GoogleShoppingEvents;
use GoogleShopping\GoogleShopping;
use GoogleShopping\Handler\GoogleShoppingHandler;
use GoogleShopping\Model\GoogleshoppingAccountQuery;
use GoogleShopping\Model\GoogleshoppingProductSynchronisationQuery;
use GoogleShopping\Model\Map\GoogleshoppingProductSynchronisationTableMap;
use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\Collection\ObjectCollection;
use Propel\Runtime\Propel;
use Thelia\Core\HttpFoundation\JsonResponse;
use Thelia\Core\HttpFoundation\Response;
use Thelia\Core\Security\AccessManager;
use Thelia\Core\Security\Resource\AdminResources;
use Thelia\Model\AreaDeliveryModuleQuery;
use Thelia\Model\AttributeAvQuery;
use Thelia\Model\Country;
use Thelia\Model\CountryQuery;
use Thelia\Model\Currency;
use Thelia\Model\CurrencyQuery;
use Thelia\Model\Lang;
use Thelia\Model\LangQuery;
use Thelia\Model\Module;
use Thelia\Model\ModuleQuery;
use Thelia\Model\OrderPostage;
use Thelia\Model\ProductQuery;
use Thelia\Model\ProductSaleElements;
use Thelia\Model\ProductSaleElementsQuery;
use Thelia\Module\BaseModule;

class ProductController extends BaseGoogleShoppingController
{
    protected $googleShoppingHandler;

    public function getGoogleProduct($id)
    {
        $query = $this->getRequest()->query;
        $merchantId = $query->get('account');

        if ($query->get('target_country')) {
            $targetCountry = $query->get('target_country');
        } else {
            $targetCountry = Country::getDefaultCountry()->getIsoalpha2();
        }

        if ($query->get('lang')) {
            $lang = LangQuery::create()->findOneById($query->get('lang'))->getCode();
        } else {
            $lang = Lang::getDefaultLanguage()->getCode();
        }

        $productSaleElements = ProductSaleElementsQuery::create()->findOneByProductId($id);

        $googleProductId = "online:".$lang.":".$targetCountry.":".$productSaleElements->getId();

        try {
            $googleShoppingHandler = (new GoogleShoppingHandler($this->container, $this->getRequest()));

            $client = $googleShoppingHandler->createGoogleClient();
            $googleShoppingService = new \Google_Service_ShoppingContent($client);
            $googleProduct = $googleShoppingService->products->get($merchantId, $googleProductId);
            $response = ["id" => $googleProduct->getOfferId(), "identifier" => $googleProduct->getIdentifierExists()];
            return new JsonResponse($response);
        } catch (\Exception $e) {
            return new JsonResponse();
        }
    }

    public function addProduct($id)
    {
        if (null !== $response = $this->checkAuth(array(AdminResources::MODULE), array('GoogleShopping'), AccessManager::CREATE)) {
            return $response;
        }

        $request = $this->getRequest()->request;

        $con = Propel::getConnection(GoogleshoppingProductSynchronisationTableMap::DATABASE_NAME);
        $con->beginTransaction();

        try {

            $eventArgs = [];
            //Get local and lang by admin config flag selected
            $eventArgs['ignoreGtin'] = $request->get('gtin') === "on" ? true : false;
            $eventArgs['lang'] = LangQuery::create()->findOneById($request->get("lang"));
            $eventArgs['targetCountry'] = CountryQuery::create()->findOneById($request->get('country'));
            $merchantId = $request->get('account');
            $locale = $eventArgs['lang']->getLocale();
            $currencyId = $request->get('currency');

            $currency = CurrencyQuery::create()
                ->findOneById($currencyId);

            if (null === $currency) {
                $currency = Currency::getDefaultCurrency();
            }

            if (!$eventArgs['targetCountry']) {
                $eventArgs['targetCountry'] = Country::getDefaultCountry();
            }

            //If the authorisation is not set yet or has expired
            if (false === $this->checkGoogleAuth()) {
                $this->getSession()->set('google_action_url', "/admin/module/googleshopping/add/$id?locale=$locale&gtin=".$eventArgs['ignoreGtin']);
                return $this->generateRedirect('/googleshopping/oauth2callback');
            }


            $googleShoppingHandler = (new GoogleShoppingHandler($this->container, $this->getRequest()));

            //Init google client
            $client = $googleShoppingHandler->createGoogleClient();
            $googleShoppingService = new \Google_Service_ShoppingContent($client);

            //Get the product
            $theliaProduct = ProductQuery::create()
                ->joinWithI18n( $eventArgs['lang']->getLocale())
                ->findOneById($id);

            /** @var ProductSaleElements $productSaleElement */
            $googleProductEvent = new GoogleProductEvent($theliaProduct, null, $googleShoppingService, $eventArgs);
            $googleProductEvent->setMerchantId($merchantId)
                ->setCurrency($currency);

            $this->getDispatcher()->dispatch(GoogleShoppingEvents::GOOGLE_PRODUCT_ADD_PRODUCT, $googleProductEvent);

            $googleAccountId = GoogleshoppingAccountQuery::create()
                ->findOneByMerchantId($merchantId);

            //Add auomatically product to sync
            $productSync = GoogleshoppingProductSynchronisationQuery::create()
                ->filterByProductId($theliaProduct->getId())
                ->filterByLang($eventArgs['lang']->getCode())
                ->filterByTargetCountry($eventArgs['targetCountry']->getIsoalpha2())
                ->filterByGoogleshoppingAccountId($googleAccountId)
                ->findOneOrCreate();

            $productSync->setSyncEnable(true)
                ->save();

            $con->commit();

            return JsonResponse::create(json_encode(["message" => "Success"]), 200);

        } catch (\Exception $e) {
            $con->rollBack();
//            $deleteResponse = $this->deleteProduct($id);
            return JsonResponse::create($e->getMessage(), 500);
        }
    }

    public function deleteProduct($id)
    {
        if (null !== $response = $this->checkAuth(array(AdminResources::MODULE), array('GoogleShopping'), AccessManager::DELETE)) {
            return $response;
        }

        //Init google client
        $client = $this->createGoogleClient();
        $googleShoppingService = new \Google_Service_ShoppingContent($client);
        $merchantId = $this->getRequest()->request->get('account');

        //If the authorisation is not set yet or has expired
        if (false === $this->checkGoogleAuth()) {
            $this->getSession()->set('google_action_url', "/admin/module/googleshopping/delete/$id");
            return $this->generateRedirect('/googleshopping/oauth2callback');
        }

        if ($this->getRequest()->request->get('target_country')) {
            $targetCountry = CountryQuery::create()->findOneByIsoalpha2($this->getRequest()->get('target_country'));
        } else {
            $targetCountry = Country::getDefaultCountry();
        }

        if ($this->getRequest()->query->get('lang')) {
            $lang = LangQuery::create()->findOneByLocale($this->getRequest()->request->get('lang'));
        } else {
            $lang = Lang::getDefaultLanguage();
        }

        $product = ProductQuery::create()
            ->findPk($id);
        $productSaleElements = ProductSaleElementsQuery::create()
            ->findByProductId($id);

        $errors = [];

        foreach ($productSaleElements as $productSaleElement) {
            try {
                $googleProductEvent = new GoogleProductEvent($product, $productSaleElement, $googleShoppingService);
                $googleProductEvent->setTargetCountry($targetCountry);
                $googleProductEvent->setLang($lang);
                $googleProductEvent->setMerchantId($merchantId);
                $this->getDispatcher()->dispatch(GoogleShoppingEvents::GOOGLE_PRODUCT_DELETE, $googleProductEvent);
            } catch (\Exception $e) {
                $errors[] = $productSaleElement->getId()." : ".$e->getMessage();
            }
        }

        if (!empty($errors)) {
            return JsonResponse::create($errors, 400);
        }

        return JsonResponse::create("Success", 200);
    }

    public function toggleProductSync($id)
    {
        if (null !== $response = $this->checkAuth(array(AdminResources::MODULE), array('GoogleShopping'), AccessManager::UPDATE)) {
            return $response;
        }

        if ($this->getRequest()->query->get('target_country')) {
            $targetCountry = CountryQuery::create()->findOneByIsoalpha2($this->getRequest()->get('target_country'));
        } else {
            $targetCountry = Country::getDefaultCountry();
        }

        if ($this->getRequest()->query->get('locale')) {
            $lang = LangQuery::create()->findOneByLocale($this->getRequest()->query->get('locale'));
        } else {
            $lang = Lang::getDefaultLanguage();
        }

        $product = ProductQuery::create()
            ->findPk($id);

        $googleProductEvent = new GoogleProductEvent($product);
        $googleProductEvent->setTargetCountry($targetCountry)
            ->setLang($lang);

        $this->getDispatcher()->dispatch(GoogleShoppingEvents::GOOGLE_PRODUCT_TOGGLE_SYNC, $googleProductEvent);
    }

    protected function getShippings($country)
    {
        $search = ModuleQuery::create()
            ->filterByActivate(1)
            ->filterByType(BaseModule::DELIVERY_MODULE_TYPE, Criteria::EQUAL)
        ->find();

        if (null === $country) {
                throw new \Exception($this->getTranslator()->trans(
                    'Target country not defined for GoogleShopping',
                    [],
                    GoogleShopping::DOMAIN_NAME
                ));
        }

        $deliveries = array();

        /** @var Module $deliveryModule */
        foreach ($search as $deliveryModule) {
            $areaDeliveryModule = AreaDeliveryModuleQuery::create()
                ->findByCountryAndModule($country, $deliveryModule);

            if (null === $areaDeliveryModule) {
                continue;
            }

            $moduleInstance = $deliveryModule->getDeliveryModuleInstance($this->container);

            if ($moduleInstance->isValidDelivery($country)) {
                $postage = OrderPostage::loadFromPostage($moduleInstance->getPostage($country));
                $deliveries[$deliveryModule->getTitle()] = $postage;
            }
        }

        return $deliveries;
    }
}
