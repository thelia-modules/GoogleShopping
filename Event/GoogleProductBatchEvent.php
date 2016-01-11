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

class GoogleProductBatchEvent extends ActionEvent
{
    /** @var  string */
    protected $merchantId;

    /** @var  \Google_Service_ShoppingContent */
    protected $googleShoppingService;

    protected $method;

    protected $entries;

    public function __construct($merchantId)
    {
        $this->merchantId = $merchantId;
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
     * @return GoogleProductBatchEvent
     */
    public function setMerchantId($merchantId)
    {
        $this->merchantId = $merchantId;
        return $this;
    }

    /**
     * @return \Google_Service_ShoppingContent
     */
    public function getGoogleShoppingService()
    {
        return $this->googleShoppingService;
    }

    /**
     * @param \Google_Service_ShoppingContent $googleShoppingService
     * @return GoogleProductBatchEvent
     */
    public function setGoogleShoppingService($googleShoppingService)
    {
        $this->googleShoppingService = $googleShoppingService;
        return $this;
    }


    /**
     * @return Array
     */
    public function getEntries()
    {
        return $this->entries;
    }

    /**
     * @param Array $entries
     * @return GoogleProductBatchEvent
     */
    public function setEntries($entries)
    {
        $this->entries = $entries;
        return $this;
    }

    /**
     * @return \Google_Service_ShoppingContent_ProductsCustomBatchRequest
     */
    public function getCustomBatchRequest()
    {
        $customBatchRequest = new \Google_Service_ShoppingContent_ProductsCustomBatchRequest();
        $customBatchRequest->setEntries($this->entries);

        return $customBatchRequest;
    }

    public function addEntry($product, $productId)
    {
        $entry = new \Google_Service_ShoppingContent_ProductsCustomBatchRequestEntry();
        $entry->setBatchId($productId);
        $entry->setMerchantId($this->merchantId);
        $entry->setMethod($this->method);

        if ($this->method !== "insert") {
            $entry->setProductId($productId);
        }

        $entry->setProduct($product);

        $this->entries[] = $entry;
    }


}
