<?php


namespace GoogleShopping\Controller\Admin;


use GoogleShopping\Form\TaxonomyForm;
use GoogleShopping\Model\GoogleshoppingTaxonomyQuery;
use Thelia\Controller\Admin\BaseAdminController;
use Thelia\Core\HttpFoundation\Response;
use Thelia\Core\Security\AccessManager;
use Thelia\Core\Security\Resource\AdminResources;

class CategoryController extends BaseAdminController
{
    //TODO : Maybe manage several language
    public function getTaxonomy()
    {
        $file = file_get_contents("http://www.google.com/basepages/producttype/taxonomy.en-US.txt");

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
                ->findOneOrCreate();

            $taxonomy->setGoogleCategory($formData["google_category"])
                ->save();

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

}