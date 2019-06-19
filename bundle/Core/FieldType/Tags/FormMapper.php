<?php

namespace Netgen\TagsBundle\Core\FieldType\Tags;

use EzSystems\RepositoryForms\Data\Content\FieldData;
use EzSystems\RepositoryForms\Data\FieldDefinitionData;
use EzSystems\RepositoryForms\FieldType\FieldDefinitionFormMapperInterface;
use EzSystems\RepositoryForms\FieldType\FieldValueFormMapperInterface;
use Netgen\TagsBundle\Form\Type\FieldType\TagsFieldType;
use Netgen\TagsBundle\Form\Type\TagTreeType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Validator\Constraints;

class FormMapper implements FieldDefinitionFormMapperInterface, FieldValueFormMapperInterface
{
    /**
     * @var array
     */
    private $availableEditViews = [];

    /**
     * Sets the available edit views.
     */
    public function setEditViews(array $availableEditViews): void
    {
        $this->availableEditViews = $availableEditViews;
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
        foreach ($this->availableEditViews as $editView) {
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
                    'choices_as_values' => true,
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
