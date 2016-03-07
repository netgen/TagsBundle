<?php

namespace Netgen\TagsBundle\Core\FieldType\Tags;

use EzSystems\RepositoryForms\Data\FieldDefinitionData;
use EzSystems\RepositoryForms\FieldType\FieldTypeFormMapperInterface;
use Symfony\Component\Form\FormInterface;

class FormMapper implements FieldTypeFormMapperInterface
{
    public function mapFieldDefinitionForm(FormInterface $fieldDefinitionForm, FieldDefinitionData $data)
    {
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
                'showDropDown', 'checkbox', array(
                    'required' => false,
                    'property_path' => 'fieldSettings[showDropDown]',
                    'label' => 'field_definition.eztags.settings.show_dropdown',
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
            );
    }
}
