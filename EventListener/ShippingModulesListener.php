<?php
/**
 * Created by PhpStorm.
 * User: tompradat
 * Date: 10/11/2016
 * Time: 15:41
 */

namespace GoogleShopping\EventListener;


use GoogleShopping\GoogleShopping;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Thelia\Core\Event\Loop\LoopExtendsParseResultsEvent;
use Thelia\Core\Event\TheliaEvents;


class ShippingModulesListener implements EventSubscriberInterface
{

    public static function getSubscribedEvents()
    {
        return [
            TheliaEvents::getLoopExtendsEvent(TheliaEvents::LOOP_EXTENDS_PARSE_RESULTS, 'googleshopping.shipping.modules') => ['addExcludeShippingModule']
        ];
    }

    public function addExcludeShippingModule(LoopExtendsParseResultsEvent $event)
    {
        $loopResult = $event->getLoopResult();

        foreach ($loopResult as $row) {
            $row->set('EXCLUDED', $this->isExcluded($row->get('CODE')));
        }
    }

    protected function isExcluded($code)
    {
        $excludedModules = explode(',', GoogleShopping::getConfigValue(GoogleShopping::GOOGLE_EXCLUDED_SHIPPING));

        return in_array($code, $excludedModules) ? true : false ;
    }
}