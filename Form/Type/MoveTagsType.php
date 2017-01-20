<?php

namespace Netgen\TagsBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;

class MoveTagsType extends AbstractType
{
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
                    'label' => 'tag.parent_tag',
                )
            );
    }
}
