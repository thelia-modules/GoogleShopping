<?php

namespace GoogleShopping\Controller\Admin;

use GoogleShopping\Event\GoogleProductEvent;
use GoogleShopping\Event\GoogleShoppingEvents;
use GoogleShopping\GoogleShopping;
use GoogleShopping\Handler\GoogleShoppingHandler;
use GoogleShopping\Model\GoogleshoppingProductSynchronisationQuery;
use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\Collection\ObjectCollection;
use Thelia\Core\HttpFoundation\JsonResponse;
use Thelia\Core\Security\AccessManager;
use Thelia\Core\Security\Resource\AdminResources;
use Thelia\Model\AreaDeliveryModuleQuery;
use Thelia\Model\AttributeAvQuery;
use Thelia\Model\Country;
use Thelia\Model\CountryQuery;
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
        if ($this->getRequest()->query->get('target_country')) {
            $targetCountry = $this->getRequest()->get('target_country');
        } else {
            $targetCountry = Country::getDefaultCountry()->getIsoalpha2();
        }

        if ($this->getRequest()->query->get('locale')) {
            $lang = LangQuery::create()->findOneByLocale($this->getRequest()->query->get('locale'))->getCode();
        } else {
            $lang = Lang::getDefaultLanguage()->getCode();
        }

        $productSaleElements = ProductSaleElementsQuery::create()->findOneByProductId($id);

        $googleProductId = "online:".$lang.":".$targetCountry.":".$productSaleElements->getId();

        try {
            $googleShoppingHandler = (new GoogleShoppingHandler($this->container, $this->getRequest()));

            $client = $googleShoppingHandler->createGoogleClient();
            $googleShoppingService = new \Google_Service_ShoppingContent($client);
            $googleProduct = $googleShoppingService->products->get(GoogleShopping::getConfigValue('merchant_id'), $googleProductId);
            $response = ["id" => $googleProduct->getOfferId(), "identifier" => $googleProduct->getIdentifierExists()];
            return new JsonResponse($response);
        } catch (\Exception $e) {
            return new JsonResponse();
        }
    }

    public function addProduct($id)
    {
        if (null !== $response = $this->checkAuth(array(AdminResources::MODULE), array('GoogleShopping'), AccessManager::DELETE)) {
            return $response;
        }

        $eventArgs = [];
        //Get local and lang by admin config flag selected
        $locale = $this->getRequest()->get('locale');
        $eventArgs['ignoreGtin'] = $this->getRequest()->get('gtin') === "on" ? true : false;
        $eventArgs['lang'] = LangQuery::create()->findOneByLocale($locale);

        $eventArgs['targetCountry'] = Country::getDefaultCountry();

        if ($this->getRequest()->get('target_country')) {
            $eventArgs['targetCountry'] = CountryQuery::create()
                ->findOneByIsoalpha2($this->getRequest()->get('target_country'));
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
            ->joinWithI18n($locale)
            ->findOneById($id);

        try {
            /** @var ProductSaleElements $productSaleElement */
            $googleProductEvent = new GoogleProductEvent($theliaProduct, null, $googleShoppingService, $eventArgs);

            $this->getDispatcher()->dispatch(GoogleShoppingEvents::GOOGLE_PRODUCT_ADD_PRODUCT, $googleProductEvent);

            //Add auomatically product to sync
            $productSync = GoogleshoppingProductSynchronisationQuery::create()
                ->filterByProductId($theliaProduct->getId())
                ->filterByLang($eventArgs['lang']->getCode())
                ->filterByTargetCountry($eventArgs['targetCountry']->getIsoalpha2())
                ->findOneOrCreate();

            $productSync->setSyncEnable(true)
                ->save();

            return $this->generateRedirectFromRoute(
                "admin.module.configure",
                array(),
                array(
                    'module_code' => 'GoogleShopping',
                    'current_tab' => 'management',
                    'google_alert' => "success",
                    'google_message' =>
                        $this->getTranslator()->trans(
                            "Product added to Google with success",
                            [],
                            GoogleShopping::DOMAIN_NAME
                        )
                )
            );


        } catch (\Exception $e) {
            if (true == $this->getRequest()->get('ajax')) {
                return JsonResponse::create($e->getMessage(), 400);
            }
            return $this->generateRedirectFromRoute(
                "admin.module.configure",
                array(),
                array(
                    'module_code' => 'GoogleShopping',
                    'current_tab' => 'management',
                    'google_alert' => "error",
                    'google_message' =>
                        $this->getTranslator()->trans(
                            "Error on Google Shopping insertion : %message",
                            ['message' => $e->getMessage()],
                            GoogleShopping::DOMAIN_NAME
                        )
                )
            );
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

        //If the authorisation is not set yet or has expired
        if (false === $this->checkGoogleAuth()) {
            $this->getSession()->set('google_action_url', "/admin/module/googleshopping/delete/$id");
            return $this->generateRedirect('/googleshopping/oauth2callback');
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
        $productSaleElements = ProductSaleElementsQuery::create()
            ->findByProductId($id);

        $errors = [];

        foreach ($productSaleElements as $productSaleElement) {
            try {
                $googleProductEvent = new GoogleProductEvent($product, $productSaleElement, $googleShoppingService);
                $googleProductEvent->setTargetCountry($targetCountry);
                $googleProductEvent->setLang($lang);
                $this->getDispatcher()->dispatch(GoogleShoppingEvents::GOOGLE_PRODUCT_DELETE, $googleProductEvent);
            } catch (\Exception $e) {
                $errors[] = $productSaleElement->getId()." : ".$e->getMessage();
            }
        }

        if (!empty($errors)) {
            if (true == $this->getRequest()->get('ajax')) {
                return JsonResponse::create($errors, 400);
            }
            return $this->generateRedirectFromRoute(
                "admin.module.configure",
                array(),
                array(
                    'module_code' => 'GoogleShopping',
                    'current_tab' => 'management',
                    'google_alert' => "error",
                    'google_message' =>
                        $this->getTranslator()->trans(
                            "Error on deletion for these product sales elements : %message",
                            ['message' => implode(" , ", $errors)],
                            GoogleShopping::DOMAIN_NAME
                        )
                )
            );
        }
        return $this->generateRedirectFromRoute(
            "admin.module.configure",
            array(),
            array(
                'module_code' => 'GoogleShopping',
                'current_tab' => 'management',
                'google_alert' => "success",
                'google_message' =>
                    $this->getTranslator()->trans(
                        "Product deleted from Google with success",
                        [],
                        GoogleShopping::DOMAIN_NAME
                    )
            )
        );
    }

    public function toggleProductSync($id)
    {
        if (null !== $response = $this->checkAuth(array(AdminResources::MODULE), array('GoogleShopping'), AccessManager::DELETE)) {
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

    protected function checkCombination(ObjectCollection $productSaleElements)
    {
        $pse = $productSaleElements->getFirst();

        $colorAttributeId = GoogleShopping::getConfigValue('attribute_color');
        $sizeAttributeId = GoogleShopping::getConfigValue('attribute_size');

        $color = false;
        $size = false;

        if (null !== $colorAttributeId) {
            $colorCombination = AttributeAvQuery::create()
                ->useAttributeCombinationQuery()
                ->filterByAttributeId($colorAttributeId)
                ->filterByProductSaleElementsId($pse->getId())
                ->endUse()
                ->findOne();
            if (null !== $colorCombination) {
                $color = true;
            }
        }

        if (null !== $sizeAttributeId) {
            $sizeCombination = AttributeAvQuery::create()
                ->useAttributeCombinationQuery()
                ->filterByAttributeId($sizeAttributeId)
                ->filterByProductSaleElementsId($pse->getId())
                ->endUse()
                ->findOne();
            if (null !== $sizeCombination) {
                $size = true;
            }
        }

        if (true === $color || true === $size) {
            return true;
        }
        return false;
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
