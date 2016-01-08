<?php


namespace GoogleShopping\Form;

use GoogleShopping\GoogleShopping;
use Thelia\Core\Translation\Translator;
use Thelia\Form\BaseForm;
use Symfony\Component\Validator\Constraints;

class AttributeConfigurationForm extends BaseForm
{

    protected function buildForm()
    {
        $this->formBuilder
            ->add("attribute_color", "text", array(
                'label' => Translator::getInstance()->trans(
                    'Attributes color ids',
                    array(),
                    GoogleShopping::DOMAIN_NAME
                ),
                'data' => GoogleShopping::getConfigValue('attribute_color'),
                'label_attr' => array(
                    'for' => 'attribute_color'
                )
            ))
            ->add("attribute_size", "text", array(
                'label' => Translator::getInstance()->trans(
                    'Attributes size ids',
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
        return "googleshopping_attribute_configuration";
    }

}
