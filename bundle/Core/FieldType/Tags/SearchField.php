<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\Core\FieldType\Tags;

use Ibexa\Contracts\Core\FieldType\Indexable;
use Ibexa\Contracts\Core\Persistence\Content\Field;
use Ibexa\Contracts\Core\Persistence\Content\Type\FieldDefinition;
use Ibexa\Contracts\Core\Search;

use function count;
use function implode;

final class SearchField implements Indexable
{
    public function getIndexData(Field $field, FieldDefinition $fieldDefinition): array
    {
        $tagKeywords = [];
        $parentTagIds = [];
        $tagIds = [];

        if (count($field->value->externalData ?? []) > 0) {
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

        return [
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
                new Search\FieldType\StringField()
            ),
            new Search\Field(
                'fulltext',
                implode(' ', $tagKeywords),
                new Search\FieldType\FullTextField()
            ),
        ];
    }

    public function getIndexDefinition(): array
    {
        return [
            'tag_keywords' => new Search\FieldType\MultipleStringField(),
            'parent_tag_ids' => new Search\FieldType\MultipleIntegerField(),
            'tag_ids' => new Search\FieldType\MultipleIntegerField(),
            'tag_text' => new Search\FieldType\StringField(),
            'fulltext' => new Search\FieldType\FullTextField(),
        ];
    }

    public function getDefaultMatchField(): string
    {
        return 'tag_text';
    }

    public function getDefaultSortField(): string
    {
        return $this->getDefaultMatchField();
    }
}
