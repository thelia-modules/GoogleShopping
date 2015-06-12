<?php


namespace GoogleShopping\Form;

use GoogleShopping\GoogleShopping;
use Thelia\Core\Translation\Translator;
use Thelia\Form\BaseForm;
use Symfony\Component\Validator\Constraints;

class MerchantConfigurationForm extends BaseForm
{

    protected function buildForm()
    {
        $this->formBuilder
            ->add("merchant_id", "text", array(
                'required' => true,
                'label' => Translator::getInstance()->trans(
                    'googleshopping.configuration.merchantid',
                    array(),
                    GoogleShopping::DOMAIN_NAME
                ),
                'data' => GoogleShopping::getConfigValue('merchant_id'),
                'label_attr' => array(
                    'for' => 'merchant_id'
                ),
                "constraints" => array(
                    new Constraints\NotBlank(),
                )
            ))
            ->add("target_country_id", "text", array(
                'label' => Translator::getInstance()->trans(
                    'googleshopping.configuration.target.country',
                    array(),
                    GoogleShopping::DOMAIN_NAME
                ),
                'data' => GoogleShopping::getConfigValue('target_country_id'),
                'label_attr' => array(
                    'for' => 'target_country_id'
                )
            ))
            ->add("attribute_color", "number", array(
                'label' => Translator::getInstance()->trans(
                    'googleshopping.configuration.attribute.color',
                    array(),
                    GoogleShopping::DOMAIN_NAME
                ),
                'data' => GoogleShopping::getConfigValue('attribute_color'),
                'label_attr' => array(
                    'for' => 'attribute_color'
                )
            ))
            ->add("attribute_size", "number", array(
                'label' => Translator::getInstance()->trans(
                    'googleshopping.configuration.attribute.size',
                    array(),
                    GoogleShopping::DOMAIN_NAME
                ),
                'data' => GoogleShopping::getConfigValue('attribute_size'),
                'label_attr' => array(
                    'for' => 'attribute_size'
                )
            ));
    }
    
    public function getName()
    {
        return "googleshopping_merchant_configuration";
    }

}
