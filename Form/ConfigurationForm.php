<?php


namespace GoogleShopping\Form;

use GoogleShopping\GoogleShopping;
use Thelia\Core\Translation\Translator;
use Thelia\Form\BaseForm;
use Symfony\Component\Validator\Constraints;

class ConfigurationForm extends BaseForm
{

    protected function buildForm()
    {
        $this->formBuilder
            ->add("title", "text", array(
                'label' => Translator::getInstance()->trans(
                    'Configuration title',
                    array(),
                    GoogleShopping::DOMAIN_NAME
                ),
                'label_attr' => array(
                    'for' => 'title'
                ),
            ))
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
            ->add("lang_id", "text", array(
                'label' => Translator::getInstance()->trans(
                    'Lang',
                    array(),
                    GoogleShopping::DOMAIN_NAME
                ),
                'label_attr' => array(
                    'for' => 'lang_id'
                )
            ))
            ->add("country_id", "text", array(
                'label' => Translator::getInstance()->trans(
                    'Country',
                    array(),
                    GoogleShopping::DOMAIN_NAME
                ),
                'label_attr' => array(
                    'for' => 'country_id'
                )
            ))
            ->add("currency_id", "text", array(
                'label' => Translator::getInstance()->trans(
                    'Currency',
                    array(),
                    GoogleShopping::DOMAIN_NAME
                ),
                'label_attr' => array(
                    'for' => 'currency_id'
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
            ))
            ->add("sync", "text", array(
                'label' => Translator::getInstance()->trans(
                    'Sync active',
                    array(),
                    GoogleShopping::DOMAIN_NAME
                ),
                'label_attr' => array(
                    'for' => 'sync'
                )
            ));
    }
    
    public function getName()
    {
        return "googleshopping_configuration";
    }

}
