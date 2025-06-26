<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\Form\Type;

use Netgen\TagsBundle\API\Repository\Values\Tags\SynonymCreateStruct;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

use function array_key_exists;

final class TagType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add(
                'keyword',
                TextType::class,
                [
                    'label' => 'tag.tag_name',
                ],
            )
            ->add(
                'alwaysAvailable',
                CheckboxType::class,
                [
                    'label' => 'tag.translations.always_available',
                    'required' => false,
                ],
            )
            ->add(
                'remoteId',
                TextType::class,
                [
                    'label' => 'tag.remote_id',
                    'required' => false,
                ],
            );

        if (($options['data_class'] !== SynonymCreateStruct::class)
            && array_key_exists('tag', $options) && $options['tag']->mainTagId === 0) {
            $builder
                ->add(
                    'priority',
                    IntegerType::class,
                    [
                        'label' => 'tag.priority',
                        'required' => false,
                    ],
                );
        }
    }
}
