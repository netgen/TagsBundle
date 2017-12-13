<?php

namespace Netgen\TagsBundle\Form\Type\FieldType;

use eZ\Publish\API\Repository\FieldTypeService;
use eZ\Publish\API\Repository\Values\Content\Field;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Exception\InvalidConfigurationException;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TagsFieldType extends AbstractType
{
    /**
     * @var \eZ\Publish\API\Repository\FieldTypeService
     */
    private $fieldTypeService;

    public function __construct(FieldTypeService $fieldTypeService)
    {
        $this->fieldTypeService = $fieldTypeService;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(array('field'));
        $resolver->setAllowedTypes('field', Field::class);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ($options['field']->fieldTypeIdentifier !== 'eztags') {
            throw new InvalidConfigurationException(
                sprintf(
                    '%s form type only works with fields of type "eztags", "%s" given',
                    self::class,
                    $options['field']->fieldTypeIdentifier
                )
            );
        }

        $builder
            ->add('ids', HiddenType::class)
            ->add('parent_ids', HiddenType::class)
            ->add('keywords', HiddenType::class)
            ->add('locales', HiddenType::class)
            ->addModelTransformer(
                new FieldValueTransformer(
                    $this->fieldTypeService->getFieldType('eztags'),
                    $options['field']
                )
            );
    }

    public function getBlockPrefix()
    {
        return 'ezplatform_fieldtype_eztags';
    }
}
