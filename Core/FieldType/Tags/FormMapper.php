<?php

namespace Netgen\TagsBundle\Core\FieldType\Tags;

use Netgen\TagsBundle\Form\Type\TagTreeType;
use EzSystems\RepositoryForms\Data\FieldDefinitionData;
use EzSystems\RepositoryForms\FieldType\FieldDefinitionFormMapperInterface;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Validator\Constraints;

class FormMapper implements FieldDefinitionFormMapperInterface
{
    /**
     * @var array
     */
    protected $availableEditViews = array();

    /**
     * Sets the available edit views.
     *
     * @param array $availableEditViews
     */
    public function setEditViews(array $availableEditViews)
    {
        $this->availableEditViews = $availableEditViews;
    }

    public function mapFieldDefinitionForm(FormInterface $fieldDefinitionForm, FieldDefinitionData $data)
    {
        $editViewChoices = array();
        foreach ($this->availableEditViews as $editView) {
            $editViewChoices[$editView['name']] = $editView['identifier'];
        }

        $fieldDefinitionForm
            ->add(
                'subTreeLimit', TagTreeType::class, array(
                    'property_path' => 'fieldSettings[subTreeLimit]',
                    'label' => 'field_definition.eztags.settings.subtree_limit',
                )
            )
            ->add(
                'hideRootTag', CheckboxType::class, array(
                    'required' => false,
                    'property_path' => 'fieldSettings[hideRootTag]',
                    'label' => 'field_definition.eztags.settings.hide_root_tag',
                    'constraints' => array(
                        new Constraints\Type(array('type' => 'bool')),
                        new Constraints\NotNull(),
                    ),
                )
            )
            ->add(
                'maxTags', IntegerType::class, array(
                    'required' => false,
                    'property_path' => 'fieldSettings[maxTags]',
                    'label' => 'field_definition.eztags.settings.max_tags',
                    'constraints' => array(
                        new Constraints\Type(array('type' => 'int')),
                        new Constraints\NotBlank(),
                        new Constraints\GreaterThanOrEqual(
                            array(
                                'value' => 0,
                            )
                        ),
                    ),
                    'empty_data' => 0,
                    'attr' => array(
                        'min' => 0,
                    ),
                )
            )
            ->add(
                'editView', ChoiceType::class, array(
                    'choices' => $editViewChoices,
                    'choices_as_values' => true,
                    'required' => true,
                    'property_path' => 'fieldSettings[editView]',
                    'label' => 'field_definition.eztags.settings.edit_view',
                    'constraints' => array(
                        new Constraints\Type(array('type' => 'string')),
                        new Constraints\NotBlank(),
                        new Constraints\Choice(
                            array(
                                'choices' => array_values($editViewChoices),
                                'strict' => true,
                            )
                        ),
                    ),
                )
            );
    }
}
