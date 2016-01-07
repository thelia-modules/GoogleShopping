<?php

namespace GoogleShopping\Controller\Admin;

use GoogleShopping\Event\GoogleShoppingEvents;
use GoogleShopping\Form\ApiConfigurationForm;
use GoogleShopping\Form\MerchantConfigurationForm;
use GoogleShopping\GoogleShopping;
use GoogleShopping\Handler\GoogleShoppingHandler;
use GoogleShopping\Model\GoogleshoppingAccountQuery;
use Thelia\Controller\Admin\BaseAdminController;
use Thelia\Core\HttpFoundation\JsonResponse;
use Thelia\Core\Security\AccessManager;
use Thelia\Core\Security\Resource\AdminResources;

class ConfigurationController extends BaseAdminController
{
    public function viewAllAction($params = array())
    {
        if (null !== $response = $this->checkAuth(array(AdminResources::MODULE), 'GoogleShopping', AccessManager::VIEW)) {
            return $response;
        }

        return $this->render(
            "google-shopping/configuration",
            array(
                "sync_secret" => GoogleShopping::getConfigValue('sync_secret')
            )
        );
    }

    public function saveApiConfiguration()
    {
        if (null !== $response = $this->checkAuth(array(AdminResources::MODULE), array('GoogleShopping'), AccessManager::CREATE)) {
            return $response;
        }

        $message = null;

        $form = new ApiConfigurationForm($this->getRequest());

        try {
            $formData = $this->validateForm($form)->getData();

            foreach ($formData as $name => $value) {
                if ($name === "success_url" || $name === "error_message") {
                    continue;
                }
                GoogleShopping::setConfigValue($name, $value);
            }

            return $this->generateRedirect('/admin/module/GoogleShopping');

        } catch (\Exception $e) {
            $message = $e->getMessage();
        }

        $this->setupFormErrorContext(
            $this->getTranslator()->trans("GoogleShopping configuration", [], GoogleShopping::DOMAIN_NAME),
            $message,
            $form,
            $e
        );


        return $this->render('module-configure', array('module_code' => 'GoogleShopping'));
    }

    public function saveMerchantConfiguration()
    {
        if (null !== $response = $this->checkAuth(array(AdminResources::MODULE), array('GoogleShopping'), AccessManager::CREATE)) {
            return $response;
        }

        $message = null;

        $form = new MerchantConfigurationForm($this->getRequest());

        try {
            $formData = $this->validateForm($form)->getData();

            foreach ($formData as $name => $value) {
                if ($name === "success_url" || $name === "error_message") {
                    continue;
                }
                GoogleShopping::setConfigValue($name, $value);
            }

            return $this->generateRedirect('/admin/module/GoogleShopping');

        } catch (\Exception $e) {
            $message = $e->getMessage();
        }

        $this->setupFormErrorContext(
            $this->getTranslator()->trans("GoogleShopping configuration", [], GoogleShopping::DOMAIN_NAME),
            $message,
            $form,
            $e
        );

        return $this->render('module-configure', array('module_code' => 'GoogleShopping'));
    }

    public function addMerchantAccount()
    {
        if (null !== $response = $this->checkAuth(array(AdminResources::MODULE), array('GoogleShopping'), AccessManager::CREATE)) {
            return $response;
        }

        $form = $this->createForm("googleshopping.merchant.account");

        try {
            $data = $this->validateForm($form, 'POST')->getData();

            $googleShoppingAccount = GoogleshoppingAccountQuery::create()
                ->filterByMerchantId($data['merchant_id'])
                ->findOneOrCreate();

            $googleShoppingAccount
                ->setDefaultCountryId($data['default_country_id'])
                ->setDefaultLangId($data['default_lang_id'])
                ->save();

            return new JsonResponse("", 200);
        } catch (\Exception $e) {
            return new JsonResponse($e->getMessage(), 500);
        }
    }

    public function updateMerchantAccount($id)
    {
        if (null !== $response = $this->checkAuth(array(AdminResources::MODULE), array('GoogleShopping'), AccessManager::CREATE)) {
            return $response;
        }

        $form = $this->createForm("googleshopping.merchant.account");

        try {
            $data = $this->validateForm($form, 'POST')->getData();

            $googleShoppingAccount = GoogleshoppingAccountQuery::create()
                ->findOneById($id);

            if (null !== $googleShoppingAccount) {
                $googleShoppingAccount->setMerchantId($data['merchant_id'])
                    ->setDefaultCountryId($data['default_country_id'])
                    ->setDefaultLangId($data['default_lang_id'])
                    ->save();
            }

            return new JsonResponse("", 200);

        } catch (\Exception $e) {
            return new JsonResponse($e->getMessage(), 500);
        }
    }

    public function deleteMerchantAccount($id)
    {
        if (null !== $response = $this->checkAuth(array(AdminResources::MODULE), array('GoogleShopping'), AccessManager::CREATE)) {
            return $response;
        }

        $form = $this->createForm("googleshopping.merchant.account");

        try {
            $data = $this->validateForm($form, 'POST')->getData();

            $googleShoppingAccount = GoogleshoppingAccountQuery::create()
                ->findOneById($id);

            if (null !== $googleShoppingAccount) {
                $googleShoppingAccount->delete();
            }

            return new JsonResponse("", 200);

        } catch (\Exception $e) {
            return new JsonResponse($e->getMessage(), 500);
        }
    }

    public function syncCatalog($secret = null)
    {
        if (null !== $secret && GoogleShopping::getConfigValue("sync_secret") !== $secret) {
            return null;
        } elseif (null !== $response = $this->checkAuth(array(AdminResources::MODULE), array('GoogleShopping'), AccessManager::CREATE)) {
            return $response;
        }

        try {
            $this->getDispatcher()->dispatch(GoogleShoppingEvents::GOOGLE_SYNC_CATALOG);

            return $this->generateRedirectFromRoute(
                "admin.module.configure",
                array(),
                array(
                    'module_code' => 'GoogleShopping',
                    'current_tab' => 'management',
                    'google_alert' => "success",
                    'google_message' =>
                        $this->getTranslator()->trans(
                            "Catalog sync with success",
                            [],
                            GoogleShopping::DOMAIN_NAME
                        )
                )
            );
        } catch (\Exception $e) {
            return $this->generateRedirectFromRoute(
                "admin.module.configure",
                array(),
                array(
                    'module_code' => 'GoogleShopping',
                    'current_tab' => 'management',
                    'google_alert' => "error",
                    'google_message' =>
                        $this->getTranslator()->trans(
                            "Error on Google Shopping synchonisation : %message",
                            ['message' => $e->getMessage()],
                            GoogleShopping::DOMAIN_NAME
                        )
                )
            );
        }
    }
}
