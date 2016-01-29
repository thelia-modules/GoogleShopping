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

class GoogleProductBatchEvent extends GoogleShoppingBaseEvent
{
    protected $method;

    protected $entries;

    public function __construct($merchantId)
    {
        $this->merchantId = $merchantId;
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
