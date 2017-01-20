<?php

namespace Netgen\TagsBundle\Form\Type;

use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints;

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
                    'constraints' => array(
                        new Constraints\NotBlank(),
                        new Constraints\Type(array('type' => 'string')),
                    ),
                )
            )
            ->add(
                'alwaysAvailable',
                CheckboxType::class,
                array(
                    'label' => 'tag.translations.always_available',
                    'required' => false,
                    'constraints' => array(
                        new Constraints\NotNull(),
                        new Constraints\Type(array('type' => 'bool')),
                    ),
                )
            )
            ->add(
                'remoteId',
                TextType::class,
                array(
                    'label' => 'tag.remote_id',
                    'required' => false,
                    'constraints' => array(
                        new Constraints\Type(array('type' => 'string')),
                    ),
                )
            );
    }
}
