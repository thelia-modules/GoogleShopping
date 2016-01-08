<?php


namespace GoogleShopping\Form;

use GoogleShopping\GoogleShopping;
use Thelia\Core\Translation\Translator;
use Thelia\Form\BaseForm;
use Symfony\Component\Validator\Constraints;

class MerchantAccountForm extends BaseForm
{

    protected function buildForm()
    {
        $this->formBuilder
            ->add("merchant_id", "text", array(
                'required' => true,
                'label' => Translator::getInstance()->trans(
                    'GoogleShopping merchant ID',
                    array(),
                    GoogleShopping::DOMAIN_NAME
                ),
                'label_attr' => array(
                    'for' => 'merchant_id'
                ),
                "constraints" => array(
                    new Constraints\NotBlank(),
                )
            ))
            ->add("default_country_id", "text", array(
                'label' => Translator::getInstance()->trans(
                    'Default country',
                    array(),
                    GoogleShopping::DOMAIN_NAME
                ),
                'label_attr' => array(
                    'for' => 'default_country_id'
                )
            ))
            ->add("default_currency_id", "text", array(
                'label' => Translator::getInstance()->trans(
                    'Default currency',
                    array(),
                    GoogleShopping::DOMAIN_NAME
                ),
                'label_attr' => array(
                    'for' => 'default_currency_id'
                )
            ))
            ->add("is_default", "text", array(
                'label' => Translator::getInstance()->trans(
                    'Is default',
                    array(),
                    GoogleShopping::DOMAIN_NAME
                ),
                'label_attr' => array(
                    'for' => 'is_default'
                )
            ));
    }
    
    public function getName()
    {
        return "googleshopping_merchant_account";
    }

}
