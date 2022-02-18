<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\Core\FieldType\Tags;

use Ibexa\AdminUi\FieldType\FieldDefinitionFormMapperInterface;
use Ibexa\AdminUi\Form\Data\FieldDefinitionData;
use Ibexa\Contracts\ContentForms\Data\Content\FieldData;
use Ibexa\Contracts\ContentForms\FieldType\FieldValueFormMapperInterface;
use Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface;
use Netgen\TagsBundle\Form\Type\FieldType\TagsFieldType;
use Netgen\TagsBundle\Form\Type\TagTreeType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Validator\Constraints;
use function array_values;

final class FormMapper implements FieldDefinitionFormMapperInterface, FieldValueFormMapperInterface
{
    /**
     * @var \Ibexa\Contracts\Core\SiteAccess\ConfigResolverInterface
     */
    private $configResolver;

    public function __construct(ConfigResolverInterface $configResolver)
    {
        $this->configResolver = $configResolver;
    }

    public function mapFieldValueForm(FormInterface $fieldForm, FieldData $data): void
    {
        $fieldForm
            ->add(
                $fieldForm->getConfig()->getFormFactory()->createBuilder()
                    ->create(
                        'value',
                        TagsFieldType::class,
                        [
                            'required' => $data->fieldDefinition->isRequired,
                            'label' => $data->fieldDefinition->getName(),
                            'field' => $data->field,
                        ]
                    )
                    ->setAutoInitialize(false)
                    ->getForm()
            );
    }

    public function mapFieldDefinitionForm(FormInterface $fieldDefinitionForm, FieldDefinitionData $data): void
    {
        $editViewChoices = [];
        foreach ($this->configResolver->getParameter('edit_views', 'netgen_tags') as $editView) {
            $editViewChoices[$editView['name']] = $editView['identifier'];
        }

        $fieldDefinitionForm
            ->add(
                'subTreeLimit',
                TagTreeType::class,
                [
                    'property_path' => 'validatorConfiguration[TagsValueValidator][subTreeLimit]',
                    'label' => 'field_definition.eztags.validator.subtree_limit',
                ]
            )
            ->add(
                'maxTags',
                IntegerType::class,
                [
                    'required' => false,
                    'property_path' => 'validatorConfiguration[TagsValueValidator][maxTags]',
                    'label' => 'field_definition.eztags.validator.max_tags',
                    'constraints' => [
                        new Constraints\Type(['type' => 'int']),
                        new Constraints\NotBlank(),
                        new Constraints\GreaterThanOrEqual(
                            [
                                'value' => 0,
                            ]
                        ),
                    ],
                    'empty_data' => 0,
                    'attr' => [
                        'min' => 0,
                    ],
                ]
            )
            ->add(
                'hideRootTag',
                CheckboxType::class,
                [
                    'required' => false,
                    'property_path' => 'fieldSettings[hideRootTag]',
                    'label' => 'field_definition.eztags.settings.hide_root_tag',
                    'constraints' => [
                        new Constraints\Type(['type' => 'bool']),
                        new Constraints\NotNull(),
                    ],
                ]
            )
            ->add(
                'editView',
                ChoiceType::class,
                [
                    'choices' => $editViewChoices,
                    'required' => true,
                    'property_path' => 'fieldSettings[editView]',
                    'label' => 'field_definition.eztags.settings.edit_view',
                    'constraints' => [
                        new Constraints\Type(['type' => 'string']),
                        new Constraints\NotBlank(),
                        new Constraints\Choice(
                            [
                                'choices' => array_values($editViewChoices),
                                'strict' => true,
                            ]
                        ),
                    ],
                ]
            );
    }
}
