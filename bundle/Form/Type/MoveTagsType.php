<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\Form\Type;

use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class MoveTagsType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver
            ->setRequired('tags')
            ->setAllowedTypes('tags', 'array')
            ->setAllowedValues('tags', static function (array $tags): bool {
                foreach ($tags as $tag) {
                    if (!$tag instanceof Tag) {
                        return false;
                    }
                }

                return true;
            });
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'parentTag',
                TagTreeType::class,
                [
                    'label' => 'tag.parent_tag',
                    'disableSubtree' => array_map(
                        static function (Tag $tag): int {
                            return $tag->id;
                        },
                        $options['tags']
                    ),
                ]
            );
    }
}
