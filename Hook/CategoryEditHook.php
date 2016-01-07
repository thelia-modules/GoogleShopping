<?php


namespace GoogleShopping\Hook;

use Thelia\Core\Event\Hook\HookRenderEvent;
use Thelia\Core\Hook\BaseHook;

class CategoryEditHook extends BaseHook
{
    public function onCategoryTabContent(HookRenderEvent $event)
    {
        $params['category_id'] = $event->getArgument('id');
        $event->add($this->render(
            'google-shopping/category/category-edit.html',
            $params
        ));
    }

    public function onCategoryEditJs(HookRenderEvent $event)
    {
        $event->add($this->render(
            'google-shopping/category/category-edit-js.html'
        ));
    }



}
