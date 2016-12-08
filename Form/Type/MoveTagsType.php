<?php

namespace Netgen\TagsBundle\Form\Type;

use Netgen\TagsBundle\Validator\Constraints\Tag;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MoveTagsType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults(
                array(
                    'translation_domain' => 'eztags_admin',
                )
            );
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'parentTag',
                TagTreeType::class,
                array(
                    'constraints' => array(
                        new Tag(),
                    ),
                    'label' => 'tag.parent',
                )
            );
    }
}
