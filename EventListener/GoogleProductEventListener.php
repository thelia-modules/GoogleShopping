<?php


namespace GoogleShopping\EventListener;

use GoogleShopping\Event\GoogleProductEvent;
use GoogleShopping\Event\GoogleShoppingEvents;
use GoogleShopping\GoogleShopping;
use GoogleShopping\Handler\GoogleShoppingHandler;
use GoogleShopping\Model\GoogleshoppingProductSynchronisation;
use GoogleShopping\Model\GoogleshoppingProductSynchronisationQuery;
use GoogleShopping\Model\GoogleshoppingTaxonomyQuery;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Thelia\Core\Event\TheliaEvents;
use Thelia\Core\HttpFoundation\Request;
use Thelia\Core\Translation\Translator;
use Thelia\Model\AttributeAvQuery;
use Thelia\Model\CategoryQuery;
use Thelia\Model\CountryQuery;
use Thelia\Model\Currency;
use Thelia\Model\LangQuery;
use Thelia\Model\ProductImageQuery;
use Thelia\Model\ProductPriceQuery;
use Thelia\Model\ProductQuery;
use Thelia\Model\ProductSaleElements;
use Thelia\Model\ProductSaleElementsQuery;
use Thelia\TaxEngine\Calculator;

class GoogleProductEventListener implements EventSubscriberInterface
{
    /** @var  GoogleShoppingHandler */
    protected $googleShoppingHandler;

    /** @var Request */
    protected $request;

    /** @var Request */
    protected $translator;

    public function __construct(Request $request, Translator $translator, GoogleShoppingHandler $googleShoppingHandler)
    {
        $this->request = $request;
        $this->translator = $translator;
        $this->googleShoppingHandler = $googleShoppingHandler;
    }

    /**
     * Get the info from product to add and dispatch an event
     * for each product sale elements of this product to build their GoogleProduct
     * @param GoogleProductEvent $event
     * @throws \Exception if category association is missing
     */
    public function addGoogleProduct(GoogleProductEvent $event)
    {
        $product = $event->getProduct();

        $theliaCategory = CategoryQuery::create()
            ->findOneById($product->getDefaultCategoryId());

        //Try to get associated Google category either in flux locale or english (accepted for all flux language)
        $googleCategory = GoogleshoppingTaxonomyQuery::create()
            ->filterByLangId($event->getLang()->getId())
            ->findOneByTheliaCategoryId($theliaCategory->getId());
        //If category is not associated in flux try to take the english association
        if (null === $googleCategory) {
            $englishLang = LangQuery::create()->findOneByLocale('en_US');
            $googleCategory = GoogleshoppingTaxonomyQuery::create()
                ->filterByLangId($englishLang->getId())
                ->findOneByTheliaCategoryId($theliaCategory->getId());
        }

        //Association is mandatory
        if (null === $theliaCategory) {
            throw new \Exception(
                $this->translator->trans(
                    "Category of product is not associated with a Google category, please fix it in module configurartion",
                    [],
                    GoogleShopping::DOMAIN_NAME
                )
            );
        }

        //Set categories
        $event->setTheliaCategory($theliaCategory)
            ->setGoogleCategory($googleCategory);

        $productSaleElementss = ProductSaleElementsQuery::create()
            ->filterByProductId($product->getId())
            ->find();

        //Create link for image
        $productImage = $product->getProductImages()->getFirst();
        $imageEvent = $this->googleShoppingHandler->createProductImageEvent($productImage);
        $event->getDispatcher()->dispatch(TheliaEvents::IMAGE_PROCESS, $imageEvent);
        $imagePath = $imageEvent->getFileUrl();


        //If item don't have multiple pse he don't need an itemGroupId
        $itemGroupId = null;

        //Manage multiple pse for one product
        if ($productSaleElementss->count() > 1) {
            $checkCombination = $this->googleShoppingHandler->checkCombination($productSaleElementss);
            $itemGroupId = $checkCombination === true ? $product->getRef() : null;
        }

        //Set imagePath and itemGroupId
        $event->setImagePath($imagePath)
            ->setItemGroupId($itemGroupId);

        //Find all shipping available and get hist postage
        $shippings = $this->googleShoppingHandler->getShippings($event->getTargetCountry());
        $event->setShippings($shippings);

        if ($itemGroupId === null) {
            $productSaleElementss->getFirst();
        }

        //If a productSaleElements is passed to event only send him
        if ($event->getProductSaleElements() !== null) {
            $event->getDispatcher()->dispatch(GoogleShoppingEvents::GOOGLE_PRODUCT_ADD_PSE, $event);
        //Else dispatch all the pse of the product
        } else {
            /** @var ProductSaleElements $productSaleElement */
            foreach ($productSaleElementss as $productSaleElements) {
                $event->setProductSaleElements($productSaleElements);
                $event->getDispatcher()->dispatch(GoogleShoppingEvents::GOOGLE_PRODUCT_ADD_PSE, $event);
            }
        }
    }

    /**
     * Build the GoogleProduct object for the given product sale elements
     * and dispatch the event who send this product to Google
     * @param GoogleProductEvent $event
     * @throws \Exception if a GTIN is not valid
     */
    public function addGoogleProductFromPse(GoogleProductEvent $event)
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
                throw new \Exception(
                    $this->translator->trans(
                        "Empty GTIN (EAN) code for product : %title",
                        ['title' => $product->getTitle()],
                        GoogleShopping::DOMAIN_NAME
                    )
                );
            }

            $checkGtin = $this->googleShoppingHandler->checkGtin($productSaleElements->getEanCode());

            if (false === $checkGtin) {
                throw new \Exception(
                    $this->translator->trans(
                        "Invalid GTIN (EAN) code : %ean",
                        ['ean' => $productSaleElements->getEanCode()],
                        GoogleShopping::DOMAIN_NAME
                    )
                );
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

        $price->setCurrency(Currency::getDefaultCurrency()->getCode());

        //Delivery shippings
        $googleShippings = array();

        /**
         * @var string $shippingTitle
         * @var \Thelia\Model\OrderPostage $shippingCost
         */
        foreach ($event->getShippings() as $shippingTitle => $shippingCost) {
            $shipping_price = new \Google_Service_ShoppingContent_Price();
            $shipping_price->setValue($shippingCost->getAmount());
            $shipping_price->setCurrency(Currency::getDefaultCurrency()->getCode());

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

        $service->products->insert($event->getMerchantId(), $product);
    }

    public function deleteGoogleProduct(GoogleProductEvent $event)
    {
        $service = $event->getGoogleShoppingService();

        $googleProductId = "online:".$event->getLang()->getCode().":".$event->getTargetCountry()->getIsoalpha2().":".$event->getProductSaleElements()->getId();

        $service->products->delete(
            GoogleShopping::getConfigValue('merchant_id'),
            $googleProductId
        );
    }

    public function toggleProductSync(GoogleProductEvent $event)
    {
        $productSync = GoogleshoppingProductSynchronisationQuery::create()
            ->filterByProductId($event->getProduct()->getId())
            ->filterByLang($event->getLang()->getCode())
            ->filterByTargetCountry($event->getTargetCountry()->getIsoalpha2())
            ->findOneOrCreate();

        if ($productSync->getSyncEnable() === true) {
            $productSync->setSyncEnable(false);
        } else {
            $productSync->setSyncEnable(true);
        }
        $productSync->save();
    }

    public function syncCatalog(Event $event)
    {
        $syncEnableds = GoogleshoppingProductSynchronisationQuery::create()
            ->filterBySyncEnable(true)
            ->find();

        $client = $this->googleShoppingHandler->createGoogleClient();
        $googleShoppingService = new \Google_Service_ShoppingContent($client);

        if ($client->isAccessTokenExpired()) {
            $client->refreshToken(GoogleShopping::getConfigValue('oauth_refresh_token'));
            $newToken = $client->getAccessToken();
            $this->request->getSession()->set('oauth_access_token', $newToken);
        }

        /** @var GoogleshoppingProductSynchronisation $syncEnable */
        foreach ($syncEnableds as $syncEnabled) {
            $product = ProductQuery::create()->findOneById($syncEnabled->getProductId());

            $eventArgs['ignoreGtin'] = false;

            //Check if product has gtin
            $productSaleElements = ProductSaleElementsQuery::create()
                ->filterByProductId($product->getId())
                ->find();
            $firstPse = $productSaleElements->getFirst();
            if (null === $firstPse->getEanCode()) {
                $eventArgs['ignoreGtin'] = true;
            }

            $country = CountryQuery::create()->findOneByIsoalpha2($syncEnabled->getTargetCountry());
            $lang = LangQuery::create()->findOneByCode($syncEnabled->getLang());
            $eventArgs['targetCountry'] = $country;
            $eventArgs['lang'] = $lang;

            $googleProductEvent = new GoogleProductEvent($product, null, $googleShoppingService, $eventArgs);
            $event->getDispatcher()->dispatch(GoogleShoppingEvents::GOOGLE_PRODUCT_ADD_PRODUCT, $googleProductEvent);
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            GoogleShoppingEvents::GOOGLE_PRODUCT_ADD_PRODUCT => ["addGoogleProduct", 128],
            GoogleShoppingEvents::GOOGLE_PRODUCT_ADD_PSE => ["addGoogleProductFromPse", 128],
            GoogleShoppingEvents::GOOGLE_PRODUCT_SEND => ["sendGoogleProduct", 128],
            GoogleShoppingEvents::GOOGLE_PRODUCT_DELETE => ["deleteGoogleProduct", 128],
            GoogleShoppingEvents::GOOGLE_PRODUCT_TOGGLE_SYNC => ["toggleProductSync", 128],
            GoogleShoppingEvents::GOOGLE_SYNC_CATALOG => ["syncCatalog", 128],
        ];
    }
}
