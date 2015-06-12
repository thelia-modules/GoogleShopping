<?php

namespace GoogleShopping\Controller\Admin;

use GoogleShopping\GoogleShopping;
use GoogleShopping\Model\GoogleshoppingProductQuery;
use GoogleShopping\Model\GoogleshoppingTaxonomy;
use GoogleShopping\Model\GoogleshoppingTaxonomyQuery;
use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\Collection\ObjectCollection;
use Thelia\Core\Event\Image\ImageEvent;
use Thelia\Core\Event\TheliaEvents;
use Thelia\Core\HttpFoundation\JsonResponse;
use Thelia\Core\Security\AccessManager;
use Thelia\Core\Security\Resource\AdminResources;
use Thelia\Log\Tlog;
use Thelia\Model\AreaDeliveryModuleQuery;
use Thelia\Model\AttributeAvQuery;
use Thelia\Model\Category;
use Thelia\Model\CategoryQuery;
use Thelia\Model\ConfigQuery;
use Thelia\Model\Country;
use Thelia\Model\CountryQuery;
use Thelia\Model\Lang;
use Thelia\Model\LangQuery;
use Thelia\Model\Module;
use Thelia\Model\ModuleQuery;
use Thelia\Model\OrderPostage;
use Thelia\Model\Product;
use Thelia\Model\ProductImage;
use Thelia\Model\ProductImageQuery;
use Thelia\Model\ProductPriceQuery;
use Thelia\Model\ProductQuery;
use Thelia\Model\ProductSaleElements;
use Thelia\Model\ProductSaleElementsQuery;
use Thelia\Module\BaseModule;
use Thelia\TaxEngine\Calculator;

class ProductController extends BaseGoogleShoppingController
{
    public function addProduct($id)
    {
        if (null !== $response = $this->checkAuth(array(AdminResources::MODULE), array('GoogleShopping'), AccessManager::DELETE)) {
            return $response;
        }

        //Get local and lang by admin config flag selected
        $locale = $this->getRequest()->get('locale');
        $passGtin = $this->getRequest()->get('gtin');
        $lang = LangQuery::create()->findOneByLocale($locale);

        if (false === $this->checkGoogleAuth()) {
            $this->getSession()->set('google_action_url', "/admin/module/googleshopping/add/$id?locale=$locale&gtin=$passGtin");
            return $this->generateRedirect('/googleshopping/oauth2callback');
        }

        $client = $this->createGoogleClient();
        $service = new \Google_Service_ShoppingContent($client);

        //Get target country in config TODO : Manage more than one country
        $targetCountryId = GoogleShopping::getConfigValue('target_country_id');
        $targetCountry = CountryQuery::create()->findOneById($targetCountryId);

        //Get the product, his category and his google associated category
        $theliaProduct = ProductQuery::create()
            ->joinWithI18n($locale)
            ->findOneById($id);
        $category =  CategoryQuery::create()
            ->findOneById($theliaProduct->getDefaultCategoryId());
        $googleCategory = GoogleshoppingTaxonomyQuery::create()
            ->findOneByTheliaCategoryId($category->getId());

        //Association is mandatory
        if (null === $googleCategory) {
            throw new \Exception("Category of product is not associated with a Google category, please fix it in module configurartion");
        }

        $productSaleElements = ProductSaleElementsQuery::create()
            ->filterByProductId($theliaProduct->getId())
            ->find();

        //Create link for image
        $productImage = $theliaProduct->getProductImages()->getFirst();
        $imageEvent = $this->createImageEvent($productImage);
        $this->getDispatcher()->dispatch(TheliaEvents::IMAGE_PROCESS, $imageEvent);
        $imageAbsolutePath = $imageEvent->getFileUrl();

        //If item don't have multiple pse he don't need an itemGroupId
        $itemGroupId = null;

        //Manage multiple pse for one product
        if ($productSaleElements->count() > 1) {
            $checkCombination = $this->checkCombination($productSaleElements);
            $itemGroupId =  $checkCombination === true ? $theliaProduct->getRef() : null;
        }

        //Find all shipping available and get hist postage
        $shippings = $this->getShippings($targetCountry);


        if ($itemGroupId === null) {
            $productSaleElements->getFirst();
        }

        $googleProducts = array();

        try {
            /** @var ProductSaleElements $productSaleElement */
            foreach ($productSaleElements as $productSaleElement) {
                $googleProducts[] = $this->insertPse($theliaProduct, $productSaleElement, $imageAbsolutePath, $lang, $category, $googleCategory, $targetCountry, $shippings, $passGtin, $itemGroupId);
            }

            foreach ($googleProducts as $googleProduct) {
                Tlog::getInstance()->info($service->products->insert(GoogleShopping::getConfigValue('merchant_id'), $googleProduct));
            }

            //Save in database for prevent multiple add of same item (Google don't like it)
            GoogleshoppingProductQuery::create()
                ->filterByProductId($theliaProduct->getId())
                ->findOneOrCreate()
                ->save();

            return $this->generateRedirectFromRoute(
                "admin.products.update",
                array(),
                array(
                    'product_id' => $theliaProduct->getId(),
                    'google_alert' => "success",
                    'error_message' => ""
                )
            );


        } catch (\Exception $e) {
            if (true == $this->getRequest()->get('ajax')) {
                return JsonResponse::create($e->getMessage(), 400);
            }
            return $this->generateRedirectFromRoute(
                "admin.products.update",
                array(),
                array(
                    'product_id' => $theliaProduct->getId(),
                    'google_alert' => "error",
                    'error_message' => "Error on Google Shopping insertion : ".$e->getMessage()
                )
            );
        }
    }

    //TODO : maybe manage this by event
    public function insertPse(
        Product $theliaProduct,
        ProductSaleElements $pse,
        $imageAbsolutePath,
        Lang $lang,
        Category $theliaCategory,
        GoogleshoppingTaxonomy $googleCategory,
        Country $targetCountry,
        $shippings,
        $passGtin,
        $itemGroupId = null
    ) {
        $product = new \Google_Service_ShoppingContent_Product();

        //If we have multiple pse for one product manage attribute
        if ($itemGroupId !== null) {
            $product->setItemGroupId($itemGroupId);

            //Use pse associated image if exist instead of product image
            $pseImage = ProductImageQuery::create()
                    ->useProductSaleElementsProductImageQuery()
                        ->filterByProductSaleElementsId($pse->getId())
                    ->endUse()
                ->findOne();
            if ($pseImage !== null) {
                $imagePseEvent = $this->createImageEvent($pseImage);
                $this->getDispatcher()->dispatch(TheliaEvents::IMAGE_PROCESS, $imagePseEvent);
                $imageAbsolutePath = $imagePseEvent->getFileUrl();
            }

            $colorAttributeId = GoogleShopping::getConfigValue('attribute_color');
            $sizeAttributeId = GoogleShopping::getConfigValue('attribute_size');

            //set color of pse if exist
            if (null !== $colorAttributeId) {
                $colorCombination = AttributeAvQuery::create()
                    ->joinWithI18n($lang->getLocale())
                    ->useAttributeCombinationQuery()
                    ->filterByAttributeId($colorAttributeId)
                    ->filterByProductSaleElementsId($pse->getId())
                    ->endUse()
                    ->findOne();
                if (null !== $colorCombination) {
                    $product->setColor($colorCombination->getTitle());
                }
            }

            //set size of pse if exist
            if (null !== $sizeAttributeId) {
                $sizeCombination = AttributeAvQuery::create()
                    ->joinWithI18n($lang->getLocale())
                    ->useAttributeCombinationQuery()
                    ->filterByAttributeId($sizeAttributeId)
                    ->filterByProductSaleElementsId($pse->getId())
                    ->endUse()
                    ->findOne();
                if (null !== $sizeCombination) {
                    $product->setColor($sizeCombination->getTitle());
                }
            }
        }


        $brand = $theliaProduct->getBrand();
        $availability = $pse->getQuantity() > 0 ? 'in stock' : 'out of stock';

        if ($passGtin !== "on") {
            if (null === $pse->getEanCode()) {
                throw new \Exception("Empty GTIN (EAN) code");
            }

            $checkGtin = $this->checkGtin($pse->getEanCode());

            if (false === $checkGtin) {
                throw new \Exception("Invalid GTIN (EAN) code : ".$pse->getEanCode());
            }
            $product->setGtin($pse->getEanCode()); //A valid GTIN code
        } else {
            $product->setIdentifierExists(false); //Product don't have GTIN
        }


        $product->setChannel('online');
        $product->setContentLanguage($lang->getCode()); //Lang of product
        $product->setOfferId($pse->getRef()); //Unique identifier (one by pse)
        $product->setTargetCountry($targetCountry->getIsoalpha2()); //Target country in ISO 3166
        $product->setBrand($brand->getTitle());
        $product->setGoogleProductCategory($googleCategory->getGoogleCategory()); //The associated google category from google taxonomy
        $product->setCondition('new'); // "new", "refurbished" or "used"
        $product->setLink($theliaProduct->getUrl()); //Link to the product
        $product->setTitle($theliaProduct->getTitle());
        $product->setAvailability($availability); //"in stock", "out of stock" or "preorder"
        $product->setDescription($theliaProduct->getDescription());
        $product->setImageLink($imageAbsolutePath); //Link to the product image
        $product->setProductType($theliaCategory->getTitle()); //Product category in store

        //Set pse price TODO : manage promo
        $psePrice = ProductPriceQuery::create()->findOneByProductSaleElementsId($pse->getId());
        $taxCalculator = new Calculator();
        $taxCalculator->load($theliaProduct, $targetCountry);
        $price = new \Google_Service_ShoppingContent_Price();
        $price->setValue($taxCalculator->getTaxedPrice($psePrice->getPrice()));
        $price->setCurrency('EUR');

        //Delivery shippings
        $googleShippings = array();

        /**
         * @var string $shippingTitle
         * @var \Thelia\Model\OrderPostage $shippingCost
         */
        foreach ($shippings as $shippingTitle => $shippingCost) {
            $shipping_price = new \Google_Service_ShoppingContent_Price();
            $shipping_price->setValue($shippingCost->getAmount());
            $shipping_price->setCurrency('EUR');

            $googleShipping = new \Google_Service_ShoppingContent_ProductShipping();
            $googleShipping->setPrice($shipping_price);
            $googleShipping->setCountry($targetCountry->getIsoalpha2());
            $googleShipping->setService($shippingTitle);

            $googleShippings[] = $googleShipping;
        }

        $product->setPrice($price);
        $product->setShipping($googleShippings);

        return $product;
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
                throw new \Exception('Target country not defined for GoogleShopping');
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

    /**
     * @param ProductImage $image
     * @return ImageEvent
     */
    protected function createImageEvent(ProductImage $image)
    {
        $imageEvent = new ImageEvent($this->getRequest());

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

    protected function checkGtin($gtin)
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
