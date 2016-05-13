<?php


namespace GoogleShopping\EventListener;

use GoogleShopping\GoogleShopping;
use GoogleShopping\Model\GoogleshoppingProductSyncQueueQuery;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Thelia\Core\Event\ProductSaleElement\ProductSaleElementUpdateEvent;
use Thelia\Core\Event\TheliaEvents;
use Thelia\Log\Tlog;

class ProductEventListener implements EventSubscriberInterface
{
    public function registerPseUpdate(ProductSaleElementUpdateEvent $event)
    {
        $pseId = $event->getProductSaleElementId();
        $pseSync = GoogleshoppingProductSyncQueueQuery::create()
            ->filterByProductSaleElementsId($pseId)
            ->findOneOrCreate();

        GoogleShopping::log("Change on pse $pseId detected -> placed in google sync queue!", Tlog::DEBUG);

        $pseSync->save();
    }

    public static function getSubscribedEvents()
    {
        return [
            TheliaEvents::PRODUCT_UPDATE_PRODUCT_SALE_ELEMENT => ["registerPseUpdate"]
        ];
    }
}
