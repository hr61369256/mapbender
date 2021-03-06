<?php
namespace Mapbender\WmcBundle\Element\Type;

use FOM\UserBundle\Form\DataTransformer\GroupIdTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Description of WmcEditorAdminType
 *
 * @author Paul Schmidt
 */
class WmcEditorAdminType extends AbstractType
{

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return 'wmceditor';
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
        $builder->add('tooltip', 'text', array('required' => false))
            ->add('target', 'target_element',
                array(
                'element_class' => 'Mapbender\\CoreBundle\\Element\\Map',
                'application' => $options['application'],
                'property_path' => '[target]',
                'required' => false));
//        $builder->add('accessGroups', 'fom_groups',
//            array(
//            'return_entity' => false,
//            'user_groups' => false,
//            'property_path' => '[accessGroups]',
//            'required' => false,
//            'multiple' => true,
//            'empty_value' => 'Choose an option',));
    }

}
?>
