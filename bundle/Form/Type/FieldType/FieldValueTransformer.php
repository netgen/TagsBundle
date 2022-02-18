<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\Form\Type\FieldType;

use Ibexa\Contracts\Core\Repository\FieldType;
use Ibexa\Contracts\Core\Repository\Values\Content\Field;
use Netgen\TagsBundle\Core\FieldType\Tags\Value;
use Symfony\Component\Form\DataTransformerInterface;
use function array_key_exists;
use function count;
use function explode;
use function htmlspecialchars;
use function implode;
use const ENT_HTML401;
use const ENT_QUOTES;
use const ENT_SUBSTITUTE;

final class FieldValueTransformer implements DataTransformerInterface
{
    /**
     * @var \Ibexa\Contracts\Core\Repository\FieldType
     */
    private $fieldType;

    /**
     * @var \Ibexa\Contracts\Core\Repository\Values\Content\Field
     */
    private $field;

    public function __construct(FieldType $fieldType, Field $field)
    {
        $this->fieldType = $fieldType;
        $this->field = $field;
    }

    /**
     * @param \Netgen\TagsBundle\Core\FieldType\Tags\Value|null $value
     */
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
            $keywords[] = $this->escape($tagKeyword ?? $mainKeyword);
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
     * @param mixed[]|null $value
     */
    public function reverseTransform($value): Value
    {
        if ($value === null) {
            return $this->fieldType->getEmptyValue();
        }

        $ids = explode('|#', $value['ids'] ?? '');
        $parentIds = explode('|#', $value['parent_ids'] ?? '');
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
                'keywords' => [$locales[$i] => $this->escape($keywords[$i])],
                'main_language_code' => $locales[$i],
            ];
        }

        return $this->fieldType->fromHash($hash);
    }

    private function escape(string $string): string
    {
        return htmlspecialchars($string, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML401, 'UTF-8');
    }
}
