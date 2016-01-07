<?php


namespace GoogleShopping\Controller\Admin;


use GoogleShopping\Model\GoogleshoppingTaxonomyQuery;
use Thelia\Core\Security\AccessManager;
use Thelia\Core\Security\Resource\AdminResources;

class CatalogController extends BaseGoogleShoppingController
{
    public function categoryManagementView($id, $langId)
    {
        if (null !== $response = $this->checkAuth(array(AdminResources::MODULE), 'GoogleShopping', AccessManager::VIEW)) {
            return $response;
        }

        $isAssociatedCategory =  GoogleshoppingTaxonomyQuery::create()
            ->filterByTheliaCategoryId($id)
            ->filterByLangId($langId)
            ->findOne();

        if (null === $isAssociatedCategory) {
            throw new \Exception("This category is not associated with a Google's one in this language");
        }

        return $this->render(
            "google-shopping/category-management",
            [
                "categoryId" => $id,
                "langId" => $langId
            ]
        );
    }
}