<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\Form\Type;

use Netgen\TagsBundle\API\Repository\Values\Tags\TagCreateStruct;
use Netgen\TagsBundle\Validator\Constraints\Structs\TagCreateStruct as TagCreateStructConstraint;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class TagCreateType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver
            ->setDefaults(
                [
                    'data_class' => TagCreateStruct::class,
                    'constraints' => [
                        new TagCreateStructConstraint(),
                    ],
                ]
            );
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add(
            'parentTagId',
            TagTreeType::class,
            [
                'label' => 'tag.parent_tag',
                // Disable constraints specified in TagTreeType, since
                // they are validated in TagCreateStructConstraint
                'constraints' => null,
            ]
        );
    }

    public function getParent(): string
    {
        return TagType::class;
    }
}
