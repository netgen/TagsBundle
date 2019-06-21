<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\Form\Type;

use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

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
                ]
            )
            ->add(
                'alwaysAvailable',
                CheckboxType::class,
                [
                    'label' => 'tag.translations.always_available',
                    'required' => false,
                ]
            )
            ->add(
                'remoteId',
                TextType::class,
                [
                    'label' => 'tag.remote_id',
                    'required' => false,
                ]
            );
    }
}
