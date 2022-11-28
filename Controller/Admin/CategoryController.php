<?php

namespace GoogleShopping\Controller\Admin;

use GoogleShopping\Form\TaxonomyForm;
use GoogleShopping\GoogleShopping;
use GoogleShopping\Model\GoogleshoppingTaxonomyQuery;
use Thelia\Controller\Admin\BaseAdminController;
use Thelia\Core\HttpFoundation\JsonResponse;
use Thelia\Core\HttpFoundation\Request;
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
        $rows = explode("\n", $file);
        $cats = [];

        foreach ($rows as $row) {
            $splittedCat = explode('>', $row);
            $name = end($splittedCat);
            $cats[$name] =  htmlspecialchars($row);
        }

        return new JsonResponse(['cats' => $cats]);
    }

    public function associateTaxonomy(Request $request)
    {
        if (null !== $response = $this->checkAuth(array(AdminResources::MODULE), array('GoogleShopping'), AccessManager::CREATE)) {
            return $response;
        }

        $message = null;

        $form = $this->createForm(TaxonomyForm::getName());

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
                'current_tab' => 'management'
            )
        );
    }

    public function deleteTaxonomy(Request $request)
    {
        if (null !== $response = $this->checkAuth(array(AdminResources::MODULE), array('GoogleShopping'), AccessManager::DELETE)) {
            return $response;
        }

        $categoryId = $request->request->get('category_id');
        $langId = $request->request->get('lang_id');

        $taxonomy = GoogleshoppingTaxonomyQuery::create()
            ->filterByTheliaCategoryId($categoryId)
            ->filterByLangId($langId)
            ->findOne();

        if (null !== $taxonomy) {
            $taxonomy->delete();
        }

        return new JsonResponse(["message" => "Category association deleted with success !"], 200);
    }
}
