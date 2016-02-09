<?php


namespace GoogleShopping\Form;

use GoogleShopping\GoogleShopping;
use Thelia\Core\Translation\Translator;
use Thelia\Form\BaseForm;
use Symfony\Component\Validator\Constraints;

class MiscConfigurationForm extends BaseForm
{

    protected function buildForm()
    {
        $this->formBuilder
            ->add("check_gtin", "text", array(
                'label' => Translator::getInstance()->trans(
                    'Check GTIN before send :',
                    array(),
                    GoogleShopping::DOMAIN_NAME
                ),
                'data' => GoogleShopping::getConfigValue('check_gtin'),
                'label_attr' => array(
                    'for' => 'check_gtin'
                )
            ))
            ->add("attribute_color", "text", array(
                'label' => Translator::getInstance()->trans(
                    'Attributes color',
                    array(),
                    GoogleShopping::DOMAIN_NAME
                ),
                'data' => explode(',', GoogleShopping::getConfigValue('attribute_color')),
                'label_attr' => array(
                    'for' => 'attribute_color'
                ),
            ))
            ->add("attribute_size", "text", array(
                'label' => Translator::getInstance()->trans(
                    'Attributes size',
                    array(),
                    GoogleShopping::DOMAIN_NAME
                ),
                'data' => explode(',', GoogleShopping::getConfigValue('attribute_size')),
                'label_attr' => array(
                    'for' => 'attribute_size'
                )
            ));
    }
    
    public function getName()
    {
        return "googleshopping_attribute_configuration";
    }

}
