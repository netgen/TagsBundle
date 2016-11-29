<?php

namespace Netgen\TagsBundle\Form\Type\Admin;

use Netgen\TagsBundle\Validator\Constraints\Structs\TagCreateStruct as TagCreateStructConstraint;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TagCreateType extends AbstractType
{
    /**
     * @param \Symfony\Component\OptionsResolver\OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class' => 'Netgen\TagsBundle\API\Repository\Values\Tags\TagCreateStruct',
                'constraints' => array(
                    new TagCreateStructConstraint(),
                ),
            ]);
    }

    /**
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('parentTagId', TextType::class);
    }

    /**
     * @return mixed
     */
    public function getParent()
    {
        return TagType::class;
    }
}
