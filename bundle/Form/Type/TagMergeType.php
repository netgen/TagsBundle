<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\Form\Type;

use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class TagMergeType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver
            ->setRequired('tag')
            ->setAllowedTypes('tag', Tag::class);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'mainTag',
                TagTreeType::class,
                [
                    'label' => 'tag.main_tag',
                    'disableSubtree' => [$options['tag']->id],
                    'allowRootTag' => false,
                ],
            );
    }
}
