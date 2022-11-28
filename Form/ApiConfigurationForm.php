<?php


namespace GoogleShopping\Form;

use GoogleShopping\GoogleShopping;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Thelia\Core\Translation\Translator;
use Thelia\Form\BaseForm;
use Symfony\Component\Validator\Constraints;

class ApiConfigurationForm extends BaseForm
{
    protected function buildForm()
    {
        $this->formBuilder
            ->add("client_id", TextType::class, array(
                'required' => true,
                'label' => Translator::getInstance()->trans(
                    'Client identifier',
                    array(),
                    GoogleShopping::DOMAIN_NAME
                ),
                'data' => GoogleShopping::getConfigValue('client_id'),
                'label_attr' => array(
                    'for' => 'client_id'
                ),
                "constraints" => array(
                    new Constraints\NotBlank(),
                )
            ))
            ->add("client_secret", TextType::class, array(
                'required' => true,
                'label' => Translator::getInstance()->trans(
                    'Client secret token',
                    array(),
                    GoogleShopping::DOMAIN_NAME
                ),
                'data' => GoogleShopping::getConfigValue('client_secret'),
                'label_attr' => array(
                    'for' => 'client_secret'
                ),
                "constraints" => array(
                    new Constraints\NotBlank(),
                )
            ))
            ->add("application_name", TextType::class, array(
                'required' => true,
                'label' => Translator::getInstance()->trans(
                    'Application name',
                    array(),
                    GoogleShopping::DOMAIN_NAME
                ),
                'data' => GoogleShopping::getConfigValue('application_name'),
                'label_attr' => array(
                    'for' => 'application_name'
                ),
                "constraints" => array(
                    new Constraints\NotBlank(),
                )
            ));
    }

    public static function getName()
    {
        return "googleshopping_api_configuration";
    }

}
