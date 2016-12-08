<?php

namespace Netgen\TagsBundle\Form\Type;

use Netgen\TagsBundle\Validator\Constraints\Tag;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints;

class TagConvertType extends AbstractType
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
                'mainTag',
                TagTreeType::class,
                array(
                    'constraints' => array(
                        new Constraints\Type(array('type' => 'scalar')),
                        new Constraints\NotBlank(),
                        new Tag(array(
                            'allowRootTag' => false,
                        )),
                    ),
                    'label' => 'tag.main_tag',
                )
            );
    }
}
