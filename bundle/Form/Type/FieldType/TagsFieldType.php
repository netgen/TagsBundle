<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\Form\Type\FieldType;

use Ibexa\Contracts\Core\Repository\FieldTypeService;
use Ibexa\Contracts\Core\Repository\Values\Content\Field;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class TagsFieldType extends AbstractType
{
    public function __construct(private FieldTypeService $fieldTypeService)
    {
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired(['field']);
        $resolver->setAllowedTypes('field', Field::class);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('ids', HiddenType::class)
            ->add('parent_ids', HiddenType::class)
            ->add('keywords', HiddenType::class)
            ->add('locales', HiddenType::class)
            ->addModelTransformer(
                new FieldValueTransformer(
                    $this->fieldTypeService->getFieldType('eztags'),
                    $options['field'],
                ),
            );
    }

    public function getBlockPrefix(): string
    {
        return 'ezplatform_fieldtype_eztags';
    }
}
