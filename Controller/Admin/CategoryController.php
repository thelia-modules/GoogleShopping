<?php

namespace GoogleShopping\Controller\Admin;

use GoogleShopping\Form\TaxonomyForm;
use GoogleShopping\GoogleShopping;
use GoogleShopping\Model\GoogleshoppingTaxonomyQuery;
use Thelia\Controller\Admin\BaseAdminController;
use Thelia\Core\HttpFoundation\Response;
use Thelia\Core\Security\AccessManager;
use Thelia\Core\Security\Resource\AdminResources;
use Thelia\Model\Lang;
use Thelia\Model\LangQuery;

class CategoryController extends BaseAdminController
{
    public function getTaxonomy($langId = null)
    {
        $lang = LangQuery::create()->findOneById($langId);

        if ($lang === null) {
            $lang = Lang::getDefaultLanguage();
        }

        $file = file_get_contents("http://www.google.com/basepages/producttype/taxonomy.".str_replace("_", "-", $lang->getLocale()).".txt");

        return new Response($file);
    }

    public function associateTaxonomy()
    {
        if (null !== $response = $this->checkAuth(array(AdminResources::MODULE), array('GoogleShopping'), AccessManager::CREATE)) {
            return $response;
        }

        $message = null;

        $form = new TaxonomyForm($this->getRequest());

        try {
            $formData = $this->validateForm($form)->getData();

            $taxonomy = GoogleshoppingTaxonomyQuery::create()
                ->filterByTheliaCategoryId($formData["thelia_category_id"])
                ->filterByLangId($formData["lang"])
                ->findOneOrCreate();

            $taxonomy->setGoogleCategory($formData["google_category"])
                ->save();

        } catch (\Exception $e) {
            $message = $e->getMessage();
            $this->setupFormErrorContext(
                $this->getTranslator()->trans("GoogleShopping configuration", [], GoogleShopping::DOMAIN_NAME),
                $message,
                $form,
                $e
            );
        }

        return $this->generateRedirectFromRoute(
            "admin.module.configure",
            array(),
            array(
                'module_code' => 'GoogleShopping',
                'current_tab' => 'association'
            )
        );
    }

}