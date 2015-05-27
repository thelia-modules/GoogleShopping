<?php

namespace GoogleShopping\Controller\Admin;

use GoogleShopping\GoogleShopping;
use GoogleShopping\Model\GoogleshoppingTaxonomyQuery;
use Propel\Runtime\Collection\ObjectCollection;
use Thelia\Core\Event\Image\ImageEvent;
use Thelia\Core\Event\TheliaEvents;
use Thelia\Core\Security\AccessManager;
use Thelia\Core\Security\Resource\AdminResources;
use Thelia\Model\AttributeAvQuery;
use Thelia\Model\CategoryQuery;
use Thelia\Model\ConfigQuery;
use Thelia\Model\CountryQuery;
use Thelia\Model\Lang;
use Thelia\Model\LangQuery;
use Thelia\Model\Product;
use Thelia\Model\ProductImage;
use Thelia\Model\ProductImageQuery;
use Thelia\Model\ProductPriceQuery;
use Thelia\Model\ProductQuery;
use Thelia\Model\ProductSaleElements;
use Thelia\Model\ProductSaleElementsQuery;

class ProductController extends BaseGoogleShoppingController
{
    /**
     * @param $id
     * @return mixed
     */
    public function addProduct($id)
    {
        if (null !== $response = $this->checkAuth(array(AdminResources::MODULE), array('GoogleShopping'), AccessManager::DELETE)) {
            return $response;
        }

        $this->authorization();

        $locale = $this->getRequest()->get('locale');
        $lang = LangQuery::create()->findOneByLocale($locale);

        $theliaProduct = ProductQuery::create()
            ->joinWithI18n($locale)
            ->findOneById($id);

        $productSaleElements = ProductSaleElementsQuery::create()

            ->filterByProductId($theliaProduct->getId())
            ->find();

        $productImage = $theliaProduct->getProductImages()->getFirst();

        $imageEvent = $this->createImageEvent($productImage);

        $this->getDispatcher()->dispatch(TheliaEvents::IMAGE_PROCESS, $imageEvent);
        $imageAbsolutePath = $imageEvent->getFileUrl();

        $gprod = array();

        $itemGroupId = null;

        if ($productSaleElements->count() > 1) {
            $checkCombination = $this->checkCombination($productSaleElements);
            $itemGroupId =  $checkCombination === true ? $theliaProduct->getRef() : null;
        }

        if ($itemGroupId === null) {
            $pse = $productSaleElements->getFirst();
            $gprod[] = $this->insertPse($theliaProduct, $pse, $imageAbsolutePath, $lang);
        } else {
            /** @var ProductSaleElements $productSaleElement */
            foreach ($productSaleElements as $productSaleElement) {
                $gprod[] = $this->insertPse($theliaProduct, $productSaleElement, $imageAbsolutePath, $lang, $itemGroupId);
            }
        }
        var_dump($gprod);
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

    public function insertPse(Product $theliaProduct, ProductSaleElements $pse, $imageAbsolutePath, Lang $lang, $itemGroupId = null)
    {
        $product = new \Google_Service_ShoppingContent_Product();

        //If we have multiple pse for one product
        if ($itemGroupId !== null) {
            $product->setItemGroupId($itemGroupId);

            //Get pse associated image if exist
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
        $category =  CategoryQuery::create()
            ->findOneById($theliaProduct->getDefaultCategoryId());

        $googleCategory = GoogleshoppingTaxonomyQuery::create()
            ->findOneByTheliaCategoryId($category->getId());

        $availability = $pse->getQuantity() > 0 ? 'in stock' : 'out of stock';

        $product->setChannel('online');
        $product->setContentLanguage($lang->getCode());
        $product->setOfferId($pse->getRef());
        $product->setTargetCountry('FR');
        $product->setGtin($pse->getEanCode());
        $product->setBrand($brand->getTitle());
        $product->setGoogleProductCategory($googleCategory->getGoogleCategory());
        $product->setCondition('new');
        $product->setLink($theliaProduct->getUrl());
        $product->setTitle($theliaProduct->getTitle());
        $product->setAvailability($availability);
        $product->setDescription($theliaProduct->getDescription());
        $product->setImageLink($imageAbsolutePath);
        $product->setProductType($category->getTitle());


        $country = CountryQuery::create()
            ->findOneByIsoalpha2('FR');

        $psePrice = ProductPriceQuery::create()->findOneByProductSaleElementsId($pse->getId());

        $price = new \Google_Service_ShoppingContent_Price();
        $price->setValue($psePrice->getPrice());
        $price->setCurrency('EUR');

        $shipping_price = new \Google_Service_ShoppingContent_Price();
        $shipping_price->setValue(GoogleShopping::getConfigValue('shipping_price'));
        $shipping_price->setCurrency('EUR');

        $shipping = new \Google_Service_ShoppingContent_ProductShipping();
        $shipping->setPrice($shipping_price);
        $shipping->setCountry('FR');
        $shipping->setService('Standard shipping');

        $product->setPrice($price);
        $product->setShipping(array($shipping));

        $result = $this->service->products->insert(GoogleShopping::getConfigValue('merchant_id'), $product);
        return $result;
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
}