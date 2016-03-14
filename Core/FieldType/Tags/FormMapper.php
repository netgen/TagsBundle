<?php

namespace Netgen\TagsBundle\Core\FieldType\Tags;

use EzSystems\RepositoryForms\Data\FieldDefinitionData;
use EzSystems\RepositoryForms\FieldType\FieldTypeFormMapperInterface;
use Symfony\Component\Form\FormInterface;

class FormMapper implements FieldTypeFormMapperInterface
{
    /**
     * @var array
     */
    protected $availableEditViews = array();

    /**
     * Sets the available edit views
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
            $editViewChoices[$editView['identifier']] = $editView['name'];
        }

        $fieldDefinitionForm
            ->add(
                'subTreeLimit', 'integer', array(
                    'required' => false,
                    'property_path' => 'fieldSettings[subTreeLimit]',
                    'label' => 'field_definition.eztags.settings.subtree_limit',
                    'empty_data' => 0
                )
            )
            ->add(
                'hideRootTag', 'checkbox', array(
                    'required' => false,
                    'property_path' => 'fieldSettings[hideRootTag]',
                    'label' => 'field_definition.eztags.settings.hide_root_tag',
                )
            )
            ->add(
                'maxTags', 'integer', array(
                    'required' => false,
                    'property_path' => 'fieldSettings[maxTags]',
                    'label' => 'field_definition.eztags.settings.max_tags',
                    'empty_data' => 0,
                    'attr' => array(
                        'min' => 0
                    )
                )
            )
            ->add(
                'editView', 'choice', array(
                    'choices' => $editViewChoices,
                    'required' => true,
                    'property_path' => 'fieldSettings[editView]',
                    'label' => 'field_definition.eztags.settings.edit_view',
                )
            );
    }
}
