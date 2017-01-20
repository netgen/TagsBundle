<?php

namespace Netgen\TagsBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;

class TagConvertType extends AbstractType
{
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
                    'label' => 'tag.main_tag',
                    'allowRootTag' => false,
                )
            );
    }
}
