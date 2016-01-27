<?php


namespace GoogleShopping\EventListener;

use GoogleShopping\Model\GoogleshoppingProductSyncQueueQuery;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Thelia\Core\Event\ProductSaleElement\ProductSaleElementUpdateEvent;
use Thelia\Core\Event\TheliaEvents;

class ProductEventListener implements EventSubscriberInterface
{
    public function registerPseUpdate(ProductSaleElementUpdateEvent $event)
    {
        $pseSync = GoogleshoppingProductSyncQueueQuery::create()
            ->filterByProductSaleElementsId($event->getProductSaleElementId())
            ->findOneOrCreate();

        $pseSync->save();
    }

    public static function getSubscribedEvents()
    {
        return [
            TheliaEvents::PRODUCT_UPDATE_PRODUCT_SALE_ELEMENT => ["registerPseUpdate"]
        ];
    }
}
