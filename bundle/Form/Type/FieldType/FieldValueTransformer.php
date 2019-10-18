<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\Form\Type\FieldType;

use eZ\Publish\API\Repository\FieldType;
use eZ\Publish\API\Repository\Values\Content\Field;
use Netgen\TagsBundle\Core\FieldType\Tags\Value;
use Symfony\Component\Form\DataTransformerInterface;

final class FieldValueTransformer implements DataTransformerInterface
{
    /**
     * @var \eZ\Publish\API\Repository\FieldType
     */
    private $fieldType;

    /**
     * @var \eZ\Publish\API\Repository\Values\Content\Field
     */
    private $field;

    public function __construct(FieldType $fieldType, Field $field)
    {
        $this->fieldType = $fieldType;
        $this->field = $field;
    }

    public function transform($value): ?array
    {
        if (!$value instanceof Value) {
            return null;
        }

        $ids = [];
        $parentIds = [];
        $keywords = [];
        $locales = [];

        foreach ($value->tags as $tag) {
            $tagKeyword = $tag->getKeyword($this->field->languageCode);
            $mainKeyword = $tag->getKeyword($tag->mainLanguageCode);

            $ids[] = $tag->id;
            $parentIds[] = $tag->parentTagId;
            $keywords[] = $tagKeyword ?? $mainKeyword;
            $locales[] = $tagKeyword !== null ? $this->field->languageCode : $tag->mainLanguageCode;
        }

        return [
            'ids' => implode('|#', $ids),
            'parent_ids' => implode('|#', $parentIds),
            'keywords' => implode('|#', $keywords),
            'locales' => implode('|#', $locales),
        ];
    }

    public function reverseTransform($value): Value
    {
        if ($value === null) {
            return $this->fieldType->getEmptyValue();
        }

        $ids = explode('|#', $value['ids'] ?? '');
        $parentIds = explode('|#', $value['parent_ids' ?? '']);
        $keywords = explode('|#', $value['keywords'] ?? '');
        $locales = explode('|#', $value['locales'] ?? '');

        $hash = [];
        for ($i = 0, $count = count($ids); $i < $count; ++$i) {
            if (!array_key_exists($i, $parentIds) || !array_key_exists($i, $keywords) || !array_key_exists($i, $locales)) {
                break;
            }

            if ($ids[$i] !== '0') {
                $hash[] = ['id' => (int) $ids[$i]];

                continue;
            }

            $hash[] = [
                'parent_id' => (int) $parentIds[$i],
                'keywords' => [$locales[$i] => $keywords[$i]],
                'main_language_code' => $locales[$i],
            ];
        }

        return $this->fieldType->fromHash($hash);
    }
}
