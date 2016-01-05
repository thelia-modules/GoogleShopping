<?php


namespace GoogleShopping\Hook;

use GoogleShopping\GoogleShopping;
use Thelia\Core\Event\Hook\HookRenderBlockEvent;
use Thelia\Core\Hook\BaseHook;
use Thelia\Tools\URL;

class AdminToolHook extends BaseHook
{
    public function onMainTopMenuTools(HookRenderBlockEvent $event)
    {
        $event->add(array(
            "url" => URL::getInstance()->absoluteUrl("/admin/module/GoogleShopping?current_tab=management"),
            "title" => $this->trans("Google catalog management", [], GoogleShopping::DOMAIN_NAME)
        ));
    }
}
