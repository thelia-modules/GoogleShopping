<?php

namespace GoogleShopping\Controller\Admin;

use GoogleShopping\GoogleShopping;
use Thelia\Controller\Admin\BaseAdminController;
use Thelia\Core\Security\AccessManager;
use Thelia\Core\Security\Resource\AdminResources;

set_include_path(get_include_path() . PATH_SEPARATOR . '/Users/vincent/Sites/thelia-dev/local/modules/GoogleShopping/Google/src');
require_once '/Users/vincent/Sites/thelia-dev/local/modules/GoogleShopping/Google/src/Google/autoload.php';
require_once '/Users/vincent/Sites/thelia-dev/local/modules/GoogleShopping/Google/src/Google/Client.php';

use Google_Client;

class ProductController extends BaseGoogleShoppingController
{
    public function addProduct($id)
    {
        if (null !== $response = $this->checkAuth(array(AdminResources::MODULE), array('GoogleShopping'), AccessManager::DELETE)) {
            return $response;
        }

        $this->authorization();

        $product = new \Google_Service_ShoppingContent_Product();
        $product->setOfferId("123");
        $product->setTitle("Testing product");
        $product->setDescription('A classic novel about the French Revolution');
        $product->setLink('http://my-book-shop.com/tale-of-two-cities.html');
        $product->setImageLink('http://my-book-shop.com/tale-of-two-cities.jpg');
        $product->setContentLanguage('en');
        $product->setTargetCountry('GB');
        $product->setChannel('online');
        $product->setAvailability('in stock');
        $product->setCondition('new');
        $product->setGoogleProductCategory('Media > Books');
        $product->setGtin('9780007350896');

        $price = new \Google_Service_ShoppingContent_Price();
        $price->setValue('2.50');
        $price->setCurrency('GBP');

        $shipping_price = new \Google_Service_ShoppingContent_Price();
        $shipping_price->setValue('0.99');
        $shipping_price->setCurrency('GBP');

        $shipping = new \Google_Service_ShoppingContent_ProductShipping();
        $shipping->setPrice($shipping_price);
        $shipping->setCountry('GB');
        $shipping->setService('Standard shipping');

        $shipping_weight = new \Google_Service_ShoppingContent_ProductShippingWeight();
        $shipping_weight->setValue(200);
        $shipping_weight->setUnit('grams');

        $product->setPrice($price);
        $product->setShipping(array($shipping));
        $product->setShippingWeight($shipping_weight);

        $result = $this->service->products->insert(GoogleShopping::getConfigValue('client_id'), $product);
    }
}