<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\Core\FieldType\Tags\TagsStorage\Gateway;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\FetchMode;
use Doctrine\DBAL\Types\Types;
use Ibexa\Contracts\Core\Persistence\Content\Field;
use Ibexa\Contracts\Core\Persistence\Content\Language\Handler as LanguageHandler;
use Ibexa\Contracts\Core\Persistence\Content\VersionInfo;
use Netgen\TagsBundle\Core\FieldType\Tags\TagsStorage\Gateway;

use function array_values;
use function in_array;

final class DoctrineStorage extends Gateway
{
    public function __construct(private Connection $connection, private LanguageHandler $languageHandler) {}

    public function storeFieldData(VersionInfo $versionInfo, Field $field): void
    {
        foreach ($field->value->externalData as $priority => $tag) {
            $insertQuery = $this->connection->createQueryBuilder();
            $insertQuery
                ->insert('eztags_attribute_link')
                ->values(
                    [
                        'keyword_id' => ':keyword_id',
                        'objectattribute_id' => ':objectattribute_id',
                        'objectattribute_version' => ':objectattribute_version',
                        'object_id' => ':object_id',
                        'priority' => ':priority',
                    ],
                )
                ->setParameter(':keyword_id', $tag['id'], Types::INTEGER)
                ->setParameter(':objectattribute_id', $field->id, Types::INTEGER)
                ->setParameter(':objectattribute_version', $versionInfo->versionNo, Types::INTEGER)
                ->setParameter(':object_id', $versionInfo->contentInfo->id, Types::INTEGER)
                ->setParameter(':priority', $priority, Types::INTEGER);

            $insertQuery->execute();
        }
    }

    public function getFieldData(VersionInfo $versionInfo, Field $field): void
    {
        $field->value->externalData = $this->loadFieldData($field->id, $versionInfo->versionNo);
    }

    public function deleteFieldData(VersionInfo $versionInfo, array $fieldIds): void
    {
        $query = $this->connection->createQueryBuilder();
        $query
            ->delete('eztags_attribute_link')
            ->where(
                $query->expr()->andX(
                    $query->expr()->in('objectattribute_id', [':objectattribute_id']),
                    $query->expr()->eq('objectattribute_version', ':objectattribute_version'),
                ),
            )
            ->setParameter(':objectattribute_id', $fieldIds, Connection::PARAM_INT_ARRAY)
            ->setParameter(':objectattribute_version', $versionInfo->versionNo, Types::INTEGER);

        $query->execute();
    }

    /**
     * Returns the data for the given $fieldId and $versionNo.
     */
    private function loadFieldData(int $fieldId, int $versionNo): array
    {
        $query = $this->connection->createQueryBuilder();
        $query
            ->select(
                // Tag
                'DISTINCT t.id AS eztags_id',
                't.parent_id AS eztags_parent_id',
                't.main_tag_id AS eztags_main_tag_id',
                't.keyword AS eztags_keyword',
                't.depth AS eztags_depth',
                't.path_string AS eztags_path_string',
                't.modified AS eztags_modified',
                't.remote_id AS eztags_remote_id',
                't.main_language_id AS eztags_main_language_id',
                't.language_mask AS eztags_language_mask',
                // Tag keywords
                'k.keyword AS eztags_keyword_keyword',
                'k.locale AS eztags_keyword_locale',
                // Tag attribute links
                'tal.priority AS eztags_attribute_link_priority',
            )
            ->from('eztags', 't')
            ->innerJoin(
                't',
                'eztags_attribute_link',
                'tal',
                $query->expr()->eq(
                    't.id',
                    'tal.keyword_id',
                ),
            )
            ->innerJoin(
                't',
                'eztags_keyword',
                'k',
                $query->expr()->eq(
                    't.id',
                    'k.keyword_id',
                ),
            )->where(
                $query->expr()->andX(
                    $query->expr()->eq('tal.objectattribute_id', ':objectattribute_id'),
                    $query->expr()->eq('tal.objectattribute_version', ':objectattribute_version'),
                ),
            )
            ->setParameter(':objectattribute_id', $fieldId, Types::INTEGER)
            ->setParameter(':objectattribute_version', $versionNo, Types::INTEGER)
            ->orderBy('tal.priority', 'ASC');

        $statement = $query->execute();

        $rows = $statement->fetchAll(FetchMode::ASSOCIATIVE);

        $tagList = [];
        foreach ($rows as $row) {
            $tagId = (int) $row['eztags_id'];
            $tagList[$tagId] ??= [
                'id' => (int) $row['eztags_id'],
                'parent_id' => (int) $row['eztags_parent_id'],
                'main_tag_id' => (int) $row['eztags_main_tag_id'],
                'keywords' => [],
                'depth' => (int) $row['eztags_depth'],
                'path_string' => $row['eztags_path_string'],
                'modified' => (int) $row['eztags_modified'],
                'remote_id' => $row['eztags_remote_id'],
                'always_available' => (bool) ((int) $row['eztags_language_mask'] & 1),
                'main_language_code' => $this->languageHandler->load($row['eztags_main_language_id'])->languageCode,
                'language_codes' => [],
            ];

            $tagList[$tagId]['keywords'][$row['eztags_keyword_locale']] ??= $row['eztags_keyword_keyword'];

            if (!in_array($row['eztags_keyword_locale'], $tagList[$tagId]['language_codes'], true)) {
                $tagList[$tagId]['language_codes'][] = $row['eztags_keyword_locale'];
            }
        }

        return array_values($tagList);
    }
}
