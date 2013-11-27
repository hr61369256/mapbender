<?php

namespace Mapbender\CoreBundle\Element\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;


/**
 *
 */
class PrintClientAdminType extends AbstractType
{

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return 'printclient';
    }

    /**
     * @inheritdoc
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'application' => null
        ));
    }

    /**
     * @inheritdoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('target', 'target_element', array(
                    'element_class' => 'Mapbender\\CoreBundle\\Element\\Map',
                    'application' => $options['application'],
                    'property_path' => '[target]',
                    'required' => false))
                ->add('autoOpen', 'checkbox', array(
                    'required' => false))
                ->add('scales', 'collection', array(
                    'type' => 'text',
                    'allow_add' => true,
                    'allow_delete' => true,
                    'required' => false))
                ->add('file_prefix', 'text', array(
                    'required' => false))
                ->add('rotatable', 'checkbox', array(
                    'required' => false))
                ->add('templates', 'collection', array(
                    'type' => new PrintClientTemplateAdminType(),
                    'allow_add' => true,
                    'allow_delete' => true
                  ))
                ->add('templates', 'collection', array(
                    'type' => new PrintClientQualityAdminType(),
                    'allow_add' => true,
                    'allow_delete' => true
                  ));
    }
}
