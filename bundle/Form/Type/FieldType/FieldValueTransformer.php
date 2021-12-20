<?php

namespace Netgen\TagsBundle\Form\Type\FieldType;

use eZ\Publish\API\Repository\FieldType;
use eZ\Publish\API\Repository\Values\Content\Field;
use eZ\Publish\SPI\FieldType\Value;
use Symfony\Component\Form\DataTransformerInterface;

class FieldValueTransformer implements DataTransformerInterface
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

    /**
     * @param \Netgen\TagsBundle\Core\FieldType\Tags\Value $value
     *
     * @return array|null
     */
    public function transform($value)
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
            $mainKeyword = $tag->getKeyword();

            $ids[] = $tag->id;
            $parentIds[] = $tag->parentTagId;
            $keywords[] = $tagKeyword !== null ? $this->escape($tagKeyword) : $this->escape($mainKeyword);
            $locales[] = $tagKeyword !== null ? $this->field->languageCode : $tag->mainLanguageCode;
        }

        return [
            'ids' => implode('|#', $ids),
            'parent_ids' => implode('|#', $parentIds),
            'keywords' => implode('|#', $keywords),
            'locales' => implode('|#', $locales),
        ];
    }

    /**
     * @param array|null $value
     *
     * @return \Netgen\TagsBundle\Core\FieldType\Tags\Value
     */
    public function reverseTransform($value)
    {
        if ($value === null) {
            return $this->fieldType->getEmptyValue();
        }

        $ids = explode('|#', $value['ids']);
        $parentIds = explode('|#', $value['parent_ids']);
        $keywords = explode('|#', $value['keywords']);
        $locales = explode('|#', $value['locales']);

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
                'keywords' => [$locales[$i] => $this->escape($keywords[$i])],
                'main_language_code' => $locales[$i],
            ];
        }

        return $this->fieldType->fromHash($hash);
    }

    private function escape($string): string
    {
        return htmlspecialchars($string, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML401, 'UTF-8');
    }
}
