<?php


namespace GoogleShopping\Event;

use Thelia\Core\Event\ActionEvent;

class GoogleShoppingBaseEvent extends ActionEvent
{
    /** @var  string */
    protected $merchantId;

    /** @var  \Google_Service_ShoppingContent */
    protected $googleShoppingService;

    /**
     * @return string
     */
    public function getMerchantId()
    {
        return $this->merchantId;
    }

    /**
     * @param string $merchantId
     * @return GoogleShoppingBaseEvent
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
     * @return GoogleShoppingBaseEvent
     */
    public function setGoogleShoppingService($googleShoppingService)
    {
        $this->googleShoppingService = $googleShoppingService;
        return $this;
    }



}
