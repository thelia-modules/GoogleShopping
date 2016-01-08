<?php


namespace GoogleShopping\Event;

use GoogleShopping\Model\GoogleshoppingTaxonomy;
use Thelia\Core\Event\ActionEvent;
use Thelia\Model\Currency;
use Thelia\Model\Product;
use Thelia\Model\Category;
use Thelia\Model\Country;
use Thelia\Model\Lang;
use Thelia\Model\ProductSaleElements;

class GoogleProductEvent extends ActionEvent
{
    /** @var  string */
    protected $merchantId;

    /** @var  Product */
    protected $product;

    /** @var  \Google_Service_ShoppingContent_Product */
    protected $googleProduct;

    /** @var  ProductSaleElements */
    protected $productSaleElements;

    /** @var  \Google_Service_ShoppingContent */
    protected $googleShoppingService;

    /** @var  string */
    protected $imagePath;

    /** @var  Lang */
    protected $lang;

    /** @var  Category */
    protected $theliaCategory;

    /** @var  GoogleshoppingTaxonomy */
    protected $googleCategory;

    /** @var  Country */
    protected $targetCountry;

    /** @var  array */
    protected $shippings;

    /** @var  bool */
    protected $ignoreGtin;

    /** @var  int */
    protected $itemGroupId;

    /** @var  Currency */
    protected $currency;

    public function __construct(Product $product, ProductSaleElements $productSaleElements = null, \Google_Service_ShoppingContent $googleShoppingService = null, $array = array())
    {
        $this->product = $product;

        if ($productSaleElements) {
            $this->productSaleElements = $productSaleElements;
        }

        $this->googleShoppingService = $googleShoppingService;

        if (is_array($array)) {
            foreach ($array as $key => $value) {
                $this->$key = $value;
            }

        } else {
            throw new \Exception("Invalid parameter. The parameter must be an array");
        }
    }

    /**
     * @return Product
     */
    public function getProduct()
    {
        return $this->product;
    }

    public function getGoogleProduct()
    {
        return $this->googleProduct;
    }

    public function setGoogleProduct(\Google_Service_ShoppingContent_Product $googleProduct)
    {
        $this->googleProduct = $googleProduct;
        return $this;
    }

    public function getProductSaleElements()
    {
        return $this->productSaleElements;
    }

    public function getGoogleShoppingService()
    {
        return $this->googleShoppingService;
    }

    public function setGoogleShoppingService($googleShoppingService)
    {
        $this->googleShoppingService = $googleShoppingService;
        return $this;
    }

    public function setProductSaleElements(ProductSaleElements $productSaleElements)
    {
        $this->productSaleElements = $productSaleElements;
        return $this;
    }

    public function getImagePath()
    {
        return $this->imagePath;
    }

    public function setImagePath($imagePath)
    {
        $this->imagePath = $imagePath;
        return $this;
    }

    public function getLang()
    {
        return $this->lang;
    }

    public function setLang(Lang $lang)
    {
        $this->lang = $lang;
        return $this;
    }

    public function getTheliaCategory()
    {
        return $this->theliaCategory;
    }

    public function setTheliaCategory(Category $theliaCategory)
    {
        $this->theliaCategory = $theliaCategory;
        return $this;
    }

    public function getGoogleCategory()
    {
        return $this->googleCategory;
    }

    public function setGoogleCategory($googleCategory)
    {
        $this->googleCategory = $googleCategory;
        return $this;
    }

    public function getTargetCountry()
    {
        return $this->targetCountry;
    }

    public function setTargetCountry(Country $targetCountry)
    {
        $this->targetCountry = $targetCountry;
        return $this;
    }

    public function getShippings()
    {
        return $this->shippings;
    }

    public function setShippings($shipping)
    {
        $this->shippings = $shipping;
        return $this;
    }

    public function getIgnoreGtin()
    {
        return $this->ignoreGtin;
    }

    public function setIgnoreGtin($ignoreGtin)
    {
        $this->ignoreGtin = $ignoreGtin;
        return $this;
    }

    public function getItemGroupId()
    {
        return $this->itemGroupId;
    }

    public function setItemGroupId($itemGroupId)
    {
        $this->itemGroupId = $itemGroupId;
        return $this;
    }

    /**
     * @return string
     */
    public function getMerchantId()
    {
        return $this->merchantId;
    }

    /**
     * @param string $merchantId
     * @return GoogleProductEvent
     */
    public function setMerchantId($merchantId)
    {
        $this->merchantId = $merchantId;
        return $this;
    }

    /**
     * @return Currency
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * @param Currency $currency
     * @return GoogleProductEvent
     */
    public function setCurrency($currency)
    {
        $this->currency = $currency;
        return $this;
    }


}
