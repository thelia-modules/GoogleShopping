<?php


namespace GoogleShopping\EventListener;

use GoogleShopping\Event\GoogleProductEvent;
use GoogleShopping\Event\GoogleShoppingEvents;
use GoogleShopping\GoogleShopping;
use GoogleShopping\Handler\GoogleShoppingHandler;
use GoogleShopping\Model\GoogleshoppingProductQuery;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Thelia\Core\Event\TheliaEvents;
use Thelia\Core\HttpFoundation\Request;
use Thelia\Model\AttributeAvQuery;
use Thelia\Model\ProductImageQuery;
use Thelia\Model\ProductPriceQuery;
use Thelia\TaxEngine\Calculator;

class GoogleProductEventListener implements EventSubscriberInterface
{
    /** @var  GoogleShoppingHandler */
    protected $googleShoppingHandler;

    /** @var Request */
    protected $request;

    public function __construct(GoogleShoppingHandler $googleShoppingHandler, Request $request)
    {
        $this->googleShoppingHandler = $googleShoppingHandler;
        $this->request = $request;
    }

    public function setGoogleProduct(GoogleProductEvent $event)
    {
        $googleProduct = new \Google_Service_ShoppingContent_Product();

        $itemGroupId = $event->getItemGroupId();
        $productSaleElements = $event->getProductSaleElements();
        $lang =  $event->getLang();
        //If we have multiple pse for one product manage attribute
        if ($itemGroupId !== null) {
            $googleProduct->setItemGroupId($itemGroupId);

            //Use pse associated image if exist instead of product image
            $pseImage = ProductImageQuery::create()
                ->useProductSaleElementsProductImageQuery()
                ->filterByProductSaleElementsId($productSaleElements->getId())
                ->endUse()
                ->findOne();
            if ($pseImage !== null) {
                $imagePseEvent = $this->googleShoppingHandler->createProductImageEvent($pseImage);
                $event->getDispatcher()->dispatch(TheliaEvents::IMAGE_PROCESS, $imagePseEvent);
                $event->setImagePath($imagePseEvent->getFileUrl());
            }

            $colorAttributeId = GoogleShopping::getConfigValue('attribute_color');
            $sizeAttributeId = GoogleShopping::getConfigValue('attribute_size');

            //set color of pse if exist
            if (null !== $colorAttributeId) {
                $colorCombination = AttributeAvQuery::create()
                    ->joinWithI18n($lang->getLocale())
                    ->useAttributeCombinationQuery()
                    ->filterByAttributeId($colorAttributeId)
                    ->filterByProductSaleElementsId($productSaleElements->getId())
                    ->endUse()
                    ->findOne();
                if (null !== $colorCombination) {
                    $googleProduct->setColor($colorCombination->getTitle());
                }
            }

            //set size of pse if exist
            if (null !== $sizeAttributeId) {
                $sizeCombination = AttributeAvQuery::create()
                    ->joinWithI18n($lang->getLocale())
                    ->useAttributeCombinationQuery()
                    ->filterByAttributeId($sizeAttributeId)
                    ->filterByProductSaleElementsId($productSaleElements->getId())
                    ->endUse()
                    ->findOne();
                if (null !== $sizeCombination) {
                    $googleProduct->setColor($sizeCombination->getTitle());
                }
            }
        }

        $product = $event->getProduct();
        $brand = $product->getBrand();
        $availability = $productSaleElements->getQuantity() > 0 ? 'in stock' : 'out of stock';

        if ($event->getIgnoreGtin() !== true) {
            if (null === $productSaleElements->getEanCode()) {
                throw new \Exception("Empty GTIN (EAN) code");
            }

            $checkGtin = $this->googleShoppingHandler->checkGtin($productSaleElements->getEanCode());

            if (false === $checkGtin) {
                throw new \Exception("Invalid GTIN (EAN) code : ".$productSaleElements->getEanCode());
            }
            $googleProduct->setGtin($productSaleElements->getEanCode()); //A valid GTIN code
        } else {
            $googleProduct->setIdentifierExists(false); //Product don't have GTIN
        }

        $googleProduct->setChannel('online');
        $googleProduct->setContentLanguage($lang->getCode()); //Lang of product
        $googleProduct->setOfferId($productSaleElements->getId()); //Unique identifier (one by pse)
        $googleProduct->setTargetCountry($event->getTargetCountry()->getIsoalpha2()); //Target country in ISO 3166
        $googleProduct->setBrand($brand->getTitle());
        $googleProduct->setGoogleProductCategory($event->getGoogleCategory()->getGoogleCategory()); //The associated google category from google taxonomy
        $googleProduct->setCondition('new'); // "new", "refurbished" or "used"
        $googleProduct->setLink($product->getUrl()); //Link to the product
        $googleProduct->setTitle($product->getTitle());
        $googleProduct->setAvailability($availability); //"in stock", "out of stock" or "preorder"
        $googleProduct->setDescription($product->getDescription());
        $googleProduct->setImageLink($event->getImagePath()); //Link to the product image
        $googleProduct->setProductType($product->getTitle()); //Product category in store

        //Set price
        $psePrice = ProductPriceQuery::create()->findOneByProductSaleElementsId($productSaleElements->getId());

        $taxCalculator = new Calculator();
        $taxCalculator->load($product, $event->getTargetCountry());

        $price = new \Google_Service_ShoppingContent_Price();
        $productPrice = $productSaleElements->getPromo() === 0 ? $psePrice->getPrice() : $psePrice->getPromoPrice();
        $price->setValue($taxCalculator->getTaxedPrice($productPrice));

        $price->setCurrency('EUR');

        //Delivery shippings
        $googleShippings = array();

        /**
         * @var string $shippingTitle
         * @var \Thelia\Model\OrderPostage $shippingCost
         */
        foreach ($event->getShippings() as $shippingTitle => $shippingCost) {
            $shipping_price = new \Google_Service_ShoppingContent_Price();
            $shipping_price->setValue($shippingCost->getAmount());
            $shipping_price->setCurrency('EUR');

            $googleShipping = new \Google_Service_ShoppingContent_ProductShipping();
            $googleShipping->setPrice($shipping_price);
            $googleShipping->setCountry($event->getTargetCountry()->getIsoalpha2());
            $googleShipping->setService($shippingTitle);

            $googleShippings[] = $googleShipping;
        }

        $googleProduct->setPrice($price);
        $googleProduct->setShipping($googleShippings);

        $event->setGoogleProduct($googleProduct);
        $event->getDispatcher()->dispatch(GoogleShoppingEvents::GOOGLE_PRODUCT_SEND, $event);
    }

    public function sendGoogleProduct(GoogleProductEvent $event)
    {
        $product = $event->getGoogleProduct();

        $service = $event->getGoogleShoppingService();

        $service->products->insert(GoogleShopping::getConfigValue('merchant_id'), $product);

        //Todo : stop record it into base get it directly from google
        //Save in database for prevent multiple add of same item (Google don't like it)
        GoogleshoppingProductQuery::create()
            ->filterByProductId($event->getProduct()->getId())
            ->findOneOrCreate()
            ->save();
    }

    public function deleteGoogleProduct(GoogleProductEvent $event)
    {
        $service = $event->getGoogleShoppingService();

        $service->products->delete(
            GoogleShopping::getConfigValue('merchant_id'),
            $event->getProductSaleElements()->getId()
        );

        //Todo : stop record it into base get it directly from google
        $googleShoppingProduct = GoogleshoppingProductQuery::create()
            ->findOneByProductId($event->getProduct()->getId());

        if ($googleShoppingProduct) {
            $googleShoppingProduct->delete();
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            GoogleShoppingEvents::GOOGLE_PRODUCT_ADD => ["setGoogleProduct", 128],
            GoogleShoppingEvents::GOOGLE_PRODUCT_SEND => ["sendGoogleProduct", 128],
            GoogleShoppingEvents::GOOGLE_PRODUCT_DELETE => ["deleteGoogleProduct", 128],
        ];
    }
}
