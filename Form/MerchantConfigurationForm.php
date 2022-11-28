<?php


namespace GoogleShopping\Form;

use GoogleShopping\GoogleShopping;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Thelia\Core\Translation\Translator;
use Thelia\Form\BaseForm;
use Symfony\Component\Validator\Constraints;

class MerchantConfigurationForm extends BaseForm
{

    protected function buildForm()
    {
        $this->formBuilder
            ->add("title", TextType::class, array(
                'label' => Translator::getInstance()->trans(
                    'Configuration title',
                    array(),
                    GoogleShopping::DOMAIN_NAME
                ),
                'label_attr' => array(
                    'for' => 'title'
                ),
            ))
            ->add("merchant_id", TextType::class, array(
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
            ->add("lang_id", TextType::class, array(
                'label' => Translator::getInstance()->trans(
                    'Lang',
                    array(),
                    GoogleShopping::DOMAIN_NAME
                ),
                'label_attr' => array(
                    'for' => 'lang_id'
                )
            ))
            ->add("country_id", TextType::class, array(
                'label' => Translator::getInstance()->trans(
                    'Country',
                    array(),
                    GoogleShopping::DOMAIN_NAME
                ),
                'label_attr' => array(
                    'for' => 'country_id'
                )
            ))
            ->add("currency_id", TextType::class, array(
                'label' => Translator::getInstance()->trans(
                    'Currency',
                    array(),
                    GoogleShopping::DOMAIN_NAME
                ),
                'label_attr' => array(
                    'for' => 'currency_id'
                )
            ))
            ->add("is_default", TextType::class, array(
                'label' => Translator::getInstance()->trans(
                    'Is default',
                    array(),
                    GoogleShopping::DOMAIN_NAME
                ),
                'label_attr' => array(
                    'for' => 'is_default'
                )
            ))
            ->add("sync", TextType::class, array(
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

    public static function getName()
    {
        return "googleshopping_configuration";
    }

}
