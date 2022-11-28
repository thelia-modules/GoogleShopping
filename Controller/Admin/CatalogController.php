<?php


namespace GoogleShopping\Controller\Admin;

use GoogleShopping\GoogleShopping;
use GoogleShopping\Model\GoogleshoppingConfigurationQuery;
use GoogleShopping\Model\GoogleshoppingTaxonomyQuery;
use GoogleShopping\Service\CatalogService;
use Thelia\Core\HttpFoundation\Request;
use Thelia\Core\HttpFoundation\Response;
use Thelia\Core\Security\AccessManager;
use Thelia\Core\Security\Resource\AdminResources;
use Thelia\Tools\URL;

class CatalogController extends BaseGoogleShoppingController
{
    /**
     * @param $categoryId
     * @param Request $request
     * @return mixed|string|\Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response|Response|null
     * @throws \Exception
     */
    public function categoryManagementViewCategory($categoryId, Request $request)
    {
        return $this->categoryManagementView($categoryId, null, $request);
    }

    /**
     * @param $categoryId
     * @param $configurationId
     * @param Request $request
     * @return mixed|string|\Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response|Response|null
     * @throws \Exception
     */
    public function categoryManagementView($categoryId, $configurationId, Request $request)
    {
        if (null !== $response = $this->checkAuth(array(AdminResources::MODULE), 'GoogleShopping', AccessManager::VIEW)) {
            return $response;
        }

        $configuration = GoogleshoppingConfigurationQuery::create()
            ->findOneById($configurationId);

        if (null === $configuration && null !== $request->get('lang_id')) {
            $configuration = GoogleshoppingConfigurationQuery::create()
                ->findOneByLangId($request->get('lang_id'));

            if (null === $configuration) {
                throw new \Exception("There is no google configuration with this language please add one before continue");
            }

            return $this->generateRedirect(URL::getInstance()->absoluteUrl("/admin/module/GoogleShopping/category/management/$categoryId/".$configuration->getId()));
        }


        if (null === $configuration) {
            $configuration = GoogleshoppingConfigurationQuery::create()
                ->findOneByIsDefault(true);
        }

        $isAssociatedCategory =  GoogleshoppingTaxonomyQuery::create()
            ->filterByTheliaCategoryId($categoryId)
            ->filterByLangId($configuration->getLangId())
            ->findOne();

        if (null === $isAssociatedCategory) {
            throw new \Exception("This category is not associated with a Google's one in this language");
        }

        $params = [
            "configId" => $configuration->getId(),
            "categoryId" => $categoryId,
            "langId" => $configuration->getLangId(),
            "countryId" => $configuration->getCountryId(),
            'currencyId' => $configuration->getCurrencyId(),
            'merchantId' => $configuration->getMerchantId()
        ];

        return $this->render(
            "google-shopping/category-management",
            $params
        );
    }

    public function syncCatalog($secret = null, CatalogService $catalogService)
    {
        if (null !== $secret && GoogleShopping::getConfigValue("sync_secret") !== $secret) {
            return null;
        }

        if (null !== $response = $this->checkAuth(array(AdminResources::MODULE), array('GoogleShopping'), AccessManager::CREATE)) {
            return $response;
        }

        $catalogService->syncCatalog();

        return new Response();
    }
}
