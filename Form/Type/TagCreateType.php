<?php

namespace Netgen\TagsBundle\Form\Type;

use Netgen\TagsBundle\Validator\Constraints\Structs\TagCreateStruct as TagCreateStructConstraint;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TagCreateType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults(
                array(
                    'data_class' => 'Netgen\TagsBundle\API\Repository\Values\Tags\TagCreateStruct',
                    'constraints' => array(
                        new TagCreateStructConstraint(),
                    ),
                )
            );
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'parentTagId',
            'Symfony\Component\Form\Extension\Core\Type\HiddenType'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'Netgen\TagsBundle\Form\Type\TagType';
    }
}
