<?php


namespace GoogleShopping\Form;

use GoogleShopping\GoogleShopping;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Thelia\Core\Translation\Translator;
use Thelia\Form\BaseForm;
use Symfony\Component\Validator\Constraints;

class TaxonomyForm extends BaseForm
{
    protected function buildForm()
    {
        $this->formBuilder
            ->add("thelia_category_id", TextType::class, array(
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
            ->add("google_category", TextType::class, array(
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
            ->add("lang", TextType::class, array(
                'required' => true,
                "constraints" => array(
                    new Constraints\NotBlank(),
                )
            ))
            ;
    }

    public static function getName()
    {
        return "googleshopping_taxonomy";
    }
}
