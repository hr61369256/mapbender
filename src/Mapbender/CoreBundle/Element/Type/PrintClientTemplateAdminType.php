<?php
namespace Mapbender\CoreBundle\Element\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 *
 */
class PrintClientTemplateAdminType extends AbstractType
{

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return 'printclienttemplate';
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
        $builder->add('template', 'text', array('required' => false))
                ->add('label', 'text', array('required' => false))
                ->add('format', 'text', array('required' => false))
                ->add('optional_fields', 'collection', array(
                    'type' => new PrintClientOptionalFieldAdminType(),
                    'required' => false,
                    'allow_add' => true,
                    'allow_delete' => true,
                    'attr' => array(
                        'class' => 'collectionItemLevel2')));
    }
}
