<?php


namespace GoogleShopping\Controller\Admin;


use Thelia\Core\Security\AccessManager;
use Thelia\Core\Security\Resource\AdminResources;

class CatalogController extends BaseGoogleShoppingController
{
    public function categoryManagementView($id)
    {
        if (null !== $response = $this->checkAuth(array(AdminResources::MODULE), 'GoogleShopping', AccessManager::VIEW)) {
            return $response;
        }

        return $this->render(
            "google-shopping/category-management",
            ["categoryId" => $id]
        );
    }
}