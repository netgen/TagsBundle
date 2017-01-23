<?php

namespace Netgen\TagsBundle\Form\Type;

use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class TagType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'keyword',
                TextType::class,
                array(
                    'label' => 'tag.tag_name',
                )
            )
            ->add(
                'alwaysAvailable',
                CheckboxType::class,
                array(
                    'label' => 'tag.translations.always_available',
                    'required' => false,
                )
            )
            ->add(
                'remoteId',
                TextType::class,
                array(
                    'label' => 'tag.remote_id',
                    'required' => false,
                )
            );
    }
}
