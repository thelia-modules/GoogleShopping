<?php


namespace GoogleShopping\Controller\Admin;

use GoogleShopping\Event\GoogleShoppingBaseEvent;
use GoogleShopping\Event\GoogleShoppingEvents;
use GoogleShopping\GoogleShopping;
use GoogleShopping\Handler\GoogleShoppingHandler;
use GoogleShopping\Model\GoogleshoppingAccount;
use GoogleShopping\Model\GoogleshoppingAccountQuery;
use GoogleShopping\Model\GoogleshoppingConfigurationQuery;
use GoogleShopping\Model\GoogleshoppingTaxonomyQuery;
use Propel\Runtime\ActiveQuery\Criteria;
use Thelia\Core\Security\AccessManager;
use Thelia\Core\Security\Resource\AdminResources;
use Thelia\Tools\URL;

class CatalogController extends BaseGoogleShoppingController
{
    public function categoryManagementView($categoryId, $configurationId = null)
    {
        if (null !== $response = $this->checkAuth(array(AdminResources::MODULE), 'GoogleShopping', AccessManager::VIEW)) {
            return $response;
        }

        $configuration = GoogleshoppingConfigurationQuery::create()
            ->findOneById($configurationId);

        if (null === $configuration && null !== $this->getRequest()->get('lang_id')) {
            $configuration = GoogleshoppingConfigurationQuery::create()
                ->findOneByLangId($this->getRequest()->get('lang_id'));

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

    public function syncCatalog($secret = null)
    {
        if (null !== $secret && GoogleShopping::getConfigValue("sync_secret") !== $secret) {
            return null;
        } elseif (null !== $response = $this->checkAuth(array(AdminResources::MODULE), array('GoogleShopping'), AccessManager::CREATE)) {
            return $response;
        }

        $googleConfigurations = GoogleshoppingConfigurationQuery::create()
            ->filterByMerchantId(null, Criteria::ISNOTNULL)
            ->find();

        $googleShoppingHandler = (new GoogleShoppingHandler($this->container, $this->getRequest()));
        $client = $googleShoppingHandler->createGoogleClient();
        $googleShoppingService = new \Google_Service_ShoppingContent($client);

        /** @var GoogleshoppingAccount $merchantAccount */
        foreach ($googleConfigurations as $googleConfiguration) {
            $syncEvent = new GoogleShoppingBaseEvent();
            $syncEvent->setMerchantId($merchantAccount->getMerchantId())
                ->setGoogleShoppingService($googleShoppingService);

            $this->getDispatcher()->dispatch(GoogleShoppingEvents::GOOGLE_ACCOUNT_SYNC_PRODUCTS, $syncEvent);
        }
    }
}