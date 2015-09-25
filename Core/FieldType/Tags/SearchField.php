<?php

namespace Netgen\TagsBundle\Core\FieldType\Tags;

use eZ\Publish\SPI\Persistence\Content\Field;
use eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition;
use eZ\Publish\SPI\FieldType\Indexable;
use eZ\Publish\SPI\Search;

class SearchField implements Indexable
{
    /**
     * Get index data for field for search backend.
     *
     * @param \eZ\Publish\SPI\Persistence\Content\Field $field
     * @param \eZ\Publish\SPI\Persistence\Content\Type\FieldDefinition $fieldDefinition
     *
     * @return \eZ\Publish\SPI\Search\Field[]
     */
    public function getIndexData(Field $field, FieldDefinition $fieldDefinition)
    {
        $tagKeywords = array();
        $parentTagIds = array();
        $tagIds = array();

        if (!empty($field->value->externalData)) {
            foreach ($field->value->externalData as $tag) {
                if (isset($tag['keywords'][$field->languageCode])) {
                    $tagKeywords[] = $tag['keywords'][$field->languageCode];
                    $parentTagIds[] = $tag['parent_id'];
                    $tagIds[] = $tag['id'];
                } elseif (isset($tag['keywords'][$tag['main_language_code']])) {
                    $tagKeywords[] = $tag['keywords'][$tag['main_language_code']];
                    $parentTagIds[] = $tag['parent_id'];
                    $tagIds[] = $tag['id'];
                } else {
                    // Something went wrong with the tag, we will not index it
                    continue;
                }
            }
        }

        return array(
            new Search\Field(
                'tag_keywords',
                $tagKeywords,
                new Search\FieldType\MultipleStringField()
            ),
            new Search\Field(
                'parent_tag_ids',
                $parentTagIds,
                new Search\FieldType\MultipleIntegerField()
            ),
            new Search\Field(
                'tag_ids',
                $tagIds,
                new Search\FieldType\MultipleIntegerField()
            ),
            new Search\Field(
                'tag_text',
                implode(' ', $tagKeywords),
                new Search\FieldType\TextField()
            ),
            new Search\Field(
                'fulltext',
                implode(' ', $tagKeywords),
                new Search\FieldType\FullTextField()
            ),
        );
    }

    /**
     * Get index field types for search backend.
     *
     * @return \eZ\Publish\SPI\Search\FieldType[]
     */
    public function getIndexDefinition()
    {
        return array(
            'tag_keywords' => new Search\FieldType\MultipleStringField(),
            'parent_tag_ids' => new Search\FieldType\MultipleIntegerField(),
            'tag_ids' => new Search\FieldType\MultipleIntegerField(),
            'tag_text' => new Search\FieldType\TextField(),
        );
    }

    /**
     * Get name of the default field to be used for matching.
     *
     * @return string
     */
    public function getDefaultMatchField()
    {
        return 'tag_text';
    }

    /**
     * Get name of the default field to be used for sorting.
     *
     * @return string
     */
    public function getDefaultSortField()
    {
        return $this->getDefaultMatchField();
    }
}
