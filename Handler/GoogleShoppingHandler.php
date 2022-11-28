<?php


namespace GoogleShopping\Handler;

use GoogleShopping\GoogleShopping;
use GoogleShopping\Model\GoogleshoppingTaxonomyQuery;
use GoogleShopping\Model\Map\GoogleshoppingTaxonomyTableMap;
use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\ActiveQuery\Join;
use Propel\Runtime\Collection\ObjectCollection;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Thelia\Action\ProductSaleElement;
use Thelia\Core\Event\Image\ImageEvent;
use Thelia\Core\HttpFoundation\Request;
use Thelia\Core\Translation\Translator;
use Thelia\Model\AreaDeliveryModuleQuery;
use Thelia\Model\AttributeAvQuery;
use Thelia\Model\Cart;
use Thelia\Model\CategoryQuery;
use Thelia\Model\ConfigQuery;
use Thelia\Model\Country;
use Thelia\Model\CountryQuery;
use Thelia\Model\Lang;
use Thelia\Model\LangQuery;
use Thelia\Model\Map\ProductCategoryTableMap;
use Thelia\Model\Map\ProductSaleElementsTableMap;
use Thelia\Model\Module;
use Thelia\Model\ModuleQuery;
use Thelia\Model\OrderPostage;
use Thelia\Model\Product;
use Thelia\Model\ProductImage;
use Thelia\Model\ProductSaleElements;
use Thelia\Model\ProductSaleElementsQuery;
use Thelia\Module\BaseModule;
use Thelia\Tools\URL;

class GoogleShoppingHandler
{
    /** @var  Request */
    protected $request;

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @param ContainerInterface $container
     * @param RequestStack $requestStack
     */
    public function __construct(ContainerInterface $container, RequestStack $requestStack)
    {
        $this->container = $container;
        $this->request = $requestStack->getMainRequest();
    }

    public function checkGoogleAuth()
    {
        $token = GoogleShopping::getConfigValue('oauth_access_token');

        if (!$token) {
            return false;
        }

        $client = new \Google_Client();
        $client->setApplicationName(GoogleShopping::getConfigValue('application_name'));
        $client->setClientId(GoogleShopping::getConfigValue('client_id'));
        $client->setClientSecret(GoogleShopping::getConfigValue('client_secret'));
        $client->setRedirectUri(URL::getInstance()->absoluteUrl('/googleshopping/oauth2callback'));
        $client->setScopes('https://www.googleapis.com/auth/content');

        $client->setAccessToken($token);

        if (true === $client->isAccessTokenExpired()) {
            return false;
        }

        return true;
    }

    public function createGoogleClient()
    {
        $client = new \Google_Client();
        $client->setApplicationName(GoogleShopping::getConfigValue('application_name'));
        $client->setClientId(GoogleShopping::getConfigValue('client_id'));
        $client->setClientSecret(GoogleShopping::getConfigValue('client_secret'));
        $client->setRedirectUri(URL::getInstance()->absoluteUrl('/googleshopping/oauth2callback'));
        $client->setScopes('https://www.googleapis.com/auth/content');
        $client->setAccessToken(GoogleShopping::getConfigValue('oauth_access_token'));

        return $client;
    }

    public function getProduct($merchantId, $googleProductId)
    {
        $client = $this->createGoogleClient();
        $googleShoppingService = new \Google_Service_ShoppingContent($client);

        try {
            $googleProduct = $googleShoppingService->products->get($merchantId, $googleProductId);
            return $googleProduct;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function getGoogleCategory($langId, $theliaCategoryId)
    {
        $googleCategory = GoogleshoppingTaxonomyQuery::create()
            ->filterByLangId($langId)
            ->findOneByTheliaCategoryId($theliaCategoryId);
        //If category is not associated in flux try to take the english association
        if (null === $googleCategory) {
            $englishLang = LangQuery::create()->findOneByLocale('en_US');
            $googleCategory = GoogleshoppingTaxonomyQuery::create()
                ->filterByLangId($englishLang->getId())
                ->findOneByTheliaCategoryId($theliaCategoryId);
        }

        return $googleCategory;
    }

    public function checkCombination(ObjectCollection $productSaleElements)
    {
        $pse = $productSaleElements->getFirst();

        $colorAttributeId = GoogleShopping::getConfigValue('attribute_color');
        $sizeAttributeId = GoogleShopping::getConfigValue('attribute_size');

        $color = false;
        $size = false;

        if (null !== $colorAttributeId) {
            $colorCombination = AttributeAvQuery::create()
                ->useAttributeCombinationQuery()
                    ->filterByAttributeId(explode(',', $colorAttributeId), Criteria::IN)
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
                    ->filterByAttributeId(explode(',', $sizeAttributeId), Criteria::IN)
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

    public function getShippings($country, EventDispatcherInterface $dispatcher)
    {
        $translator = Translator::getInstance();

        $search = ModuleQuery::create()
            ->filterByActivate(1)
            ->filterByType(BaseModule::DELIVERY_MODULE_TYPE, Criteria::EQUAL)
            ->filterByCode(explode(',', GoogleShopping::getConfigValue(GoogleShopping::GOOGLE_EXCLUDED_SHIPPING)), Criteria::NOT_IN)
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

            // Make sure the session has a cart, if it does not "getSessionCart" will create one
            $this->request->getSession()->getSessionCart($dispatcher);

            $moduleInstance = $deliveryModule->getDeliveryModuleInstance($this->container);

            if ($moduleInstance->isValidDelivery($country)) {
                $postage = OrderPostage::loadFromPostage($moduleInstance->getPostage($country));
                $deliveries[$deliveryModule->getTitle()] = $postage;
            }
        }

        return $deliveries;
    }

    /**
     * @param ProductImage $image
     * @return ImageEvent
     */
    public function createProductImageEvent(ProductImage $image)
    {
        $imageEvent = new ImageEvent($this->request);

        $baseSourceFilePath = ConfigQuery::read('images_library_path');

        if ($baseSourceFilePath === null) {
            $baseSourceFilePath = THELIA_LOCAL_DIR . 'media' . DS . 'images';
        } else {
            $baseSourceFilePath = THELIA_ROOT . $baseSourceFilePath;
        }

        // Put source image file path
        $sourceFilePath = sprintf(
            '%s/%s/%s',
            $baseSourceFilePath,
            'product',
            $image->getFile()
        );

        $imageEvent->setSourceFilepath($sourceFilePath);
        $imageEvent->setCacheSubdirectory('product');

        return $imageEvent;
    }

    public function checkGtin($gtin)
    {
        $cleanedCode = $this->clean($gtin);

        return $this->isValidGtin($cleanedCode);
    }

    protected function clean($gtin, $fill = 14)
    {
        if (is_numeric($gtin)) {
            return $this->zfill($gtin, $fill);
        } elseif (is_string($gtin)) {
            return $this->zfill(trim(str_replace("-", "", $gtin)), $fill);
        }
        return false;
    }

    protected function isValidGtin($cleanedCode)
    {
        if (!is_numeric($cleanedCode)) {
            return false;
        } elseif (!in_array(strlen($cleanedCode), array(8, 12, 13, 14, 18))) {
            return false;
        }

        return $this->isGtinChecksumValid($cleanedCode);
    }

    protected function isGtinChecksumValid($code)
    {
        $lastPart = substr($code, -1);
        $checkSum = $this->gtinCheckSum(substr($code, 0, strlen($code)-1));
        return $lastPart == $checkSum;
    }

    protected function gtinCheckSum($code)
    {
        $total = 0;

        $codeArray = str_split($code);
        foreach (array_values($codeArray) as $i => $c) {
            if ($i % 2 == 1) {
                $total = $total + $c;
            } else {
                $total = $total + (3*$c);
            }
        }
        $checkDigit = (10 - ($total % 10)) % 10;
        return $checkDigit;
    }

    protected function zfill($int, $cnt)
    {
        $int = intval($int);
        $nulls = "";
        for ($i=0; $i<($cnt-strlen($int)); $i++) {
            $nulls .= '0';
        }
        return $nulls.$int;
    }

}
