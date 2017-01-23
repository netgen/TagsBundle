<?php

namespace Netgen\TagsBundle\Form\Type;

use Netgen\TagsBundle\API\Repository\Values\Tags\TagCreateStruct;
use Netgen\TagsBundle\Validator\Constraints\Structs\TagCreateStruct as TagCreateStructConstraint;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TagCreateType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver
            ->setDefaults(
                array(
                    'data_class' => TagCreateStruct::class,
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
            TagTreeType::class,
            array(
                'label' => 'tag.parent_tag',
                // Disable constraints specified in TagTreeType, since
                // they are validated in TagCreateStructConstraint
                'constraints' => null,
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return TagType::class;
    }
}
