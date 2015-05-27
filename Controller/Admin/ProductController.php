<?php

namespace GoogleShopping\Controller\Admin;

use GoogleShopping\GoogleShopping;
use Thelia\Controller\Admin\BaseAdminController;
use Thelia\Core\Security\AccessManager;
use Thelia\Core\Security\Resource\AdminResources;

class ProductController extends BaseGoogleShoppingController
{
    public function addProduct($id)
    {
        if (null !== $response = $this->checkAuth(array(AdminResources::MODULE), array('GoogleShopping'), AccessManager::DELETE)) {
            return $response;
        }

        $this->authorization();

        $product = new \Google_Service_ShoppingContent_Product();
        $product->setChannel('online');
        $product->setContentLanguage('fr');
        $product->setOfferId('123');
        $product->setTargetCountry('FR');
        $product->setGtin('3001234567892');
        $product->setBrand('test');
        $product->setGoogleProductCategory('Animals & Pet Supplies > Live Animals');
        $product->setCondition('new');
        $product->setLink('http://gshopping.openstudio-lab.com/?view=product&locale=en_US&product_id=22');
        $product->setTitle('test');
        $product->setAvailability('in stock');

        $price = new \Google_Service_ShoppingContent_Price();
        $price->setValue('3');
        $price->setCurrency('EUR');

        $shipping_price = new \Google_Service_ShoppingContent_Price();
        $shipping_price->setValue('1');
        $shipping_price->setCurrency('EUR');

        $shipping = new \Google_Service_ShoppingContent_ProductShipping();
        $shipping->setPrice($shipping_price);
        $shipping->setCountry('FR');
        $shipping->setService('Standard shipping');

        $product->setPrice($price);
        $product->setShipping(array($shipping));

        $result = $this->service->products->insert(GoogleShopping::getConfigValue('merchant_id'), $product);
    }
}