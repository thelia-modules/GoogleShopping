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
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Thelia\Core\HttpFoundation\JsonResponse;
use Thelia\Core\HttpFoundation\Request;
use Thelia\Core\HttpFoundation\Response;
use Thelia\Core\HttpFoundation\Session\Session;
use Thelia\Core\Security\AccessManager;
use Thelia\Core\Security\Resource\AdminResources;
use Thelia\Core\Translation\Translator;
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
use Thelia\Model\Product;
use Thelia\Model\ProductQuery;
use Thelia\Model\ProductSaleElements;
use Thelia\Model\ProductSaleElementsQuery;
use Thelia\Module\BaseModule;

class ProductController extends BaseGoogleShoppingController
{
    protected $googleShoppingHandler;

    public function statusBatch($merchantId, $categoryId, $langId, $targetCountryId, RequestStack $requestStack)
    {
        $products = ProductQuery::create()
                ->useProductCategoryQuery()
                    ->filterByCategoryId($categoryId)
                    ->filterByDefaultCategory(true)
                ->endUse()
            ->find();

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

        /** @var Product $product */
        foreach ($products as $product) {
            $i++;
            $pse = $product->getDefaultSaleElements();

            $googleProductId = "online:".$langCode.":".$isoAlpha2.":".$pse->getId();

            $entry =  [
                'batchId' => $product->getId(),
                'merchantId' => $merchantId,
                'method' => 'get',
                'productId' => $googleProductId
            ];

            $entries[] = $entry;
        }

        $googleShoppingHandler = new GoogleShoppingHandler($this->container, $requestStack);
        $client = $googleShoppingHandler->createGoogleClient();
        $googleShoppingService = new \Google_Service_ShoppingContent($client);

        $statusesRequest = new \Google_Service_ShoppingContent_ProductstatusesCustomBatchRequest();
        $statusesRequest->setEntries($entries);

        $params['fields'] = 'entries(batchId, productStatus/destinationStatuses/approvalStatus)';
        $googleStatuses = $googleShoppingService->productstatuses->custombatch($statusesRequest, $params);
        $simpleResponse = $googleStatuses->toSimpleObject();

        $disapprovedEntries = [];
        $approvedEntries = [];

        foreach ($simpleResponse->entries as $responseEntry) {
            if (isset($responseEntry['productStatus'])) {
                $isApproved = true;
                foreach ($responseEntry['productStatus']['destinationStatuses'] as $destinationStatus) {
                    if ($destinationStatus['approvalStatus'] === 'disapproved') {
                        $isApproved = false;
                    }
                }

                if ($isApproved === true) {
                    $approvedEntries[] = $responseEntry['batchId'];
                } else {
                    $disapprovedEntries[] = $responseEntry['batchId'];
                }
            }
        }

        $responseArray = [
            "approvedProducts" => $approvedEntries,
            "disapprovedProducts" => $disapprovedEntries
        ];

        return new JsonResponse($responseArray);
    }

    public function addProduct($id, Request $request, RequestStack $requestStack, EventDispatcherInterface $dispatcher)
    {
        if (null !== $response = $this->checkAuth(array(AdminResources::MODULE), array('GoogleShopping'), AccessManager::CREATE)) {
            return $response;
        }

        $request = $request->request;

        try {

            $eventArgs = [];
            //Get local and lang by admin config flag selected
            $eventArgs['ignoreGtin'] = $request->get('gtin') === "on";
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


            $googleShoppingHandler = (new GoogleShoppingHandler($this->container, $requestStack));

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

            $dispatcher->dispatch($googleProductEvent, GoogleShoppingEvents::GOOGLE_PRODUCT_CREATE_PRODUCT);

            return JsonResponse::create(["message" => "Success"], 200);

        } catch (\Exception $e) {
            return JsonResponse::create($e->getMessage(), 500);
        }
    }

    public function deleteProduct($id, Request $request, Session $session, RequestStack $requestStack, EventDispatcherInterface $dispatcher)
    {
        if (null !== $response = $this->checkAuth(array(AdminResources::MODULE), array('GoogleShopping'), AccessManager::DELETE)) {
            return $response;
        }

        try {
            $request = $request->request;

            $eventArgs = [];
            //Get local and lang by admin config flag selected
            $eventArgs['lang'] = LangQuery::create()->findOneById($request->get("lang"));
            $eventArgs['targetCountry'] = CountryQuery::create()->findOneById($request->get('country'));
            $merchantId = $request->get('account');
            $locale = $eventArgs['lang']->getLocale();

            if (!$eventArgs['targetCountry']) {
                $eventArgs['targetCountry'] = Country::getDefaultCountry();
            }

            //If the authorisation is not set yet or has expired
            if (false === $this->checkGoogleAuth()) {
                $$session->set('google_action_url', "/admin/module/googleshopping/delete/$id?locale=$locale&gtin=".$eventArgs['ignoreGtin']);
                return $this->generateRedirect('/googleshopping/oauth2callback');
            }

            $googleShoppingHandler = (new GoogleShoppingHandler($this->container, $requestStack));

            //Init google client
            $client = $googleShoppingHandler->createGoogleClient();
            $googleShoppingService = new \Google_Service_ShoppingContent($client);

            //Get the product
            $theliaProduct = ProductQuery::create()
                ->joinWithI18n($eventArgs['lang']->getLocale())
                ->findOneById($id);

            /** @var ProductSaleElements $productSaleElement */
            $googleProductEvent = new GoogleProductEvent($theliaProduct, null, $googleShoppingService, $eventArgs);
            $googleProductEvent->setMerchantId($merchantId);

            $dispatcher->dispatch($googleProductEvent, GoogleShoppingEvents::GOOGLE_PRODUCT_DELETE_PRODUCT);
            return JsonResponse::create(["message" => "Success"], 200);

        } catch (\Exception $e) {
            return JsonResponse::create($e->getMessage(), 500);
        }
    }

    protected function getShippings($country, Translator $translator)
    {
        $search = ModuleQuery::create()
            ->filterByActivate(1)
            ->filterByType(BaseModule::DELIVERY_MODULE_TYPE, Criteria::EQUAL)
        ->find();

        if (null === $country) {
                throw new \Exception($translator->trans(
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
