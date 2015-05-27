<?php


namespace GoogleShopping\Form;

use GoogleShopping\GoogleShopping;
use Thelia\Core\Translation\Translator;
use Thelia\Form\BaseForm;
use Symfony\Component\Validator\Constraints;

class TaxonomyForm extends BaseForm
{
    protected function buildForm()
    {
        $this->formBuilder
            ->add("thelia_category_id", "text", array(
                'required' => true,
                'label' => Translator::getInstance()->trans(
                    'googleshopping.taxonomy.label.thelia_category_id',
                    array(),
                    GoogleShopping::DOMAIN_NAME
                ),
                'label_attr' => array(
                    'for' => 'thelia_category_id'
                ),
                "constraints" => array(
                    new Constraints\NotBlank(),
                )
            ))
            ->add("google_category", "text", array(
                'required' => true,
                'label' => Translator::getInstance()->trans(
                    'googleshopping.taxonomy.label.google_category',
                    array(),
                    GoogleShopping::DOMAIN_NAME
                ),
                'label_attr' => array(
                    'for' => 'google_category'
                ),
                "constraints" => array(
                    new Constraints\NotBlank(),
                )
            ))
            ;
    }

    public function getName()
    {
        return "googleshopping_taxonomy";
    }
}