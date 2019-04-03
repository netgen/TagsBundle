<?php

namespace Netgen\TagsBundle\Form\Type;

use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CopyTagsType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver
            ->setRequired('tags')
            ->setAllowedTypes('tags', 'array')
            ->setAllowedValues('tags', function (array $tags) {
                foreach ($tags as $tag) {
                    if (!$tag instanceof Tag) {
                        return false;
                    }
                }

                return true;
            });
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
                [
                    'label' => 'tag.parent_tag',
                    'disableSubtree' => array_map(
                        function (Tag $tag) {
                            return $tag->id;
                        },
                        $options['tags']
                    ),
                ]
            );
    }
}
